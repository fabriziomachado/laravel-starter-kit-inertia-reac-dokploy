<?php

declare(strict_types=1);

use App\Models\User;

it('denies guest access to the horizon dashboard', function (): void {
    config(['horizon.allowed_emails' => 'admin@example.com']);

    $response = $this->get('/horizon');

    $response->assertForbidden();
});

it('denies authenticated users whose email is not in the allowed list', function (): void {
    config(['horizon.allowed_emails' => 'admin@example.com']);

    $user = User::factory()->withoutTwoFactor()->create([
        'email' => 'other@example.com',
    ]);

    $response = $this->actingAs($user)->get('/horizon');

    $response->assertForbidden();
});

it('allows authenticated users whose email is in the allowed list', function (): void {
    config(['horizon.allowed_emails' => 'admin@example.com,dev@example.com']);

    $user = User::factory()->withoutTwoFactor()->create([
        'email' => 'admin@example.com',
    ]);

    $response = $this->actingAs($user)->get('/horizon');

    $response->assertOk();
});

it('allows access in local when the allowed email list is empty', function (): void {
    app()->detectEnvironment(fn (): string => 'local');
    config(['horizon.allowed_emails' => '']);

    $response = $this->get('/horizon');

    $response->assertOk();
});

it('trims whitespace from allowed emails in the list', function (): void {
    config(['horizon.allowed_emails' => ' admin@example.com , dev@example.com ']);

    $user = User::factory()->withoutTwoFactor()->create([
        'email' => 'dev@example.com',
    ]);

    $response = $this->actingAs($user)->get('/horizon');

    $response->assertOk();
});

it('treats a non-string allowed email config as an empty list', function (): void {
    config(['horizon.allowed_emails' => null]);

    $user = User::factory()->withoutTwoFactor()->create([
        'email' => 'admin@example.com',
    ]);

    $response = $this->actingAs($user)->get('/horizon');

    $response->assertForbidden();
});
