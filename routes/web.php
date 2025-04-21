<?php

use App\Livewire\Categories;
use App\Livewire\PeriodicTransactions;
use App\Livewire\Tags;
use App\Livewire\Transactions;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');
    Route::get('/categories', Categories::class)->name('categories');
    Route::get('/tags', Tags::class)->name('tags');
    Route::get('/transactions', Transactions::class)->name('transactions');
    Route::get('/periodic-transactions', PeriodicTransactions::class)->name('periodicTransactions');
});
