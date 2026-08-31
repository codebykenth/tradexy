<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('standard registration records terms_accepted_at timestamp', function () {
    /** @var \Tests\TestCase $this */
    $response = $this->post('/register', [
        'name' => 'Test Trader',
        'email' => 'trader@example.com',
        'password' => 'SecurePass123!',
        'password_confirmation' => 'SecurePass123!',
        'terms' => '1',
    ]);

    $response->assertRedirect('/dashboard');

    $user = User::where('email', 'trader@example.com')->first();
    expect($user)->not->toBeNull();
    expect($user->terms_accepted_at)->not->toBeNull();
});

test('user without accepted terms sees terms modal on authenticated pages', function () {
    /** @var \Tests\TestCase $this */
    $user = User::factory()->unacceptedTerms()->create();

    $response = $this->actingAs($user)->get('/dashboard');

    $response->assertStatus(200);
    $response->assertSee('terms_acceptance_modal');
    $response->assertSee('I Accept the Terms & Privacy Policy', false);
});

test('user can accept terms via terms.accept endpoint', function () {
    /** @var \Tests\TestCase $this */
    $user = User::factory()->unacceptedTerms()->create();

    expect($user->terms_accepted_at)->toBeNull();

    $response = $this->actingAs($user)->post(route('terms.accept'));

    $response->assertSessionHas('success');
    $user->refresh();
    expect($user->terms_accepted_at)->not->toBeNull();
});

test('user with accepted terms does not see terms modal', function () {
    /** @var \Tests\TestCase $this */
    $user = User::factory()->create([
        'terms_accepted_at' => now(),
    ]);

    $response = $this->actingAs($user)->get('/dashboard');

    $response->assertStatus(200);
    $response->assertDontSee('terms_acceptance_modal');
});
