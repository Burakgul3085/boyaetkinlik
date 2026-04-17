<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('categories')) {
            Schema::create('categories', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('slug')->unique();
                $table->text('description')->nullable();
                $table->foreignId('parent_id')->nullable()->constrained('categories')->nullOnDelete();
                $table->unsignedInteger('nav_order')->default(0);
                $table->boolean('show_in_nav')->default(true);
                $table->timestamps();
            });
        } else {
            Schema::table('categories', function (Blueprint $table) {
                if (! Schema::hasColumn('categories', 'parent_id')) {
                    $table->foreignId('parent_id')->nullable()->after('description')->constrained('categories')->nullOnDelete();
                }
                if (! Schema::hasColumn('categories', 'nav_order')) {
                    $table->unsignedInteger('nav_order')->default(0)->after('parent_id');
                }
                if (! Schema::hasColumn('categories', 'show_in_nav')) {
                    $table->boolean('show_in_nav')->default(true)->after('nav_order');
                }
            });
        }

        $roots = [
            ['name' => 'Okul Öncesi', 'slug' => 'okul-oncesi', 'description' => 'Okul öncesi seviyesine uygun temel boyama ve etkinlik içerikleri.', 'nav_order' => 10],
            ['name' => 'Özel Eğitim', 'slug' => 'ozel-egitim', 'description' => 'Özel eğitim süreçlerine destek olacak sade ve etkili içerikler.', 'nav_order' => 20],
            ['name' => 'İlkokul', 'slug' => 'ilkokul', 'description' => 'İlkokul seviyesine uygun eğitici ve eğlenceli boyama içerikleri.', 'nav_order' => 30],
            ['name' => 'Ortaokul', 'slug' => 'ortaokul', 'description' => 'Ortaokul öğrencileri için detay seviyesi artan boyama sayfaları.', 'nav_order' => 40],
            ['name' => 'Yetişkin', 'slug' => 'yetiskin', 'description' => 'Yetişkinler için rahatlatıcı ve detaylı boyama koleksiyonları.', 'nav_order' => 50],
        ];

        foreach ($roots as $root) {
            DB::table('categories')->updateOrInsert(
                ['slug' => $root['slug']],
                [
                    'name' => $root['name'],
                    'description' => $root['description'],
                    'parent_id' => null,
                    'nav_order' => $root['nav_order'],
                    'show_in_nav' => true,
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );
        }

        $children = [
            ['parent_slug' => 'okul-oncesi', 'name' => '2 Yaş', 'slug' => 'okul-oncesi-2-yas', 'description' => '2 yaş grubu için basit şekiller ve dikkat geliştirici sayfalar.', 'nav_order' => 1],
            ['parent_slug' => 'okul-oncesi', 'name' => '3 Yaş', 'slug' => 'okul-oncesi-3-yas', 'description' => '3 yaş grubu için eğlenceli ve kolay seviye boyama içerikleri.', 'nav_order' => 2],
            ['parent_slug' => 'okul-oncesi', 'name' => '4 Yaş', 'slug' => 'okul-oncesi-4-yas', 'description' => '4 yaş çocuklar için el-göz koordinasyonunu destekleyen içerikler.', 'nav_order' => 3],
            ['parent_slug' => 'okul-oncesi', 'name' => '5-6 Yaş', 'slug' => 'okul-oncesi-5-6-yas', 'description' => '5-6 yaşa uygun, ilkokula hazırlık odaklı boyama sayfaları.', 'nav_order' => 4],
            ['parent_slug' => 'ozel-egitim', 'name' => 'Boyama Sayfaları', 'slug' => 'ozel-egitim-boyama', 'description' => 'Özel eğitimde kullanılabilecek görsel destekli boyama içerikleri.', 'nav_order' => 1],
            ['parent_slug' => 'ozel-egitim', 'name' => 'Çizgi Çalışmaları', 'slug' => 'ozel-egitim-cizgi-calismalari', 'description' => 'Kalem kontrolü ve ince motor gelişimine yardımcı çizgi çalışmaları.', 'nav_order' => 2],
            ['parent_slug' => 'ozel-egitim', 'name' => 'Etkinlik Sayfaları', 'slug' => 'ozel-egitim-etkinlik', 'description' => 'Dikkat, eşleştirme ve temel kavramları destekleyen etkinlik sayfaları.', 'nav_order' => 3],
            ['parent_slug' => 'ozel-egitim', 'name' => 'Kelime Kartları', 'slug' => 'ozel-egitim-kelime-kartlari', 'description' => 'Dil gelişimini destekleyen görsel kelime kartı çalışmaları.', 'nav_order' => 4],
        ];

        foreach ($children as $child) {
            $parentId = DB::table('categories')->where('slug', $child['parent_slug'])->value('id');
            if (! $parentId) {
                continue;
            }

            DB::table('categories')->updateOrInsert(
                ['slug' => $child['slug']],
                [
                    'name' => $child['name'],
                    'description' => $child['description'],
                    'parent_id' => $parentId,
                    'nav_order' => $child['nav_order'],
                    'show_in_nav' => true,
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('categories')) {
            return;
        }

        Schema::table('categories', function (Blueprint $table) {
            if (Schema::hasColumn('categories', 'parent_id')) {
                $table->dropConstrainedForeignId('parent_id');
            }
            if (Schema::hasColumn('categories', 'nav_order')) {
                $table->dropColumn('nav_order');
            }
            if (Schema::hasColumn('categories', 'show_in_nav')) {
                $table->dropColumn('show_in_nav');
            }
        });
    }
};
