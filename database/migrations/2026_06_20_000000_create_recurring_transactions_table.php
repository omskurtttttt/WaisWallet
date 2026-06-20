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
        Schema::create('recurring_transactions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained()->cascadeOnDelete();
            $table->enum('type', ['income', 'expense']);
            $table->integer('amount'); // stored in cents
            $table->foreignUuid('category_id')->constrained()->cascadeOnDelete();
            $table->string('recipient_name')->nullable();
            $table->string('title');
            $table->foreignUuid('card_id')->nullable()->constrained()->nullOnDelete();
            $table->string('frequency'); // daily, weekly, bi-weekly, monthly, yearly
            $table->date('next_run_date');
            $table->date('last_run_date')->nullable();
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['user_id', 'is_active', 'next_run_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recurring_transactions');
    }
};
