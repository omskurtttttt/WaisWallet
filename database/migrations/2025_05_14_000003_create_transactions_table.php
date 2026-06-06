<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained()->cascadeOnDelete();
            $table->enum('type', ['income', 'expense']);
            $table->integer('amount'); // stored in cents
            $table->foreignUuid('category_id')->constrained()->cascadeOnDelete();
            $table->string('recipient_name');
            $table->string('title')->nullable();
            $table->foreignUuid('card_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('status', ['completed', 'pending', 'failed'])->default('completed');
            $table->timestamp('transaction_date');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'type']);
            $table->index(['user_id', 'transaction_date']);
            $table->index(['user_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
