<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class StoreEmojiReactionRequest extends FormRequest
{
    /**
     * @return list<string>
     */
    public static function allowedEmojis(): array
    {
        return ['❤️', '🔥', '🚀', '🤯'];
    }

    /**
     * @return array<string, array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'emoji' => ['required', 'string', Rule::in(self::allowedEmojis())],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'emoji.required' => 'Please select an emoji reaction.',
            'emoji.in' => 'The selected emoji is not allowed.',
        ];
    }
}
