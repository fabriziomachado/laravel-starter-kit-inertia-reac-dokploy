<?php

declare(strict_types=1);

use App\Http\Requests\UpdateUserRequest;
use App\Models\User;
use Illuminate\Validation\Rules\Unique;
use Symfony\Component\HttpKernel\Exception\HttpException;

it('defines validation rules for the authenticated user', function (): void {
    $user = User::factory()->create([
        'email' => 'user@example.com',
    ]);

    $request = UpdateUserRequest::create(route('user-profile.update'), 'PATCH');
    $request->setUserResolver(fn (): User => $user);

    $rules = $request->rules();

    expect($rules)->toHaveKeys(['name', 'email'])
        ->and($rules['name'])->toContain('required', 'string', 'max:255')
        ->and($rules['email'])->toContain('required', 'string', 'lowercase', 'email', 'max:255');

    $uniqueRule = collect($rules['email'])->first(fn (mixed $rule): bool => $rule instanceof Unique);

    expect($uniqueRule)->not->toBeNull();
});

it('aborts when the user is not authenticated', function (): void {
    $request = UpdateUserRequest::create(route('user-profile.update'), 'PATCH');
    $request->setUserResolver(fn (): null => null);

    expect(fn (): array => $request->rules())
        ->toThrow(HttpException::class);
});
