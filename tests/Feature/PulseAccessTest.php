<?php

declare(strict_types=1);

use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

it('denies guest access to the pulse dashboard', function (): void {
    config(['pulse.allowed_emails' => 'admin@example.com']);

    $response = $this->get('/pulse');

    $response->assertForbidden();
});

it('denies authenticated users whose email is not in the allowed list', function (): void {
    config(['pulse.allowed_emails' => 'admin@example.com']);

    $user = User::factory()->withoutTwoFactor()->create([
        'email' => 'other@example.com',
    ]);

    $response = $this->actingAs($user)->get('/pulse');

    $response->assertForbidden();
});

it('allows authenticated users whose email is in the allowed list', function (): void {
    config(['pulse.allowed_emails' => 'admin@example.com,dev@example.com']);

    $user = User::factory()->withoutTwoFactor()->create([
        'email' => 'admin@example.com',
    ]);

    $response = $this->actingAs($user)->get('/pulse');

    $response->assertOk();
});

it('allows access in local when the allowed email list is empty', function (): void {
    app()->detectEnvironment(fn (): string => 'local');
    config(['pulse.allowed_emails' => '']);

    $response = $this->get('/pulse');

    $response->assertOk();
});

it('trims whitespace from allowed emails in the list', function (): void {
    config(['pulse.allowed_emails' => ' admin@example.com , dev@example.com ']);

    $user = User::factory()->withoutTwoFactor()->create([
        'email' => 'dev@example.com',
    ]);

    $response = $this->actingAs($user)->get('/pulse');

    $response->assertOk();
});

it('treats a non-string allowed email config as an empty list', function (): void {
    config(['pulse.allowed_emails' => null]);

    $user = User::factory()->withoutTwoFactor()->create([
        'email' => 'admin@example.com',
    ]);

    $response = $this->actingAs($user)->get('/pulse');

    $response->assertForbidden();
});

it('allows pulse cache classes to be unserialized from cache', function (): void {
    expect(config('cache.serializable_classes'))->toContain(
        Collection::class,
        stdClass::class,
        CarbonImmutable::class,
    );
});

it('uses the default database connection for pulse in tests', function (): void {
    expect(env('PULSE_DB_CONNECTION'))->toBe('');

    expect(config('pulse.storage.database.connection'))->toBeIn([null, '']);
});

it('supports a dedicated pulse database connection when configured', function (): void {
    config([
        'pulse.storage.database.connection' => 'pulse',
        'database.connections.pulse.database' => 'laravel_pulse',
    ]);

    expect(config('pulse.storage.database.connection'))->toBe('pulse');
    expect(config('database.connections.pulse.database'))->toBe('laravel_pulse');
});
