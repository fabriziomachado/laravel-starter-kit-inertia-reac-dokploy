<?php

declare(strict_types=1);

use App\Events\EmojiReactionSent;
use Illuminate\Support\Facades\Event;

it('broadcasts a valid emoji reaction', function (): void {
    Event::fake([EmojiReactionSent::class]);

    $response = $this->postJson(route('reactions.store'), [
        'emoji' => '❤️',
    ]);

    $response->assertNoContent();

    Event::assertDispatched(EmojiReactionSent::class, function (EmojiReactionSent $event): bool {
        return $event->emoji === '❤️';
    });
});

it('uses a public broadcast event name for echo listeners', function (): void {
    $event = new EmojiReactionSent('🔥');

    expect($event->broadcastAs())->toBe('EmojiReactionSent');
});

it('rejects an invalid emoji reaction', function (): void {
    Event::fake([EmojiReactionSent::class]);

    $response = $this->postJson(route('reactions.store'), [
        'emoji' => '👎',
    ]);

    $response->assertUnprocessable();
    $response->assertJsonValidationErrors(['emoji']);

    Event::assertNotDispatched(EmojiReactionSent::class);
});

it('rate limits emoji reactions', function (): void {
    Event::fake([EmojiReactionSent::class]);

    for ($attempt = 0; $attempt < 30; $attempt++) {
        $this->postJson(route('reactions.store'), [
            'emoji' => '🔥',
        ])->assertNoContent();
    }

    $response = $this->postJson(route('reactions.store'), [
        'emoji' => '🔥',
    ]);

    $response->assertTooManyRequests();

    Event::assertDispatchedTimes(EmojiReactionSent::class, 30);
});

it('shares reverb client configuration via inertia', function (): void {
    config([
        'broadcasting.connections.reverb.key' => 'test-app-key',
        'broadcasting.connections.reverb.client.host' => 'reverb.test',
        'broadcasting.connections.reverb.client.port' => 443,
        'broadcasting.connections.reverb.client.scheme' => 'https',
    ]);

    $response = $this->get(route('home'));

    $response->assertSuccessful();
    $response->assertInertia(fn ($page) => $page
        ->component('welcome')
        ->where('reverb.key', 'test-app-key')
        ->where('reverb.host', 'reverb.test')
        ->where('reverb.port', 443)
        ->where('reverb.scheme', 'https')
    );
});
