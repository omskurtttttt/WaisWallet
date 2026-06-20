<?php

use App\Models\Card;
use App\Models\User;
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

test('user can update their card', function () {
    $user = User::factory()->create();
    $card = Card::factory()->create([
        'user_id' => $user->id,
        'name' => 'Old Card Name',
        'brand' => 'Visa',
        'last_four' => '1111',
        'expiration_month' => 5,
        'expiration_year' => 2029,
        'color' => '#111111',
        'is_active' => true,
    ]);

    $response = $this->actingAs($user)
        ->put(route('wallet.cards.update', $card), [
            'name' => 'New Card Name',
            'brand' => 'Mastercard',
            'last_four' => '2222',
            'expiration_month' => 6,
            'expiration_year' => 2030,
            'color' => '#222222',
            'is_active' => false,
        ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('cards', [
        'id' => $card->id,
        'name' => 'New Card Name',
        'brand' => 'Mastercard',
        'last_four' => '2222',
        'is_active' => false,
    ]);
});

test('user cannot update another user\'s card', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();
    $card = Card::factory()->create([
        'user_id' => $user2->id,
        'name' => 'User 2 Card',
        'brand' => 'Visa',
        'last_four' => '1234',
        'expiration_month' => 12,
        'expiration_year' => 2030,
        'color' => '#123456',
        'is_active' => true,
    ]);

    $response = $this->actingAs($user1)
        ->put(route('wallet.cards.update', $card), [
            'name' => 'Hacked Card Name',
            'brand' => 'Visa',
            'last_four' => '4321',
            'expiration_month' => 12,
            'expiration_year' => 2030,
            'color' => '#123456',
            'is_active' => true,
        ]);

    $response->assertStatus(403);
    $this->assertDatabaseHas('cards', [
        'id' => $card->id,
        'name' => 'User 2 Card',
    ]);
});

test('user can delete their card (soft delete)', function () {
    $user = User::factory()->create();
    $card = Card::factory()->create([
        'user_id' => $user->id,
        'name' => 'Card to Delete',
    ]);

    $response = $this->actingAs($user)
        ->delete(route('wallet.cards.destroy', $card));

    $response->assertRedirect();
    $this->assertSoftDeleted('cards', [
        'id' => $card->id,
    ]);
});

test('user cannot delete another user\'s card', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();
    $card = Card::factory()->create([
        'user_id' => $user2->id,
        'name' => 'User 2 Card',
    ]);

    $response = $this->actingAs($user1)
        ->delete(route('wallet.cards.destroy', $card));

    $response->assertStatus(403);
    $this->assertDatabaseHas('cards', [
        'id' => $card->id,
        'deleted_at' => null,
    ]);
});
