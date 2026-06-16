<?php

namespace App\Http\Controllers;

use App\Services\PaintRoomService;
use App\Support\PaintRoomParticipantResolver;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use RuntimeException;

/**
 * Misafir katılımı — PIN veya davet linki ile tek akış.
 *
 * 1) Link: GET /davet/{token} → ad + KVKK → POST → lobi (misafir)
 * 2) PIN:  GET /katil → PIN → POST → aynı davet formu → POST → lobi (misafir)
 */
class PaintRoomJoinController extends Controller
{
    public function __construct(
        private readonly PaintRoomService $rooms,
        private readonly PaintRoomParticipantResolver $participants,
    ) {}

    public function pinForm(): View
    {
        return view('frontend.paint-room.join-pin');
    }

    public function pinSubmit(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'pin' => ['required', 'string', 'max:12'],
        ]);

        $pin = $this->normalizePin($data['pin']);
        if (! preg_match('/^\d{6}$/', $pin)) {
            return back()->withErrors(['pin' => 'PIN 6 haneli rakamlardan oluşmalıdır.'])->withInput();
        }

        $room = $this->rooms->findOpenRoomByPin($pin);
        if (! $room) {
            return back()->withErrors(['pin' => 'Geçersiz PIN veya oda kapalı.'])->withInput();
        }

        if ($this->participants->isRoomOwner($request, $room)) {
            return redirect()
                ->route('paint-room.lobby', $room)
                ->with('success', 'Zaten oda sahibisiniz. PIN veya davet linkini misafirinizle paylaşın.');
        }

        session(['paint_room_pin_verified' => true]);

        return redirect()->route('paint-room.join.guest', $room->invite_token);
    }

    public function guestForm(Request $request, string $inviteToken): View|RedirectResponse
    {
        $room = $this->rooms->findOpenRoomByInviteToken($inviteToken);

        if (! $room) {
            return view('frontend.paint-room.error', [
                'title' => 'Davet geçersiz',
                'message' => 'Davet linki veya PIN geçersiz, süresi dolmuş veya oda kapatılmış.',
            ]);
        }

        $room->load('owner');
        $this->rooms->releaseStaleGuest($room);
        $room->refresh();

        if ($redirect = $this->redirectIfAlreadyInside($request, $room)) {
            return $redirect;
        }

        if ($room->hasGuest()) {
            return view('frontend.paint-room.error', [
                'title' => 'Oda dolu',
                'message' => 'Bu odada şu an başka bir misafir var. Oda sahibinden bekleyin veya yeni davet isteyin.',
            ]);
        }

        return view('frontend.paint-room.join-guest', [
            'room' => $room,
            'inviteToken' => $inviteToken,
            'ownerName' => trim((string) ($room->owner?->name ?? '')),
            'viaPin' => (bool) session()->pull('paint_room_pin_verified', false),
        ]);
    }

    public function guestSubmit(Request $request, string $inviteToken): RedirectResponse
    {
        $data = $request->validate([
            'display_name' => ['required', 'string', 'max:80'],
            'paint_room_consent_accepted' => ['accepted'],
        ], [
            'display_name.required' => 'Lütfen adınızı yazın.',
            'paint_room_consent_accepted.accepted' => 'Odaya katılmak için bilgilendirme metnini okuyup onaylamanız gerekir.',
        ]);

        $room = $this->rooms->findOpenRoomByInviteToken($inviteToken);
        if (! $room) {
            return redirect()->route('paint-room.index')
                ->withErrors(['room' => 'Davet geçersiz veya oda kapalı.']);
        }

        $this->rooms->releaseStaleGuest($room);
        $room->refresh();

        if ($redirect = $this->redirectIfAlreadyInside($request, $room)) {
            return $redirect;
        }

        if ($room->hasGuest()) {
            return back()->withErrors(['room' => 'Oda dolu. Başka bir misafir zaten katılmış.']);
        }

        try {
            $guestToken = $this->rooms->joinAsGuest($room, $data['display_name']);
            $this->participants->storeGuestSession($room, $guestToken);
            session(['paint_room_consent_accepted' => true]);

            return redirect()
                ->route('paint-room.lobby', $room)
                ->with('success', 'Odaya katıldınız. Oda sahibi ile boyayabilir ve görüntülü konuşabilirsiniz.');
        } catch (RuntimeException $e) {
            return back()->withErrors(['room' => $e->getMessage()])->withInput();
        }
    }

    private function redirectIfAlreadyInside(Request $request, $room): ?RedirectResponse
    {
        if ($this->participants->isRoomOwner($request, $room)) {
            return redirect()
                ->route('paint-room.lobby', $room)
                ->with('success', 'Odanız açık. Bu linki veya PIN\'i misafirinizle paylaşın.');
        }

        if ($this->participants->resolveGuestRole($request, $room) === 'guest') {
            return redirect()->route('paint-room.lobby', $room);
        }

        return null;
    }

    private function normalizePin(string $raw): string
    {
        $digits = preg_replace('/\D/', '', trim($raw));

        if ($digits === '') {
            return '';
        }

        return str_pad($digits, 6, '0', STR_PAD_LEFT);
    }
}
