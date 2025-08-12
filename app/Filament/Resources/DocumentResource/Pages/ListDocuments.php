<?php

namespace App\Filament\Resources\DocumentResource\Pages;

use App\Filament\Actions\ReceiveDocumentAction;
use App\Filament\Resources\DocumentResource;
use App\Models\Document;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class ListDocuments extends ListRecords
{
    protected static string $resource = DocumentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ReceiveDocumentAction::make(),
            Actions\CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        return [
            // 'all' => Tab::make('All Documents')
            //     ->icon('heroicon-o-document-duplicate')
            //     ->badge(fn () => Document::count()),

            'office' => Tab::make('Office Documents')
                ->icon('heroicon-o-building-office')
                ->modifyQueryUsing(fn (Builder $query) => $query
                    ->where('office_id', Auth::user()->office_id)
                )
                ->badge(fn () => Document::where('office_id', Auth::user()->office_id)->count()),

            'incoming' => Tab::make('Incoming Documents')
                ->icon('heroicon-o-inbox-arrow-down')
                ->modifyQueryUsing(fn (Builder $query) => $query
                    ->whereHas('transmittals', function (Builder $subQuery) {
                        $subQuery->where('to_office_id', Auth::user()->office_id)  // ✅ Use to_office_id
                            ->whereNull('received_at'); // Not yet received
                    })
                )
                ->badge(fn () => Document::whereHas('transmittals', function (Builder $query) {
                    $query->where('to_office_id', Auth::user()->office_id)  // ✅ Use to_office_id
                        ->whereNull('received_at');
                })->count()),

            'received' => Tab::make('Received Documents')
                ->icon('heroicon-o-check-circle')
                ->modifyQueryUsing(fn (Builder $query) => $query
                    ->whereHas('transmittals', function (Builder $subQuery) {
                        $subQuery->where('to_office_id', Auth::user()->office_id)  // ✅ Use to_office_id
                            ->whereNotNull('received_at'); // Already received
                    })
                )
                ->badge(fn () => Document::whereHas('transmittals', function (Builder $query) {
                    $query->where('to_office_id', Auth::user()->office_id)  // ✅ Use to_office_id
                        ->whereNotNull('received_at');
                })->count()),

            // 'draft' => Tab::make('Draft Documents')
            //     ->icon('heroicon-o-pencil-square')
            //     ->modifyQueryUsing(fn (Builder $query) => $query
            //         ->where('status', 'draft')
            //         ->where('office_id', Auth::user()->office_id)
            //     )
            //     ->badge(fn () => Document::where('status', 'draft')
            //         ->where('office_id', Auth::user()->office_id)
            //         ->count()),

            // 'published' => Tab::make('Published Documents')
            //     ->icon('heroicon-o-eye')
            //     ->modifyQueryUsing(fn (Builder $query) => $query
            //         ->where('status', 'published')
            //         ->where('office_id', Auth::user()->office_id)
            //     )
            //     ->badge(fn () => Document::where('status', 'published')
            //         ->where('office_id', Auth::user()->office_id)
            //         ->count()),
        ];
    }
}
