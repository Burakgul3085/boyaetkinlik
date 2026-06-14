<?php

namespace App\Http\Controllers;

use App\Models\PaintRoom;
use App\Models\PaintRoomSignal;
use App\Models\Setting;
use App\Services\PaintRoomService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use RuntimeException;

class PaintRoomController extends Controller
{
    public function __construct(private readonly PaintRoomService $rooms) {}

    public function index(): View
    {
        return view('frontend.paint-room.index', [
            'canCreate' => auth()->check() && ! auth()->user()->is_admin,
        ]);
    }

    public function create(Request $request): RedirectResponse
    {
        $user = $request->user();
        if (! $user || $user->is_admin) {
            return redirect()->route('paint-room.index')
                ->withErrors(['room' => 'Oda oluşturmak için üye girişi yapmalısınız.']);
        }

        $room = $this->rooms->createRoom($user);

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
            return redirect()->route('paint-room.join.form')
                ->withErrors(['room' => 'Bu odaya erişim yetkiniz yok. PIN veya davet linki ile katılın.']);
        }

        return view('frontend.paint-room.lobby', [
            'room' => $room->fresh(),
            'role' => $role,
            'inviteUrl' => route('paint-room.invite', $room->invite_token),
            'pin' => $room->pin,
            'expiresAtIso' => $room->expires_at->toIso8601String(),
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

        return response()->json([
            'open' => true,
            'status' => $room->status,
            'participants' => $room->participantCount(),
            'maxParticipants' => 2,
            'guestName' => $room->guest_display_name,
            'inviteUsed' => $room->inviteLinkUsed(),
            'expiresAt' => $room->expires_at->toIso8601String(),
            'role' => $role,
            'message' => $room->hasGuest()
                ? 'Görüntülü bağlantı kuruluyor…'
                : 'Misafir bekleniyor…',
        ]);
    }

    public function pollSignals(Request $request, PaintRoom $room): JsonResponse
    {
        $role = $this->resolveParticipantRole($request, $room);
        if ($role === null || ! $room->isOpen()) {
            return response()->json(['signals' => []], 403);
        }

        $after = max(0, (int) $request->query('after', 0));

        $signals = PaintRoomSignal::query()
            ->where('paint_room_id', $room->id)
            ->where('id', '>', $after)
            ->where('from_role', '!=', $role)
            ->orderBy('id')
            ->limit(50)
            ->get(['id', 'from_role', 'signal_type', 'payload']);

        return response()->json([
            'signals' => $signals->map(fn ($s) => [
                'id' => $s->id,
                'from' => $s->from_role,
                'type' => $s->signal_type,
                'payload' => json_decode($s->payload, true),
            ]),
        ]);
    }

    public function sendSignal(Request $request, PaintRoom $room): JsonResponse
    {
        $role = $this->resolveParticipantRole($request, $room);
        if ($role === null || ! $room->isOpen()) {
            return response()->json(['ok' => false], 403);
        }

        $data = $request->validate([
            'type' => ['required', 'in:offer,answer,ice'],
            'payload' => ['required', 'array'],
        ]);

        $encoded = json_encode($data['payload']);
        if ($encoded === false || strlen($encoded) > 65536) {
            return response()->json(['ok' => false, 'message' => 'Geçersiz sinyal'], 422);
        }

        $signal = PaintRoomSignal::query()->create([
            'paint_room_id' => $room->id,
            'from_role' => $role,
            'signal_type' => $data['type'],
            'payload' => $encoded,
            'created_at' => now(),
        ]);

        if ($data['type'] === 'offer') {
            PaintRoomSignal::query()
                ->where('paint_room_id', $room->id)
                ->where('id', '<', $signal->id)
                ->delete();
        }

        return response()->json(['ok' => true, 'id' => $signal->id]);
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
        return view('frontend.paint-room.join', [
            'clarificationText' => Setting::getValue('clarification_text', ''),
        ]);
    }

    public function joinByPin(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'pin' => ['required', 'string', 'size:6', 'regex:/^\d{6}$/'],
            'display_name' => ['nullable', 'string', 'max:80'],
            'kvkk_accepted' => ['accepted'],
        ], [
            'pin.regex' => 'PIN 6 haneli rakamlardan oluşmalıdır.',
            'kvkk_accepted.accepted' => 'Devam etmek için KVKK aydınlatma metnini kabul etmelisiniz.',
        ]);

        try {
            $room = $this->rooms->findOpenRoomByPin($data['pin']);
            if (! $room) {
                return back()->withErrors(['pin' => 'Geçersiz PIN veya oda kapalı.'])->withInput();
            }

            $guestToken = $this->rooms->joinAsGuest($room, $data['display_name'] ?? '', false);
            $this->storeGuestSession($room, $guestToken);
            session(['paint_room_kvkk_accepted' => true]);

            return redirect()
                ->route('paint-room.lobby', $room)
                ->with('success', 'Odaya katıldınız.');
        } catch (RuntimeException $e) {
            return back()->withErrors(['pin' => $e->getMessage()])->withInput();
        }
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

        if ($room->inviteLinkUsed()) {
            return view('frontend.paint-room.error', [
                'title' => 'Link kullanıldı',
                'message' => 'Bu davet linki zaten kullanıldı. Oda sahibinden PIN isteyerek katılabilirsiniz.',
                'actionUrl' => route('paint-room.join.form'),
                'actionLabel' => 'PIN ile katıl',
            ]);
        }

        if ($room->hasGuest()) {
            return view('frontend.paint-room.error', [
                'title' => 'Oda dolu',
                'message' => 'Bu oda dolu (2/2 kişi).',
            ]);
        }

        $existingRole = $this->resolveParticipantRole(request(), $room);
        if ($existingRole !== null) {
            return redirect()->route('paint-room.lobby', $room);
        }

        return view('frontend.paint-room.invite', [
            'room' => $room,
            'inviteToken' => $inviteToken,
            'clarificationText' => Setting::getValue('clarification_text', ''),
        ]);
    }

    public function joinByInvite(Request $request, string $inviteToken): RedirectResponse
    {
        $data = $request->validate([
            'display_name' => ['nullable', 'string', 'max:80'],
            'kvkk_accepted' => ['accepted'],
        ], [
            'kvkk_accepted.accepted' => 'Devam etmek için KVKK aydınlatma metnini kabul etmelisiniz.',
        ]);

        $room = $this->rooms->findOpenRoomByInviteToken($inviteToken);
        if (! $room) {
            return redirect()->route('paint-room.index')
                ->withErrors(['room' => 'Davet linki geçersiz veya oda kapalı.']);
        }

        try {
            $guestToken = $this->rooms->joinAsGuest($room, $data['display_name'] ?? '', true);
            $this->storeGuestSession($room, $guestToken);
            session(['paint_room_kvkk_accepted' => true]);

            return redirect()
                ->route('paint-room.lobby', $room)
                ->with('success', 'Odaya katıldınız.');
        } catch (RuntimeException $e) {
            return back()->withErrors(['room' => $e->getMessage()])->withInput();
        }
    }

    private function resolveParticipantRole(Request $request, PaintRoom $room): ?string
    {
        $user = $request->user();
        if ($user && ! $user->is_admin && (int) $user->id === (int) $room->owner_user_id) {
            return 'owner';
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

    private function storeGuestSession(PaintRoom $room, string $guestToken): void
    {
        session([
            'paint_room_guest' => [
                'room_id' => $room->id,
                'token' => $guestToken,
            ],
        ]);
    }

    private function clearGuestSession(PaintRoom $room): void
    {
        $session = session('paint_room_guest');
        if (is_array($session) && (int) ($session['room_id'] ?? 0) === (int) $room->id) {
            session()->forget('paint_room_guest');
        }
    }
}
