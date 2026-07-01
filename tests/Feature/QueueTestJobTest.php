<?php

declare(strict_types=1);

use App\Jobs\WriteQueueTestMessage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;

it('dispatches the queue test job from the job route', function (): void {
    Queue::fake();

    $response = $this->get('/job');

    $response->assertOk()
        ->assertJson(['message' => 'Job despachado para a fila.']);

    Queue::assertPushed(WriteQueueTestMessage::class);
});

it('writes a message to the log when the queue test job is handled', function (): void {
    Log::shouldReceive('info')
        ->once()
        ->with('Queue test: WriteQueueTestMessage job executed successfully.');

    (new WriteQueueTestMessage)->handle();
});
