<?php

namespace App\Filament\User\Pages;

use App\Enums\UserRole;
use App\Models\Office;
use App\Models\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Components\Actions\Action;
use Filament\Actions\Action as FilamentAction;
use Filament\Facades\Filament;

class RequiredOffice extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-exclamation-triangle';

    protected static string $view = 'filament.user.pages.required-office';

    protected static bool $shouldRegisterNavigation = false;

    protected static string $layout = 'filament-panels::components.layout.base';

    public ?array $data = [];

    public function mount(): void
    {
        if (Auth::user()->office_id) {
            $this->data['office_id'] = Auth::user()->office_id;
        }

        $this->form->fill($this->data);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('office_id')
                    ->label('Office')
                    ->options(Office::pluck('name', 'id'))
                    ->required()
                    ->searchable()
                    ->preload()
                    ->live()
                    ->afterStateUpdated(fn(callable $set) => $set('section_id', null))
                    ->hidden(Auth::user()->office_id !== null),
                Select::make('section_id')
                    ->label('Section')
                    ->options(function (callable $get) {
                        $officeId = $get('office_id') ?? Auth::user()->office_id;

                        if (!$officeId) {
                            return [];
                        }

                        return Section::query()
                            ->where('office_id', $officeId)
                            ->pluck('name', 'id');
                    })
                    ->required()
                    ->searchable()
                    ->preload(),
            ])
            ->statePath('data');
    }

    public function assign(): void
    {
        Auth::user()->update($this->form->getState());

        Notification::make()
            ->title('Office and section assigned successfully')
            ->success()
            ->send();

        $this->redirect('/user');
    }

    public static function getRelativeRouteName(): string
    {
        return 'required-office';
    }

    public function getTitle(): string
    {
        return 'Access Denied';
    }

    public function getHeading(): string
    {
        return 'Access Denied';
    }

    public function getSubheading(): ?string
    {
        return 'You must be associated with both an office and a section to access this page.';
    }

    public function isAdministrator(): bool
    {
        return Auth::user()?->role === UserRole::ADMINISTRATOR || Auth::user()?->role === UserRole::ROOT;
    }

    public function logoutAction(): FilamentAction
    {
        return FilamentAction::make('logout')
            ->label('Log Out')
            ->color('primary')
            ->size('md')
            ->extraAttributes([
                'class' => 'w-full transition-colors duration-200',
                'style' => 'width: 100%;'
            ])
            ->action(function () {
                Filament::auth()->logout();
                session()->invalidate();
                session()->regenerateToken();
                return redirect('/');
            });
    }
}
