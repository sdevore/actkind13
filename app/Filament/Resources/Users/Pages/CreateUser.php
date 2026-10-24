<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use App\Models\User;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    protected bool $shouldSendPasswordSetLink = false;

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (filled($data['password'] ?? null)) {
            return $data;
        }

        $this->shouldSendPasswordSetLink = true;

        return [...$data, 'password' => Str::password(32)];
    }

    protected function afterCreate(): void
    {
        if (! $this->shouldSendPasswordSetLink) {
            return;
        }

        /** @var User $user */
        $user = $this->record;

        $status = Password::sendResetLink(['email' => $user->email]);

        if ($status !== Password::ResetLinkSent) {
            Notification::make()
                ->title("Couldn't email a set-password link to {$user->email}")
                ->body('Set a password for them on the edit page instead.')
                ->warning()
                ->send();

            return;
        }

        Notification::make()
            ->title("Set-password link emailed to {$user->email}")
            ->success()
            ->send();
    }
}
