<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Arr;
use Carbon\Carbon;

class PeriodicTransactionSeeder extends Seeder
{
    public function run(): void
    {
        $userId = 1; // Change if needed

        $now = Carbon::now();

        $categories = DB::table('categories')
            ->pluck('id', 'name')
            ->toArray();

        $tags = DB::table('tags')
            ->pluck('id', 'name')
            ->toArray();

        $data = [
            [
                'title'            => 'Monthly Salary',
                'amount'           => 3000_00 / 100, // 3000.00
                'type'             => 'income',
                'category_name'    => 'Salary',
                'tag_names'        => ['essential', 'work', 'recurring'],
                'transaction_date' => $now->copy()->startOfMonth()->toDateString(),
            ],
            [
                'title'            => 'Rent Payment',
                'amount'           => 1200_00 / 100,
                'type'             => 'expense',
                'category_name'    => 'Rent / Mortgage',
                'tag_names'        => ['essential', 'home', 'recurring'],
                'transaction_date' => $now->copy()->startOfMonth()->toDateString(),
            ],
            [
                'title'            => 'Internet & Mobile Plan',
                'amount'           => 60_00 / 100,
                'type'             => 'expense',
                'category_name'    => 'Internet & Mobile',
                'tag_names'        => ['subscription', 'online', 'recurring'],
                'transaction_date' => $now->copy()->startOfMonth()->toDateString(),
            ],
            [
                'title'            => 'Gym Membership',
                'amount'           => 40_00 / 100,
                'type'             => 'expense',
                'category_name'    => 'Sports / Gym',
                'tag_names'        => ['health', 'personal', 'recurring'],
                'transaction_date' => $now->copy()->startOfMonth()->toDateString(),
            ],
        ];

        DB::table('periodic_transactions')->truncate();

        foreach ($data as $item) {
            $categoryId = $categories[$item['category_name']] ?? null;

            $tagIds = collect($item['tag_names'])
                ->map(fn ($name) => $tags[$name] ?? null)
                ->filter()
                ->values()
                ->all();

            DB::table('periodic_transactions')->insert([
                'user_id'          => $userId,
                'title'            => $item['title'],
                'amount'           => $item['amount'],
                'type'             => $item['type'],
                'category_id'      => $categoryId,
                'transaction_date' => $item['transaction_date'],
                'tag_ids'          => json_encode($tagIds),
                'is_active'        => true,
                'created_at'       => $now,
                'updated_at'       => $now,
            ]);
        }
    }
}
