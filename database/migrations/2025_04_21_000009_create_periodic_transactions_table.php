<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('periodic_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('title')->nullable();
            $table->decimal('amount', 15, 2);
            $table->enum('type', ['income', 'expense']);
            $table->foreignId('category_id')->nullable()->constrained()->onDelete('set null');
            $table->date('transaction_date');
            $table->json('tag_ids')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('periodic_transactions');
    }
};
