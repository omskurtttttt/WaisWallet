<?php

use App\Models\Budget;
use App\Models\Category;
use App\Models\RecurringTransaction;
use App\Models\Transaction;
use App\Models\User;
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

test('authenticated user can update their category', function () {
    $user = User::factory()->create();
    $category = Category::factory()->create([
        'user_id' => $user->id,
        'name' => 'Old Category Name',
        'type' => 'expense',
        'icon' => 'tag',
        'color' => '#112233',
    ]);

    $response = $this->actingAs($user)
        ->put(route('categories.update', $category), [
            'name' => 'Updated Category Name',
            'type' => 'expense',
            'icon' => 'coffee',
            'color' => '#445566',
        ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('categories', [
        'id' => $category->id,
        'name' => 'Updated Category Name',
        'icon' => 'coffee',
        'color' => '#445566',
    ]);
});

test('user cannot update another user\'s category', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();
    $category = Category::factory()->create(['user_id' => $user2->id, 'name' => 'User2 Category']);

    $response = $this->actingAs($user1)
        ->put(route('categories.update', $category), [
            'name' => 'Hacked Name',
            'type' => 'expense',
            'icon' => 'tag',
            'color' => '#123456',
        ]);

    $response->assertStatus(403);
    $this->assertDatabaseMissing('categories', ['name' => 'Hacked Name']);
});

test('authenticated user can delete an unused category', function () {
    $user = User::factory()->create();
    $category = Category::factory()->create(['user_id' => $user->id, 'name' => 'Temporary Category']);

    $response = $this->actingAs($user)
        ->delete(route('categories.destroy', $category));

    $response->assertRedirect();
    $this->assertDatabaseMissing('categories', ['id' => $category->id]);
});

test('user cannot delete another user\'s category', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();
    $category = Category::factory()->create(['user_id' => $user2->id, 'name' => 'Protected Category']);

    $response = $this->actingAs($user1)
        ->delete(route('categories.destroy', $category));

    $response->assertStatus(403);
    $this->assertDatabaseHas('categories', ['id' => $category->id]);
});

test('user cannot delete category that has existing transactions', function () {
    $user = User::factory()->create();
    $category = Category::factory()->create(['user_id' => $user->id, 'name' => 'Dining Out']);
    Transaction::factory()->create([
        'user_id' => $user->id,
        'category_id' => $category->id,
    ]);

    $response = $this->actingAs($user)
        ->delete(route('categories.destroy', $category));

    $response->assertSessionHasErrors(['error']);
    $this->assertDatabaseHas('categories', ['id' => $category->id]);
});

test('user cannot delete category that has active budgets', function () {
    $user = User::factory()->create();
    $category = Category::factory()->create(['user_id' => $user->id, 'name' => 'Groceries']);
    Budget::factory()->create([
        'user_id' => $user->id,
        'category_id' => $category->id,
    ]);

    $response = $this->actingAs($user)
        ->delete(route('categories.destroy', $category));

    $response->assertSessionHasErrors(['error']);
    $this->assertDatabaseHas('categories', ['id' => $category->id]);
});

test('user cannot delete category that has recurring transactions', function () {
    $user = User::factory()->create();
    $category = Category::factory()->create(['user_id' => $user->id, 'name' => 'Subscriptions']);
    RecurringTransaction::factory()->create([
        'user_id' => $user->id,
        'category_id' => $category->id,
    ]);

    $response = $this->actingAs($user)
        ->delete(route('categories.destroy', $category));

    $response->assertSessionHasErrors(['error']);
    $this->assertDatabaseHas('categories', ['id' => $category->id]);
});
