<?php

use App\Models\User;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('new user registration preloads default categories', function () {
    $response = $this->post('/register', [
        'name' => 'Jane Smith',
        'email' => 'jane@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response->assertRedirect();
    
    $user = User::where('email', 'jane@example.com')->first();
    $this->assertNotNull($user);

    // Should have 16 default categories (10 expense + 6 income)
    $this->assertCount(16, $user->categories);

    // Check specific default category exists
    $this->assertDatabaseHas('categories', [
        'user_id' => $user->id,
        'name' => '🍔 Food & Dining',
        'type' => 'expense',
        'icon' => 'coffee',
    ]);

    $this->assertDatabaseHas('categories', [
        'user_id' => $user->id,
        'name' => '💼 Salary',
        'type' => 'income',
        'icon' => 'briefcase',
    ]);
});

test('authenticated user can create custom category', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)
        ->post(route('categories.store'), [
            'name' => '🍿 Movies & Cinema',
            'type' => 'expense',
            'icon' => 'film',
            'color' => '#ff477e',
        ]);

    $response->assertRedirect();
    
    $this->assertDatabaseHas('categories', [
        'user_id' => $user->id,
        'name' => '🍿 Movies & Cinema',
        'type' => 'expense',
        'icon' => 'film',
        'color' => '#ff477e',
    ]);
});

test('user cannot create category with invalid type', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)
        ->post(route('categories.store'), [
            'name' => 'Invalid Cat',
            'type' => 'invalid-type', // invalid type
            'icon' => 'tag',
            'color' => '#ffffff',
        ]);

    $response->assertSessionHasErrors(['type']);
    $this->assertDatabaseMissing('categories', [
        'name' => 'Invalid Cat',
    ]);
});

test('user cannot create category with invalid color hex code', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)
        ->post(route('categories.store'), [
            'name' => 'Invalid Color Cat',
            'type' => 'expense',
            'icon' => 'tag',
            'color' => 'not-a-hex', // invalid color
        ]);

    $response->assertSessionHasErrors(['color']);
});
