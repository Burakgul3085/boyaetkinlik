<?php

namespace App\Support;

use App\Models\PaintRoom;
use Illuminate\Http\Request;

class PaintRoomParticipantResolver
{
    public function isRoomOwner(Request $request, PaintRoom $room): bool
    {
        $user = $request->user();

        return $user
            && ! $user->is_admin
            && (int) $user->id === (int) $room->owner_user_id;
    }

    public function resolveGuestRole(Request $request, PaintRoom $room): ?string
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

    /**
     * Oda sahibi oturumu misafir oturumundan önce gelir (aynı cihazda karışıklığı önler).
     */
    public function resolveRole(Request $request, PaintRoom $room): ?string
    {
        $this->bootstrapGuestSessionFromHeader($request, $room);

        if ($this->isRoomOwner($request, $room)) {
            if ($this->resolveGuestRole($request, $room) !== null) {
                $this->clearGuestSession($room);
            }

            return 'owner';
        }

        if ($this->resolveGuestRole($request, $room) === 'guest') {
            return 'guest';
        }

        return null;
    }

    public function storeGuestSession(PaintRoom $room, string $guestToken): void
    {
        session([
            'paint_room_guest' => [
                'room_id' => $room->id,
                'token' => $guestToken,
            ],
        ]);
        session()->save();
    }

    public function clearGuestSession(PaintRoom $room): void
    {
        $session = session('paint_room_guest');
        if (is_array($session) && (int) ($session['room_id'] ?? 0) === (int) $room->id) {
            session()->forget('paint_room_guest');
        }
    }

    /**
     * Mobil tarayıcıda çerez oturumu kaybolsa bile API isteklerindeki misafir token'ını oturuma yazar.
     */
    public function bootstrapGuestSessionFromHeader(Request $request, PaintRoom $room): void
    {
        if ($this->isRoomOwner($request, $room)) {
            return;
        }

        $headerToken = trim((string) $request->header('X-Paint-Room-Guest-Token', ''));
        if ($headerToken === '' || ! $room->guest_token) {
            return;
        }

        if (! hash_equals($room->guest_token, hash('sha256', $headerToken))) {
            return;
        }

        $session = session('paint_room_guest');
        $hasValidSession = is_array($session)
            && (int) ($session['room_id'] ?? 0) === (int) $room->id
            && hash_equals($room->guest_token, hash('sha256', (string) ($session['token'] ?? '')));

        if (! $hasValidSession) {
            $this->storeGuestSession($room, $headerToken);
        }
    }
}
