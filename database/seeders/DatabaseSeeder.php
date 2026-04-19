<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Setting;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::query()->updateOrCreate([
            'email' => 'admin@boyaetkinlik.test',
        ], [
            'name' => 'Admin',
            'password' => bcrypt('12345678'),
            'is_admin' => true,
        ]);

        $defaults = [
            'footer_text' => 'Boya Etkinlik Platformu',
            'about' => 'Modern ve kullanıcı dostu boyama sayfası platformu.',
            'contact_email' => 'admin@boyaetkinlik.test',
            'navbar_links' => "Anasayfa|/\nİletişim|/iletisim",
        ];

        foreach ($defaults as $key => $value) {
            Setting::query()->updateOrCreate(['key' => $key], ['value' => $value]);
        }
    }
}
