<?php

use App\Models\User;
use App\Models\Card;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('authenticated user can view wallets/cards index', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)
        ->get(route('wallet.index'));

    $response->assertStatus(200);
});

test('user can create a bank account card', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)
        ->post(route('wallet.cards.store'), [
            'name' => 'BPI Savings',
            'brand' => 'BPI',
            'last_four' => '1234',
            'expiration_month' => 12,
            'expiration_year' => 2030,
            'color' => '#3b82f6',
            'is_active' => true,
        ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('cards', [
        'user_id' => $user->id,
        'name' => 'BPI Savings',
        'brand' => 'BPI',
        'last_four' => '1234',
    ]);
});

test('user can create an e-wallet account', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)
        ->post(route('wallet.cards.store'), [
            'name' => 'GCash Wallet',
            'brand' => 'GCash',
            'last_four' => '9876',
            'expiration_month' => 12,
            'expiration_year' => 2099,
            'color' => '#8b5cf6',
            'is_active' => true,
        ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('cards', [
        'user_id' => $user->id,
        'name' => 'GCash Wallet',
        'brand' => 'GCash',
        'last_four' => '9876',
    ]);
});

test('user can create a cash account', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)
        ->post(route('wallet.cards.store'), [
            'name' => 'My Cash Box',
            'brand' => 'Cash',
            'last_four' => '0000',
            'expiration_month' => 12,
            'expiration_year' => 2099,
            'color' => '#10b981',
            'is_active' => true,
        ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('cards', [
        'user_id' => $user->id,
        'name' => 'My Cash Box',
        'brand' => 'Cash',
        'last_four' => '0000',
    ]);
});
