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
        // 1. categories table: Add color
        Schema::table('categories', function (Blueprint $table) {
            $table->string('color')->nullable();
        });

        // 2. cards table: Restructure
        Schema::table('cards', function (Blueprint $table) {
            $table->renameColumn('cardholder_name', 'name');
            $table->string('brand')->default('Visa');
            $table->integer('expiration_month')->default(12);
            $table->integer('expiration_year')->default(2027);
            $table->string('color')->default('#7033ff');
            $table->boolean('is_active')->default(true);
            
            $table->dropColumn(['card_type', 'expiry_date', 'nickname']);
        });

        // 3. transactions table: Add title and card_id
        Schema::table('transactions', function (Blueprint $table) {
            $table->string('title')->nullable();
            $table->foreignUuid('card_id')->nullable()->constrained()->nullOnDelete();
        });

        // 4. goals table: Make target_date nullable
        Schema::table('goals', function (Blueprint $table) {
            $table->timestamp('target_date')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('goals', function (Blueprint $table) {
            $table->timestamp('target_date')->nullable(false)->change();
        });

        Schema::table('transactions', function (Blueprint $table) {
            $table->dropForeign(['card_id']);
            $table->dropColumn(['title', 'card_id']);
        });

        Schema::table('cards', function (Blueprint $table) {
            $table->renameColumn('name', 'cardholder_name');
            $table->enum('card_type', ['visa', 'mastercard', 'amex', 'other'])->default('visa');
            $table->string('expiry_date', 5)->default('12/25');
            $table->string('nickname')->nullable();
            
            $table->dropColumn(['brand', 'expiration_month', 'expiration_year', 'color', 'is_active']);
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->dropColumn('color');
        });
    }
};
