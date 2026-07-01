<?php

declare(strict_types=1);

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

final class WriteQueueTestMessage implements ShouldQueue
{
    use Queueable;

    public function handle(): void
    {
        Log::info('Queue test: WriteQueueTestMessage job executed successfully.');
    }
}
