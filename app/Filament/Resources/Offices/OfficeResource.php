<?php

namespace App\Filament\Resources\Offices;

use BackedEnum;
use App\Models\Office;
use App\Enums\UserRole;
use Filament\Tables\Table;
use App\Models\OfficeAction;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\Offices\Pages\EditOffice;
use App\Filament\Resources\Offices\Pages\ListOffices;
use App\Filament\Resources\Offices\Pages\CreateOffice;
use App\Filament\Resources\Offices\Schemas\OfficeForm;
use App\Filament\Resources\Offices\Tables\OfficesTable;
use App\Filament\Resources\Offices\RelationManagers\ActionsRelationManager;
use App\Filament\Resources\Offices\RelationManagers\SectionRelationManager;

class OfficeResource extends Resource
{
    protected static ?string $model = Office::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingOffice2;

    protected static ?string $recordTitleAttribute = 'Office';

    public static function form(Schema $schema): Schema
    {
        return OfficeForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return OfficesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            SectionRelationManager::class,
            ActionsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListOffices::route('/'),
            'create' => CreateOffice::route('/create'),
            'edit' => EditOffice::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        $user = auth()->user();
        $query = parent::getEloquentQuery();

        if ($user->role === UserRole::ROOT) {
            return $query;
        }

        return $query->where('id', $user->office_id);
    }

    public static function getNavigationUrl(): string
    {
        $user = auth()->user();

        if ($user && $user->role === UserRole::ADMINISTRATOR && $user->office_id) {
            return static::getUrl('edit', ['record' => $user->office_id]);
        }

        return static::getUrl('index');
    }
}
