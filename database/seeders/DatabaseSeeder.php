<?php

namespace Database\Seeders;

use App\Models\Member;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();
        User::query()->firstOrCreate(
            ['email' => 'test@example.com'],
            ['name' => 'Test User', 'password' => bcrypt('password')]
        );

        $members = [
            ['name' => '山田 太郎', 'company' => null, 'status' => Member::STATUS_UNCHECKED],
            ['name' => '佐藤 一郎', 'company' => null, 'status' => Member::STATUS_UNCHECKED],
            ['name' => '鈴木 次郎', 'company' => null, 'status' => Member::STATUS_UNCHECKED],
            ['name' => '高橋 健', 'company' => null, 'status' => Member::STATUS_UNCHECKED],
            ['name' => '伊藤 誠', 'company' => null, 'status' => Member::STATUS_UNCHECKED],
        ];

        foreach ($members as $member) {
            Member::query()->updateOrCreate(
                ['name' => $member['name']],
                ['company' => $member['company'], 'status' => $member['status']]
            );
        }
    }
}
