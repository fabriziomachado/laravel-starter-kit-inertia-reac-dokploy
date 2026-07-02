<?php

declare(strict_types=1);

use App\Http\Middleware\HandleInertiaRequests;
use App\Models\User;
use Illuminate\Http\Request;

it('shares app name from config', function (): void {
    $middleware = new HandleInertiaRequests();

    $request = Request::create('/', 'GET');

    $shared = $middleware->share($request);

    expect($shared)->toHaveKey('name')
        ->and($shared['name'])->toBe(config('app.name'));
});

it('shares null user when guest', function (): void {
    $middleware = new HandleInertiaRequests();

    $request = Request::create('/', 'GET');

    $shared = $middleware->share($request);

    expect($shared)->toHaveKey('auth')
        ->and($shared['auth'])->toHaveKey('user')
        ->and($shared['auth']['user'])->toBeNull();
});

it('shares authenticated user data', function (): void {
    $user = User::factory()->create([
        'name' => 'Test User',
        'email' => 'test@example.com',
    ]);

    $middleware = new HandleInertiaRequests();

    $request = Request::create('/', 'GET');
    $request->setUserResolver(fn () => $user);

    $shared = $middleware->share($request);

    expect($shared['auth']['user'])->not->toBeNull()
        ->and($shared['auth']['user']->id)->toBe($user->id)
        ->and($shared['auth']['user']->name)->toBe('Test User')
        ->and($shared['auth']['user']->email)->toBe('test@example.com');
});

it('defaults sidebarOpen to true when no cookie', function (): void {
    $middleware = new HandleInertiaRequests();

    $request = Request::create('/', 'GET');

    $shared = $middleware->share($request);

    expect($shared)->toHaveKey('sidebarOpen')
        ->and($shared['sidebarOpen'])->toBeTrue();
});

it('sets sidebarOpen to true when cookie is true', function (): void {
    $middleware = new HandleInertiaRequests();

    $request = Request::create('/', 'GET');
    $request->cookies->set('sidebar_state', 'true');

    $shared = $middleware->share($request);

    expect($shared['sidebarOpen'])->toBeTrue();
});

it('sets sidebarOpen to false when cookie is false', function (): void {
    $middleware = new HandleInertiaRequests();

    $request = Request::create('/', 'GET');
    $request->cookies->set('sidebar_state', 'false');

    $shared = $middleware->share($request);

    expect($shared['sidebarOpen'])->toBeFalse();
});

it('shares reverb client configuration from broadcasting config', function (): void {
    config([
        'broadcasting.connections.reverb.key' => 'shared-key',
        'broadcasting.connections.reverb.client.host' => 'ws.example.com',
        'broadcasting.connections.reverb.client.port' => 8081,
        'broadcasting.connections.reverb.client.scheme' => 'http',
    ]);

    $middleware = new HandleInertiaRequests();

    $request = Request::create('/', 'GET');

    $shared = $middleware->share($request);

    expect($shared['reverb'])->toBe([
        'key' => 'shared-key',
        'host' => 'ws.example.com',
        'port' => 8081,
        'scheme' => 'http',
    ]);
});

it('includes parent shared data', function (): void {
    $middleware = new HandleInertiaRequests();

    $request = Request::create('/', 'GET');

    $shared = $middleware->share($request);

    // Parent Inertia middleware shares 'errors' by default
    expect($shared)->toHaveKey('errors');
});
