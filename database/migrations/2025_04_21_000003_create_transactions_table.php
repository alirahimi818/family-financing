<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('title')->nullable();
            $table->decimal('amount', 15, 2);
            $table->enum('type', ['income', 'expense']);
            $table->foreignId('category_id')->nullable()->constrained()->onDelete('set null');
            $table->date('transaction_date');
            $table->timestamps();

            $table->index('type');
            $table->index('transaction_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
