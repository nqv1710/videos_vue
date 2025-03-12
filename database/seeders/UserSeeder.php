<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Tạo tài khoản admin
        User::factory()->admin()->create();

        // Tạo 10 user thông thường
        User::factory()->count(10)->create();

        // Tạo 5 user chưa xác thực email
        User::factory()->count(5)->unverified()->create();
    }
}
