<?php

namespace App\Services;

use App\Models\Category;
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

    public function createRoom(User $owner, ?int $coloringPageId = null): PaintRoom
    {
        $this->closeExpiredRooms();
        $this->closeActiveRoomsForOwner($owner, 'Yeni oda oluşturuldu');

        return DB::transaction(function () use ($owner, $coloringPageId) {
            $pin = $this->generateUniquePin();
            $page = $this->resolveFreeColoringPage($coloringPageId);

            return PaintRoom::query()->create([
                'room_code' => $this->generateUniqueRoomCode(),
                'pin' => $pin,
                'invite_token' => Str::random(48),
                'owner_user_id' => $owner->id,
                'coloring_page_id' => $page->id,
                'status' => PaintRoom::STATUS_WAITING,
                'expires_at' => now()->addMinutes(self::ROOM_TTL_MINUTES),
            ]);
        });
    }

    public function changeColoringPage(PaintRoom $room, int $coloringPageId): ColoringPage
    {
        if (! $room->isOpen()) {
            throw new RuntimeException('Oda kapalı veya süresi dolmuş.');
        }

        $page = $this->resolveFreeColoringPage($coloringPageId);

        $room->update([
            'coloring_page_id' => $page->id,
            'canvas_snapshot' => null,
        ]);

        return $page;
    }

    /**
     * @return array<int, array{id: int, title: string, previewUrl: string}>
     */
    public function freePagesInCategory(int $categoryId): array
    {
        $exists = Category::query()->whereKey($categoryId)->exists();
        if (! $exists) {
            return [];
        }

        return ColoringPage::query()
            ->where('is_free', true)
            ->where('category_id', $categoryId)
            ->orderBy('title')
            ->get(['id', 'title'])
            ->map(fn (ColoringPage $page) => [
                'id' => $page->id,
                'title' => $page->title,
                'previewUrl' => route('products.preview-image', $page),
            ])
            ->values()
            ->all();
    }

    public function hasAnyFreePages(): bool
    {
        return ColoringPage::query()->where('is_free', true)->exists();
    }

    /**
     * Ücretsiz boyaması olan kategoriler — iç içe ağaç.
     *
     * @return list<array{id: int, name: string, directCount: int, totalCount: int, children: array}>
     */
    public function freeCategoryTree(): array
    {
        $allCategories = Category::allForAdminTree();

        $directCounts = ColoringPage::query()
            ->where('is_free', true)
            ->selectRaw('category_id, COUNT(*) as aggregate')
            ->groupBy('category_id')
            ->pluck('aggregate', 'category_id')
            ->map(fn ($count) => (int) $count)
            ->all();

        $subtreeCounts = [];
        foreach ($allCategories as $category) {
            $sum = 0;
            foreach (Category::subtreeIdsFromCollection($allCategories, $category->id) as $id) {
                $sum += $directCounts[$id] ?? 0;
            }
            $subtreeCounts[$category->id] = $sum;
        }

        $byParent = Category::childrenGroupedByParentId($allCategories);

        $build = function (int $parentKey) use (&$build, $byParent, $directCounts, $subtreeCounts): array {
            $nodes = [];
            foreach ($byParent[$parentKey] ?? [] as $category) {
                $total = $subtreeCounts[$category->id] ?? 0;
                if ($total === 0) {
                    continue;
                }

                $nodes[] = [
                    'id' => $category->id,
                    'name' => $category->name,
                    'directCount' => $directCounts[$category->id] ?? 0,
                    'totalCount' => $total,
                    'children' => $build($category->id),
                ];
            }

            return $nodes;
        };

        return $build(0);
    }

    /**
     * @deprecated freeCategoryTree + freePagesInCategory kullanın
     * @return array<int, array{id: int, title: string, previewUrl: string}>
     */
    public function freePagesForPicker(): array
    {
        return ColoringPage::query()
            ->where('is_free', true)
            ->orderBy('title')
            ->get(['id', 'title'])
            ->map(fn (ColoringPage $page) => [
                'id' => $page->id,
                'title' => $page->title,
                'previewUrl' => route('products.preview-image', $page),
            ])
            ->values()
            ->all();
    }

    private function resolveFreeColoringPage(?int $coloringPageId): ColoringPage
    {
        $query = ColoringPage::query()->where('is_free', true);

        if ($coloringPageId !== null) {
            $page = (clone $query)->where('id', $coloringPageId)->first();
            if (! $page) {
                throw new RuntimeException('Seçilen boyama bulunamadı veya ücretsiz değil.');
            }

            return $page;
        }

        throw new RuntimeException('Lütfen bir boyama sayfası seçin.');
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

        $pin = str_pad(preg_replace('/\D/', '', trim($pin)), 6, '0', STR_PAD_LEFT);
        if (! preg_match('/^\d{6}$/', $pin)) {
            return null;
        }

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
            'guest_last_seen_at' => now(),
            'status' => PaintRoom::STATUS_ACTIVE,
        ]);

        return $guestToken;
    }

    public function releaseStaleGuest(PaintRoom $room): void
    {
        if (! $room->hasGuest()) {
            return;
        }

        $lastSeen = $room->guest_last_seen_at;
        if ($lastSeen === null) {
            return;
        }

        if ($lastSeen->diffInSeconds(now()) < 120) {
            return;
        }

        PaintRoomSignal::query()->where('paint_room_id', $room->id)->delete();
        $room->update([
            'guest_display_name' => null,
            'guest_token' => null,
            'guest_last_seen_at' => null,
            'status' => PaintRoom::STATUS_WAITING,
        ]);
    }

    public function touchGuestPresence(PaintRoom $room): void
    {
        if (! $room->hasGuest()) {
            return;
        }

        $room->update(['guest_last_seen_at' => now()]);
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
