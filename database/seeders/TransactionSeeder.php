<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Faker\Factory as Faker;

class TransactionSeeder extends Seeder
{
    public function run(): void
    {
        $userId = 1; // Change if needed
        $faker = Faker::create();

        $now = Carbon::now();
        $monthsBack = 10;   // how many past months to seed
        $monthlyIncome = 3000; // salary per month

        // Load categories and tags
        $categoryIds = DB::table('categories')->pluck('id', 'name')->toArray();
        $allTagIds = DB::table('tags')->pluck('id')->all();

        // Clean old data
        DB::table('transaction_tag')->truncate();
        DB::table('transactions')->truncate();

        // Load periodic transactions
        $periodics = DB::table('periodic_transactions')
            ->where('user_id', $userId)
            ->where('is_active', true)
            ->get();

        $transactionsToInsert = [];
        $pivotToInsert = [];

        // Iterate months from oldest to newest
        for ($m = $monthsBack; $m >= 0; $m--) {
            $monthStart = $now->copy()->subMonths($m)->startOfMonth();
            $monthEnd = $monthStart->copy()->endOfMonth();

            $monthExpenseTotal = 0;

            // 1) Insert Salary transaction on first day of month
            $salaryIdTemp = count($transactionsToInsert); // temp index before insert

            $transactionsToInsert[] = [
                'user_id'          => $userId,
                'title'            => 'Monthly Salary',
                'amount'           => $monthlyIncome,
                'type'             => 'income',
                'category_id'      => $categoryIds['Salary'] ?? array_values($categoryIds)[0],
                'transaction_date' => $monthStart->toDateString(),
                'created_at'       => $now,
                'updated_at'       => $now,
            ];

            // Attach a couple of tags to salary
            // We will map to real transaction IDs after insert, so store temp index
            $salaryTagIds = $this->pickTagsByName(['recurring', 'essential', 'work'], $allTagIds);
            foreach ($salaryTagIds as $tagId) {
                $pivotToInsert[] = [
                    '_temp_tx_index' => $salaryIdTemp,
                    'tag_id'         => $tagId,
                ];
            }

            // 2) Insert periodic EXPENSES as real transactions for this month
            foreach ($periodics as $p) {
                if ($p->type !== 'expense') {
                    continue;
                }

                // If periodic expense exceeds monthly income, skip (demo safety)
                if ($p->amount > $monthlyIncome) {
                    continue;
                }

                // ensure we still don't exceed income cap
                if ($monthExpenseTotal + $p->amount > $monthlyIncome) {
                    continue;
                }

                $txTempIndex = count($transactionsToInsert);
                $date = $monthStart->copy()->addDays(rand(0, 3)); // early days of month

                $transactionsToInsert[] = [
                    'user_id'          => $userId,
                    'title'            => $p->title,
                    'amount'           => $p->amount,
                    'type'             => 'expense',
                    'category_id'      => $p->category_id,
                    'transaction_date' => $date->toDateString(),
                    'created_at'       => $now,
                    'updated_at'       => $now,
                ];

                $monthExpenseTotal += $p->amount;

                // Attach periodic tags if any
                $tagIds = json_decode($p->tag_ids ?? '[]', true) ?: [];
                foreach ($tagIds as $tagId) {
                    $pivotToInsert[] = [
                        '_temp_tx_index' => $txTempIndex,
                        'tag_id'         => $tagId,
                    ];
                }
            }

            // 3) Generate random expenses but keep monthly total <= income
            $remainingBudget = $monthlyIncome - $monthExpenseTotal;

            // Random number of expenses this month
            $expenseCount = rand(12, 25);

            for ($i = 0; $i < $expenseCount; $i++) {
                if ($remainingBudget <= 0) {
                    break;
                }

                // each expense between 5 and min(remainingBudget, 400)
                $maxExpense = min($remainingBudget, 400);
                $amount = rand(5, (int)$maxExpense);

                $txTempIndex = count($transactionsToInsert);
                $date = $faker->dateTimeBetween($monthStart, $monthEnd);

                $categoryId = $faker->randomElement(array_values($categoryIds));

                $transactionsToInsert[] = [
                    'user_id'          => $userId,
                    'title'            => $faker->randomElement([
                        'Groceries', 'Restaurant', 'Fuel', 'Shopping',
                        'Bill Payment', 'Pharmacy', 'Subscription'
                    ]),
                    'amount'           => $amount,
                    'type'             => 'expense',
                    'category_id'      => $categoryId,
                    'transaction_date' => Carbon::instance($date)->toDateString(),
                    'created_at'       => $now,
                    'updated_at'       => $now,
                ];

                $monthExpenseTotal += $amount;
                $remainingBudget -= $amount;

                // attach 1-3 random tags
                $randomTags = collect($allTagIds)->random(rand(1, 3));
                foreach ($randomTags as $tagId) {
                    $pivotToInsert[] = [
                        '_temp_tx_index' => $txTempIndex,
                        'tag_id'         => $tagId,
                    ];
                }
            }
        }

        // Bulk insert transactions
        DB::table('transactions')->insert($transactionsToInsert);

        // Fetch real transaction IDs in the same order
        $insertedTxIds = DB::table('transactions')
            ->orderBy('id')
            ->pluck('id')
            ->all();

        // Map pivot temp indices to real IDs
        $finalPivot = [];
        foreach ($pivotToInsert as $row) {
            $tempIndex = $row['_temp_tx_index'];
            $finalPivot[] = [
                'transaction_id' => $insertedTxIds[$tempIndex],
                'tag_id'         => $row['tag_id'],
            ];
        }

        DB::table('transaction_tag')->insert($finalPivot);
    }

    /**
     * Pick tags by name if exist, otherwise fallback random.
     */
    private function pickTagsByName(array $names, array $allTagIds): array
    {
        $tagMap = DB::table('tags')->pluck('id', 'name')->toArray();
        $ids = [];

        foreach ($names as $n) {
            if (isset($tagMap[$n])) {
                $ids[] = $tagMap[$n];
            }
        }

        // fallback to 1 random tag if none matched
        if (empty($ids) && !empty($allTagIds)) {
            $ids[] = $allTagIds[array_rand($allTagIds)];
        }

        return array_values(array_unique($ids));
    }
}
