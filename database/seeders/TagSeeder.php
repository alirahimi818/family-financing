<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TagSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        $tags = [
            'essential',
            'non-essential',
            'recurring',
            'emergency',
            'family',
            'kids',
            'health',
            'work',
            'personal',
            'home',
            'travel',
            'fun',
            'subscription',
            'online',
            'cash',
            'card',
            'big-purchase',
            'maintenance',
            'food',
            'transport',
        ];

        DB::table('tags')->truncate();

        DB::table('tags')->insert(
            collect($tags)->map(function ($name) use ($now) {
                return [
                    'name'       => $name,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            })->toArray()
        );
    }
}
