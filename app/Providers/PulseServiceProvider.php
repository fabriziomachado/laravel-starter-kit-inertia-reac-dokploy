<?php

declare(strict_types=1);

namespace App\Providers;

use App\Models\User;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

final class PulseServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->gate();
    }

    public function gate(): void
    {
        Gate::define('viewPulse', function (?User $user): bool {
            $allowedEmails = $this->allowedEmails();

            if ($allowedEmails === [] && App::environment('local')) {
                return true;
            }

            if (! $user instanceof User) {
                return false;
            }

            return in_array($user->email, $allowedEmails, true);
        });
    }

    /**
     * @return list<string>
     */
    private function allowedEmails(): array
    {
        $raw = config('pulse.allowed_emails');

        if (! is_string($raw) || $raw === '') {
            return [];
        }

        $allowed = [];

        foreach (explode(',', $raw) as $email) {
            $trimmed = mb_trim($email);

            if ($trimmed !== '') {
                $allowed[] = $trimmed;
            }
        }

        return $allowed;
    }
}
