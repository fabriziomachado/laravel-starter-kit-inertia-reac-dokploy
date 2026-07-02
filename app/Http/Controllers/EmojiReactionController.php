<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\SendEmojiReaction;
use App\Http\Requests\StoreEmojiReactionRequest;
use Illuminate\Http\Response;

final readonly class EmojiReactionController
{
    public function store(StoreEmojiReactionRequest $request, SendEmojiReaction $action): Response
    {
        $action->handle($request->string('emoji')->value());

        return response()->noContent();
    }
}
