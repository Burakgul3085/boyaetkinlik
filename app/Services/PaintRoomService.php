<?php

namespace App\Services;

use App\Models\ColoringPage;
use App\Models\PaintRoom;
use App\Models\PaintRoomSignal;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

class PaintRoomService
{
    public const ROOM_TTL_MINUTES = 30;

    public function closeExpiredRooms(): void
    {
        PaintRoom::query()
            ->where('status', '!=', PaintRoom::STATUS_CLOSED)
            ->where('expires_at', '<=', now())
            ->update([
                'status' => PaintRoom::STATUS_CLOSED,
                'closed_at' => now(),
                'closed_reason' => 'Süre doldu (30 dk)',
            ]);
    }

    public function closeActiveRoomsForOwner(User $owner, string $reason): void
    {
        PaintRoom::query()
            ->where('owner_user_id', $owner->id)
            ->where('status', '!=', PaintRoom::STATUS_CLOSED)
            ->update([
                'status' => PaintRoom::STATUS_CLOSED,
                'closed_at' => now(),
                'closed_reason' => $reason,
            ]);
    }

    public function createRoom(User $owner): PaintRoom
    {
        $this->closeExpiredRooms();
        $this->closeActiveRoomsForOwner($owner, 'Yeni oda oluşturuldu');

        return DB::transaction(function () use ($owner) {
            $pin = $this->generateUniquePin();
            $page = ColoringPage::query()->where('is_free', true)->inRandomOrder()->first();

            return PaintRoom::query()->create([
                'room_code' => $this->generateUniqueRoomCode(),
                'pin' => $pin,
                'invite_token' => Str::random(48),
                'owner_user_id' => $owner->id,
                'coloring_page_id' => $page?->id,
                'status' => PaintRoom::STATUS_WAITING,
                'expires_at' => now()->addMinutes(self::ROOM_TTL_MINUTES),
            ]);
        });
    }

    public function closeRoom(PaintRoom $room, string $reason): void
    {
        if ($room->status === PaintRoom::STATUS_CLOSED) {
            return;
        }

        $room->update([
            'status' => PaintRoom::STATUS_CLOSED,
            'closed_at' => now(),
            'closed_reason' => $reason,
        ]);

        PaintRoomSignal::query()->where('paint_room_id', $room->id)->delete();
    }

    public function findOpenRoomByPin(string $pin): ?PaintRoom
    {
        $this->closeExpiredRooms();

        return PaintRoom::query()
            ->where('pin', $pin)
            ->where('status', '!=', PaintRoom::STATUS_CLOSED)
            ->where('expires_at', '>', now())
            ->first();
    }

    public function findOpenRoomByInviteToken(string $token): ?PaintRoom
    {
        $this->closeExpiredRooms();

        return PaintRoom::query()
            ->where('invite_token', $token)
            ->where('status', '!=', PaintRoom::STATUS_CLOSED)
            ->where('expires_at', '>', now())
            ->first();
    }

    public function joinAsGuest(PaintRoom $room, string $displayName): string
    {
        $this->closeExpiredRooms();

        if (! $room->isOpen()) {
            throw new RuntimeException('Oda kapalı veya süresi dolmuş.');
        }

        if ($room->hasGuest()) {
            throw new RuntimeException('Oda dolu (2/2).');
        }

        $guestToken = Str::random(40);
        $name = trim($displayName) !== '' ? trim($displayName) : 'Misafir';

        $room->update([
            'guest_display_name' => Str::limit($name, 80, ''),
            'guest_token' => hash('sha256', $guestToken),
            'status' => PaintRoom::STATUS_ACTIVE,
        ]);

        return $guestToken;
    }

    private function generateUniquePin(): string
    {
        for ($i = 0; $i < 30; $i++) {
            $pin = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
            $exists = PaintRoom::query()
                ->where('pin', $pin)
                ->where('status', '!=', PaintRoom::STATUS_CLOSED)
                ->where('expires_at', '>', now())
                ->exists();
            if (! $exists) {
                return $pin;
            }
        }

        throw new RuntimeException('PIN üretilemedi, lütfen tekrar deneyin.');
    }

    private function generateUniqueRoomCode(): string
    {
        for ($i = 0; $i < 20; $i++) {
            $code = Str::lower(Str::random(10));
            if (! PaintRoom::query()->where('room_code', $code)->exists()) {
                return $code;
            }
        }

        throw new RuntimeException('Oda kodu üretilemedi.');
    }
}
