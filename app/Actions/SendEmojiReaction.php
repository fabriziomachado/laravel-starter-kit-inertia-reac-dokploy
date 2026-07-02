<?php

declare(strict_types=1);

namespace App\Actions;

use App\Events\EmojiReactionSent;

final readonly class SendEmojiReaction
{
    public function handle(string $emoji): void
    {
        broadcast(new EmojiReactionSent($emoji))->toOthers();
    }
}
