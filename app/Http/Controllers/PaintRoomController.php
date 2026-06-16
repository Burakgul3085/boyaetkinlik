<?php

namespace App\Http\Controllers;

use App\Models\PaintRoom;
use App\Models\PaintRoomSignal;
use App\Models\Setting;
use App\Services\PaintRoomService;
use App\Support\FileFormatDownloadService;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;
use RuntimeException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Throwable;

class PaintRoomController extends Controller
{
    public function __construct(private readonly PaintRoomService $rooms) {}

    public function index(): View
    {
        return view('frontend.paint-room.index', [
            'canCreate' => auth()->check() && ! auth()->user()->is_admin,
            'hasFreePages' => $this->rooms->hasAnyFreePages(),
            'categoryTree' => $this->rooms->freeCategoryTree(),
            'freePagesUrl' => route('paint-room.free-pages'),
        ]);
    }

    public function create(Request $request): RedirectResponse
    {
        $user = $request->user();
        if (! $user || $user->is_admin) {
            return redirect()->route('paint-room.index')
                ->withErrors(['room' => 'Oda oluşturmak için üye girişi yapmalısınız.']);
        }

        $data = $request->validate([
            'coloring_page_id' => ['required', 'integer', 'exists:coloring_pages,id'],
            'paint_room_consent_accepted' => ['accepted'],
        ], [
            'coloring_page_id.required' => 'Lütfen bir boyama sayfası seçin.',
            'paint_room_consent_accepted.accepted' => 'Oda oluşturmak için görüntülü boyama bilgilendirme metnini okuyup onaylamanız gerekir.',
        ]);

        try {
            $room = $this->rooms->createRoom($user, (int) $data['coloring_page_id']);
        } catch (RuntimeException $e) {
            return redirect()->route('paint-room.index')
                ->withErrors(['room' => $e->getMessage()])
                ->withInput();
        }

        return redirect()
            ->route('paint-room.lobby', $room)
            ->with('success', 'Odanız hazır — PIN veya davet linkini paylaşın.');
    }

    public function lobby(Request $request, PaintRoom $room): View|RedirectResponse
    {
        $this->rooms->closeExpiredRooms();

        if (! $room->isOpen()) {
            return redirect()->route('paint-room.index')
                ->withErrors(['room' => $room->closed_reason ?? 'Oda kapalı veya süresi dolmuş.']);
        }

        $role = $this->resolveParticipantRole($request, $room);
        if ($role === null) {
            if ($room->isOpen()) {
                return redirect()->route('paint-room.invite', $room->invite_token);
            }

            return redirect()->route('paint-room.index')
                ->withErrors(['room' => $room->closed_reason ?? 'Oda kapalı veya süresi dolmuş.']);
        }

        $guestSession = session('paint_room_guest', []);
        $room = $room->fresh()->load('coloringPage');
        $coloringPage = $room->coloringPage;

        return view('frontend.paint-room.lobby', [
            'room' => $room,
            'role' => $role,
            'inviteUrl' => route('paint-room.invite', $room->invite_token),
            'pin' => $room->pin,
            'expiresAtIso' => $room->expires_at->toIso8601String(),
            'guestAccessToken' => $role === 'guest'
                ? (string) ($guestSession['token'] ?? '')
                : '',
            'iceServers' => $this->iceServers(),
            'coloringPageTitle' => $coloringPage?->title,
            'lineArtUrl' => $coloringPage
                ? route('paint-room.line-art', $room)
                : null,
            'canvasLoadUrl' => route('paint-room.canvas.load', $room),
            'canvasSaveUrl' => route('paint-room.canvas.save', $room),
            'chatSendUrl' => route('paint-room.chat.send', $room),
            'chatPollUrl' => route('paint-room.chat.poll', $room),
            'chatHistoryUrl' => route('paint-room.chat.history', $room),
            'chatDisplayName' => $role === 'owner'
                ? (auth()->user()->name ?? 'Oda sahibi')
                : ($room->guest_display_name ?: 'Misafir'),
            'coloringPageId' => $room->coloring_page_id,
            'categoryTree' => $role === 'owner' ? $this->rooms->freeCategoryTree() : [],
            'freePagesUrl' => $role === 'owner' ? route('paint-room.free-pages') : null,
            'changePageUrl' => $role === 'owner' ? route('paint-room.page.change', $room) : null,
        ]);
    }

    public function status(Request $request, PaintRoom $room): JsonResponse
    {
        $this->rooms->closeExpiredRooms();
        $room->refresh();

        if (! $room->isOpen()) {
            return response()->json([
                'open' => false,
                'message' => $room->closed_reason ?? 'Oda kapandı.',
            ]);
        }

        $role = $this->resolveParticipantRole($request, $room);
        $room->load('coloringPage');

        return response()->json([
            'open' => true,
            'status' => $room->status,
            'participants' => $room->participantCount(),
            'maxParticipants' => 2,
            'guestName' => $room->guest_display_name,
            'expiresAt' => $room->expires_at->toIso8601String(),
            'role' => $role,
            'coloringPageId' => $room->coloring_page_id,
            'coloringPageTitle' => $room->coloringPage?->title,
            'message' => $room->hasGuest()
                ? 'Görüntülü bağlantı kuruluyor…'
                : 'Misafir bekleniyor…',
        ]);
    }

    public function pollSignals(Request $request, PaintRoom $room): JsonResponse
    {
        $role = $this->resolveParticipantRole($request, $room);
        if ($role === null || ! $room->isOpen()) {
            return $this->signalJson(['signals' => [], 'error' => 'unauthorized'], 403);
        }

        $after = max(0, (int) ($request->input('after', $request->query('after', 0))));

        $signals = PaintRoomSignal::query()
            ->where('paint_room_id', $room->id)
            ->where('id', '>', $after)
            ->where('from_role', '!=', $role)
            ->orderBy('id')
            ->limit(50)
            ->get(['id', 'from_role', 'signal_type', 'payload']);

        return $this->signalJson([
            'signals' => $signals->map(function ($s) {
                $payload = json_decode($s->payload, true);

                return [
                    'id' => $s->id,
                    'from' => $s->from_role,
                    'type' => $s->signal_type,
                    'payload' => is_array($payload) ? $payload : [],
                ];
            })->values(),
            'role' => $role,
        ]);
    }

    public function lineArt(Request $request, PaintRoom $room, FileFormatDownloadService $downloadService): BinaryFileResponse
    {
        $role = $this->resolveParticipantRole($request, $room);
        if ($role === null || ! $room->isOpen()) {
            abort(403);
        }

        $room->load('coloringPage');
        $coloringPage = $room->coloringPage;
        if (! $coloringPage || ! $coloringPage->is_free) {
            abort(404);
        }

        try {
            $source = $coloringPage->lineArtFileSource();
            /** @var FilesystemAdapter $disk */
            $disk = $source['disk'];
            $raster = $downloadService->lineArtRasterForPainting($disk, $source['path']);
        } catch (Throwable $exception) {
            report($exception);
            abort(404);
        }

        $response = response()->file($raster['absolute_path'], [
            'Content-Type' => 'image/png',
            'Cache-Control' => 'public, max-age=3600',
            'Access-Control-Allow-Origin' => $request->getSchemeAndHttpHost(),
        ]);

        if ($raster['is_temporary']) {
            $path = $raster['absolute_path'];
            register_shutdown_function(static function () use ($path): void {
                @unlink($path);
            });
        }

        return $response;
    }

    public function loadCanvas(Request $request, PaintRoom $room): JsonResponse
    {
        $role = $this->resolveParticipantRole($request, $room);
        if ($role === null || ! $room->isOpen()) {
            return $this->signalJson(['image' => null], 403);
        }

        return $this->signalJson([
            'image' => $room->canvas_snapshot,
            'updated' => $room->updated_at?->toIso8601String(),
        ]);
    }

    public function saveCanvas(Request $request, PaintRoom $room): JsonResponse
    {
        $role = $this->resolveParticipantRole($request, $room);
        if ($role === null || ! $room->isOpen()) {
            return $this->signalJson(['ok' => false], 403);
        }

        $data = $request->validate([
            'image' => ['required', 'string', 'max:524288'],
        ]);

        $room->update(['canvas_snapshot' => $data['image']]);

        return $this->signalJson(['ok' => true]);
    }

    public function signalHealth(Request $request, PaintRoom $room): JsonResponse
    {
        $role = $this->resolveParticipantRole($request, $room);
        $tableExists = Schema::hasTable('paint_room_signals');

        return $this->signalJson([
            'ok' => $role !== null && $tableExists,
            'role' => $role,
            'table' => $tableExists,
            'signalCount' => $tableExists
                ? PaintRoomSignal::query()->where('paint_room_id', $room->id)->count()
                : 0,
            'participants' => $room->participantCount(),
        ], ($role === null || ! $tableExists) ? 503 : 200);
    }

    public function sendSignal(Request $request, PaintRoom $room): JsonResponse
    {
        $role = $this->resolveParticipantRole($request, $room);
        if ($role === null || ! $room->isOpen()) {
            return $this->signalJson(['ok' => false, 'message' => 'Yetkisiz'], 403);
        }

        $type = (string) $request->input('type', '');
        if (! in_array($type, ['offer', 'answer', 'ice'], true)) {
            return $this->signalJson(['ok' => false, 'message' => 'Geçersiz sinyal türü'], 422);
        }

        $payload = $this->decodeSignalPayload($request->input('payload'));
        if ($payload === null) {
            return $this->signalJson(['ok' => false, 'message' => 'Geçersiz sinyal verisi'], 422);
        }

        $encoded = json_encode($payload, JSON_UNESCAPED_SLASHES);
        if ($encoded === false || strlen($encoded) > 65536) {
            return $this->signalJson(['ok' => false, 'message' => 'Geçersiz sinyal'], 422);
        }

        try {
            $signal = PaintRoomSignal::query()->create([
                'paint_room_id' => $room->id,
                'from_role' => $role,
                'signal_type' => $type,
                'payload' => $encoded,
                'created_at' => now(),
            ]);
        } catch (Throwable $e) {
            report($e);

            return $this->signalJson([
                'ok' => false,
                'message' => 'Sinyal kaydedilemedi. php artisan migrate --force çalıştırın.',
            ], 500);
        }

        if ($type === 'offer') {
            PaintRoomSignal::query()
                ->where('paint_room_id', $room->id)
                ->where('id', '<', $signal->id)
                ->whereIn('signal_type', ['offer', 'ice'])
                ->delete();
        }

        return $this->signalJson(['ok' => true, 'id' => $signal->id, 'role' => $role]);
    }

    public function sendChat(Request $request, PaintRoom $room): JsonResponse
    {
        $role = $this->resolveParticipantRole($request, $room);
        if ($role === null || ! $room->isOpen()) {
            return $this->signalJson(['ok' => false, 'message' => 'Yetkisiz'], 403);
        }

        $data = $request->validate([
            'text' => ['required', 'string', 'min:1', 'max:500'],
        ]);

        $text = trim(preg_replace('/\s+/u', ' ', $data['text']) ?? '');
        if ($text === '') {
            return $this->signalJson(['ok' => false, 'message' => 'Mesaj boş olamaz'], 422);
        }

        $payload = [
            'text' => $text,
            'name' => $this->chatSenderName($request, $room, $role),
            'at' => now()->toIso8601String(),
        ];

        $encoded = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if ($encoded === false) {
            return $this->signalJson(['ok' => false, 'message' => 'Mesaj kaydedilemedi'], 422);
        }

        try {
            $signal = PaintRoomSignal::query()->create([
                'paint_room_id' => $room->id,
                'from_role' => $role,
                'signal_type' => 'chat',
                'payload' => $encoded,
                'created_at' => now(),
            ]);
        } catch (Throwable $e) {
            report($e);

            return $this->signalJson([
                'ok' => false,
                'message' => 'Mesaj kaydedilemedi. php artisan migrate --force çalıştırın.',
            ], 500);
        }

        return $this->signalJson([
            'ok' => true,
            'id' => $signal->id,
            'message' => $this->formatChatMessage($signal),
        ]);
    }

    public function pollChat(Request $request, PaintRoom $room): JsonResponse
    {
        $role = $this->resolveParticipantRole($request, $room);
        if ($role === null || ! $room->isOpen()) {
            return $this->signalJson(['messages' => [], 'error' => 'unauthorized'], 403);
        }

        $after = max(0, (int) $request->input('after', $request->query('after', 0)));

        $messages = PaintRoomSignal::query()
            ->where('paint_room_id', $room->id)
            ->where('signal_type', 'chat')
            ->where('id', '>', $after)
            ->where('from_role', '!=', $role)
            ->orderBy('id')
            ->limit(30)
            ->get();

        return $this->signalJson([
            'messages' => $messages->map(
                fn (PaintRoomSignal $signal): array => $this->formatChatMessage($signal)
            )->values(),
            'role' => $role,
        ]);
    }

    public function chatHistory(Request $request, PaintRoom $room): JsonResponse
    {
        $role = $this->resolveParticipantRole($request, $room);
        if ($role === null || ! $room->isOpen()) {
            return $this->signalJson(['messages' => []], 403);
        }

        $messages = PaintRoomSignal::query()
            ->where('paint_room_id', $room->id)
            ->where('signal_type', 'chat')
            ->orderByDesc('id')
            ->limit(60)
            ->get()
            ->reverse()
            ->values();

        return $this->signalJson([
            'messages' => $messages->map(
                fn (PaintRoomSignal $signal): array => $this->formatChatMessage($signal)
            )->values(),
            'role' => $role,
        ]);
    }

    public function freePagesByCategory(Request $request): JsonResponse
    {
        $categoryId = max(0, (int) $request->query('category_id', 0));
        if ($categoryId <= 0) {
            return response()->json(['pages' => [], 'categoryId' => null]);
        }

        return response()->json([
            'categoryId' => $categoryId,
            'pages' => $this->rooms->freePagesInCategory($categoryId),
        ]);
    }

    public function changeColoringPage(Request $request, PaintRoom $room): JsonResponse
    {
        $role = $this->resolveParticipantRole($request, $room);
        if ($role !== 'owner' || ! $room->isOpen()) {
            return $this->signalJson(['ok' => false, 'message' => 'Yetkisiz'], 403);
        }

        $data = $request->validate([
            'coloring_page_id' => ['required', 'integer', 'exists:coloring_pages,id'],
        ]);

        try {
            $page = $this->rooms->changeColoringPage($room, (int) $data['coloring_page_id']);
        } catch (RuntimeException $e) {
            return $this->signalJson(['ok' => false, 'message' => $e->getMessage()], 422);
        }

        return $this->signalJson([
            'ok' => true,
            'coloringPageId' => $page->id,
            'coloringPageTitle' => $page->title,
            'lineArtUrl' => route('paint-room.line-art', $room),
        ]);
    }

    public function leave(Request $request, PaintRoom $room): JsonResponse|RedirectResponse
    {
        $role = $this->resolveParticipantRole($request, $room);

        if ($role === 'owner') {
            $this->rooms->closeRoom($room, 'Oda sahibi ayrıldı');
            $this->clearGuestSession($room);

            if ($request->expectsJson()) {
                return response()->json(['ok' => true, 'closed' => true]);
            }

            return redirect()->route('paint-room.index')->with('success', 'Oda kapatıldı.');
        }

        if ($role === 'guest') {
            PaintRoomSignal::query()->where('paint_room_id', $room->id)->delete();
            $room->update([
                'guest_display_name' => null,
                'guest_token' => null,
                'status' => PaintRoom::STATUS_WAITING,
            ]);
            $this->clearGuestSession($room);

            if ($request->expectsJson()) {
                return response()->json(['ok' => true, 'closed' => false]);
            }

            return redirect()->route('paint-room.index')->with('success', 'Odadan ayrıldınız.');
        }

        if ($request->expectsJson()) {
            return response()->json(['ok' => false], 403);
        }

        return redirect()->route('paint-room.index');
    }

    public function joinForm(): View
    {
        return view('frontend.paint-room.join');
    }

    public function verifyPin(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'pin' => ['required', 'string', 'max:12'],
        ]);

        $pin = $this->normalizePaintRoomPin($data['pin']);
        if (! preg_match('/^\d{6}$/', $pin)) {
            return back()->withErrors(['pin' => 'PIN 6 haneli rakamlardan oluşmalıdır.'])->withInput();
        }

        $room = $this->rooms->findOpenRoomByPin($pin);
        if (! $room) {
            return back()->withErrors(['pin' => 'Geçersiz PIN veya oda kapalı.'])->withInput();
        }

        if ($this->isRoomOwner($request, $room)) {
            return redirect()
                ->route('paint-room.lobby', $room)
                ->with('success', 'Zaten oda sahibisiniz. PIN veya davet linkini misafirinizle paylaşın.');
        }

        return redirect()->route('paint-room.invite', $room->invite_token);
    }

    public function inviteForm(string $inviteToken): View|RedirectResponse
    {
        $room = $this->rooms->findOpenRoomByInviteToken($inviteToken);

        if (! $room) {
            return view('frontend.paint-room.error', [
                'title' => 'Davet geçersiz',
                'message' => 'Davet linki geçersiz, süresi dolmuş veya oda kapatılmış.',
            ]);
        }

        $room->load('owner');

        if ($this->resolveGuestRole(request(), $room) === 'guest') {
            return redirect()->route('paint-room.lobby', $room);
        }

        if ($this->isRoomOwner(request(), $room)) {
            return redirect()
                ->route('paint-room.lobby', $room)
                ->with('success', 'Odanız açık. Bu davet linkini veya PIN\'i misafirinizle paylaşın.');
        }

        if ($room->hasGuest()) {
            return view('frontend.paint-room.error', [
                'title' => 'Oda dolu',
                'message' => 'Bu odada şu an başka bir misafir var. Lütfen oda sahibinden bekleyin veya yeni bir davet isteyin.',
            ]);
        }

        return view('frontend.paint-room.guest-join', [
            'room' => $room,
            'inviteToken' => $inviteToken,
            'ownerName' => trim((string) ($room->owner?->name ?? '')),
        ]);
    }

    public function joinByInvite(Request $request, string $inviteToken): RedirectResponse
    {
        $data = $request->validate([
            'display_name' => ['required', 'string', 'max:80'],
            'paint_room_consent_accepted' => ['accepted'],
        ], [
            'display_name.required' => 'Lütfen görünen adınızı yazın.',
            'paint_room_consent_accepted.accepted' => 'Odaya katılmak için görüntülü boyama bilgilendirme metnini okuyup onaylamanız gerekir.',
        ]);

        $room = $this->rooms->findOpenRoomByInviteToken($inviteToken);
        if (! $room) {
            return redirect()->route('paint-room.index')
                ->withErrors(['room' => 'Davet linki geçersiz veya oda kapalı.']);
        }

        if ($this->isRoomOwner($request, $room)) {
            return redirect()
                ->route('paint-room.lobby', $room)
                ->with('success', 'Zaten oda sahibisiniz. Bu sayfa misafirler içindir.');
        }

        if ($this->resolveGuestRole($request, $room) === 'guest') {
            return redirect()->route('paint-room.lobby', $room);
        }

        if ($room->hasGuest()) {
            return back()->withErrors(['room' => 'Oda dolu. Başka bir misafir zaten katılmış.']);
        }

        try {
            $guestToken = $this->rooms->joinAsGuest($room, $data['display_name']);
            $this->storeGuestSession($room, $guestToken);
            session(['paint_room_consent_accepted' => true]);

            return redirect()
                ->route('paint-room.lobby', $room)
                ->with('success', 'Odaya katıldınız — oda sahibi ile boyayabilir ve görüntülü konuşabilirsiniz.');
        } catch (RuntimeException $e) {
            return back()->withErrors(['room' => $e->getMessage()])->withInput();
        }
    }

    private function normalizePaintRoomPin(string $raw): string
    {
        $digits = preg_replace('/\D/', '', trim($raw));

        if ($digits === '') {
            return '';
        }

        return str_pad($digits, 6, '0', STR_PAD_LEFT);
    }

    private function isRoomOwner(Request $request, PaintRoom $room): bool
    {
        $user = $request->user();

        return $user
            && ! $user->is_admin
            && (int) $user->id === (int) $room->owner_user_id;
    }

    private function resolveGuestRole(Request $request, PaintRoom $room): ?string
    {
        $headerToken = trim((string) $request->header('X-Paint-Room-Guest-Token', ''));
        if ($headerToken !== '' && $room->guest_token) {
            if (hash_equals($room->guest_token, hash('sha256', $headerToken))) {
                return 'guest';
            }
        }

        $session = session('paint_room_guest');
        if (
            is_array($session)
            && (int) ($session['room_id'] ?? 0) === (int) $room->id
            && $room->guest_token
            && hash_equals($room->guest_token, hash('sha256', (string) ($session['token'] ?? '')))
        ) {
            return 'guest';
        }

        return null;
    }

    private function resolveParticipantRole(Request $request, PaintRoom $room): ?string
    {
        $guestRole = $this->resolveGuestRole($request, $room);
        if ($guestRole === 'guest') {
            return 'guest';
        }

        $user = $request->user();
        if ($user && ! $user->is_admin && (int) $user->id === (int) $room->owner_user_id) {
            return 'owner';
        }

        return null;
    }

    private function signalJson(array $data, int $status = 200): JsonResponse
    {
        return response()
            ->json($data, $status)
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->header('Pragma', 'no-cache');
    }

    private function storeGuestSession(PaintRoom $room, string $guestToken): void
    {
        session([
            'paint_room_guest' => [
                'room_id' => $room->id,
                'token' => $guestToken,
            ],
        ]);
        session()->save();
    }

    private function decodeSignalPayload(mixed $payload): ?array
    {
        if (is_array($payload)) {
            return $payload;
        }

        if (is_string($payload)) {
            $decoded = json_decode($payload, true);

            return is_array($decoded) ? $decoded : null;
        }

        return null;
    }

    private function iceServers(): array
    {
        $servers = [
            ['urls' => 'stun:stun.l.google.com:19302'],
            ['urls' => 'stun:stun1.l.google.com:19302'],
            ['urls' => 'stun:stun.cloudflare.com:3478'],
        ];

        $turnUrls = config('services.webrtc.turn_urls', []);
        $username = (string) config('services.webrtc.turn_username', '');
        $credential = (string) config('services.webrtc.turn_credential', '');

        if ($turnUrls !== [] && $username !== '' && $credential !== '') {
            $servers[] = [
                'urls' => $turnUrls,
                'username' => $username,
                'credential' => $credential,
            ];
        }

        return $servers;
    }

    private function clearGuestSession(PaintRoom $room): void
    {
        $session = session('paint_room_guest');
        if (is_array($session) && (int) ($session['room_id'] ?? 0) === (int) $room->id) {
            session()->forget('paint_room_guest');
        }
    }

    private function chatSenderName(Request $request, PaintRoom $room, string $role): string
    {
        if ($role === 'owner') {
            $name = trim((string) ($request->user()?->name ?? ''));

            return $name !== '' ? $name : 'Oda sahibi';
        }

        $name = trim((string) ($room->guest_display_name ?? ''));

        return $name !== '' ? $name : 'Misafir';
    }

    private function formatChatMessage(PaintRoomSignal $signal): array
    {
        $payload = json_decode($signal->payload, true);
        $text = is_array($payload) ? trim((string) ($payload['text'] ?? '')) : '';
        $name = is_array($payload) ? trim((string) ($payload['name'] ?? '')) : '';
        $at = is_array($payload) ? (string) ($payload['at'] ?? '') : '';

        if ($name === '') {
            $name = $signal->from_role === 'owner' ? 'Oda sahibi' : 'Misafir';
        }

        if ($at === '' && $signal->created_at) {
            $at = $signal->created_at->toIso8601String();
        }

        return [
            'id' => $signal->id,
            'from' => $signal->from_role,
            'name' => $name,
            'text' => $text,
            'at' => $at,
        ];
    }
}
