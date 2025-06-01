<?php

namespace App\Filament\Auth\Pages;

use App\Enums\UserRole;
use App\Filament\Auth\Concerns\BaseAuthPage;
use App\Http\Middleware\Approve;
use App\Http\Middleware\Authenticate;
use App\Http\Middleware\Verify;
use App\Models\Office;
use App\Models\Section;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\SimplePage;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Support\Facades\Auth;

class Affilation extends SimplePage implements HasMiddleware
{
    use BaseAuthPage;

    protected static string $layout = 'filament-panels::components.layout.base';

    protected static string $view = 'filament.auth.pages.affiliation';

    public ?array $data = [];

    public static function getSlug(): string
    {
        return 'user-affiliation/prompt';
    }

    public static function getRelativeRouteName(): string
    {
        return 'auth.user-affiliation.prompt';
    }

    public static function middleware(): array
    {
        return [
            Authenticate::class,
            Verify::class,
            Approve::class,
        ];
    }

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
                    ->live()
                    ->afterStateUpdated(fn (callable $set) => $set('section_id', null))
                    ->hidden(Auth::user()->office_id !== null)
                    ->hintAction(
                        Action::make('create')
                            ->modalWidth('lg')
                            ->form([
                                TextInput::make('name')
                                    ->markAsRequired()
                                    ->rule('required')
                                    ->placeholder('Enter new office name'),
                                TextInput::make('acronym')
                                    ->markAsRequired()
                                    ->rule('required')
                                    ->placeholder('Enter new office acronym'),
                            ])
                            ->action(function (array $data, callable $set) {
                                $office = Office::create(['name' => $data['name'], 'acronym' => $data['acronym']]);

                                $set('office_id', $office->id);
                            }),
                    ),
                Select::make('section_id')
                    ->label('Section')
                    ->options(function (callable $get) {
                        return Section::query()
                            ->where('office_id', $get('office_id') ?? Auth::user()->office_id)
                            ->pluck('name', 'id');
                    })
                    ->required()
                    ->searchable()
                    ->hintAction(
                        Action::make('create')
                            ->modalWidth('lg')
                            ->disabled(fn (callable $get) => $get('office_id') === null)
                            ->form([
                                TextInput::make('name')
                                    ->markAsRequired()
                                    ->rule('required')
                                    ->placeholder('Enter new section name'),
                            ])
                            ->action(function (array $data, callable $get, callable $set) {
                                $section = Section::create(['name' => $data['name'], 'office_id' => $get('office_id')]);

                                $set('section_id', $section->id);
                            }),
                    ),
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

    public function getTitle(): string
    {
        return 'Affiliation Required';
    }

    public function getSubheading(): ?string
    {
        return 'You need to be affiliated with an office and section before you are given access to the system.';
    }

    public function isAdministrator(): bool
    {
        return Auth::user()?->role === UserRole::ADMINISTRATOR || Auth::user()?->role === UserRole::ROOT;
    }
}
