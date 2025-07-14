<?php

namespace Database\Seeders;

use App\Domain\User\Entities\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        if (Schema::hasTable('users')) {
            User::truncate();
        }
        fake()->unique(true);
        User::factory(10000)->create();
    }
}
