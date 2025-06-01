<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use App\Models\User;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Pages\EditRecord;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('change_password')
                ->label('Change Password')
                ->form([
                    TextInput::make('password')
                        ->label('New Password')
                        ->password()
                        ->markAsRequired()
                        ->rule('required')
                        ->minLength(8),
                ])
                ->action(fn (array $data, User $record) => $record->update($data))
                ->hidden(fn (User $record) => $record->trashed())
                ->requiresConfirmation()
                ->color('warning'),
            RestoreAction::make(),
            ActionGroup::make([
                DeleteAction::make(),
                ForceDeleteAction::make(),

            ])
                ->label('Danger Actions')
                ->icon('heroicon-o-ellipsis-vertical'),
        ];
    }

    protected function saved(): void
    {
        parent::saved();

        \Filament\Notifications\Notification::make()
            ->title('Password changed successfully')
            ->success()
            ->send();
    }

    protected function getFormActions(): array
    {
        return $this->getRecord()->trashed() ? [] : parent::getFormActions();
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
