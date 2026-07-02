import { usePage } from '@inertiajs/react';
import { useCallback, useEffect, useState } from 'react';
import { store } from '@/actions/App/Http/Controllers/EmojiReactionController';
import { getEcho } from '@/lib/echo';

const EMOJIS = ['❤️', '🔥', '🚀', '🤯'] as const;

type FloatingEmoji = {
    id: number;
    emoji: string;
    left: number;
};

function getXsrfToken(): string {
    const match = document.cookie.match(/XSRF-TOKEN=([^;]+)/);

    if (!match?.[1]) {
        return '';
    }

    return decodeURIComponent(match[1]);
}

export default function EmojiReactions() {
    const { reverb } = usePage().props;
    const [floatingEmojis, setFloatingEmojis] = useState<FloatingEmoji[]>([]);

    const spawnEmoji = useCallback((emoji: string) => {
        const id = Date.now() + Math.random();
        const left = 10 + Math.random() * 80;

        setFloatingEmojis((current) => [...current, { id, emoji, left }]);

        window.setTimeout(() => {
            setFloatingEmojis((current) =>
                current.filter((item) => item.id !== id),
            );
        }, 2500);
    }, []);

    useEffect(() => {
        const echo = getEcho(reverb);

        if (!echo) {
            return;
        }

        const channel = echo.channel('reactions');

        channel.listen('.EmojiReactionSent', (event: { emoji: string }) => {
            spawnEmoji(event.emoji);
        });

        return () => {
            channel.stopListening('.EmojiReactionSent');
            echo.leave('reactions');
        };
    }, [reverb, spawnEmoji]);

    const sendReaction = async (emoji: string): Promise<void> => {
        spawnEmoji(emoji);

        const echo = getEcho(reverb);
        const socketId = echo?.socketId();

        await fetch(store.url(), {
            method: 'POST',
            headers: {
                Accept: 'application/json',
                'Content-Type': 'application/json',
                'X-XSRF-TOKEN': getXsrfToken(),
                ...(socketId ? { 'X-Socket-ID': socketId } : {}),
            },
            body: JSON.stringify({ emoji }),
            credentials: 'same-origin',
        });
    };

    return (
        <>
            <div className="pointer-events-none fixed inset-0 z-40 overflow-hidden">
                {floatingEmojis.map((item) => (
                    <span
                        key={item.id}
                        className="emoji-float absolute bottom-24 text-4xl"
                        style={{ left: `${item.left}%` }}
                    >
                        {item.emoji}
                    </span>
                ))}
            </div>

            <div className="fixed bottom-6 left-1/2 z-50 flex -translate-x-1/2 items-center gap-2 rounded-full border border-[#19140035] bg-white/90 px-4 py-2 shadow-lg backdrop-blur-sm dark:border-[#3E3E3A] dark:bg-[#161615]/90">
                {EMOJIS.map((emoji) => (
                    <button
                        key={emoji}
                        type="button"
                        onClick={() => void sendReaction(emoji)}
                        className="rounded-full p-2 text-2xl transition-transform hover:scale-125 active:scale-95"
                        aria-label={`Send ${emoji} reaction`}
                    >
                        {emoji}
                    </button>
                ))}
            </div>
        </>
    );
}
