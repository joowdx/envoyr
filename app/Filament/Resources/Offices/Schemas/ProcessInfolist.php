<?php

// filepath: /Users/johnfordleonielbalignot/envoyr/envoyr/app/Filament/Resources/Offices/Schemas/ProcessInfolist.php

namespace App\Filament\Resources\Offices\Schemas;

use App\Models\ActionType;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Infolists\Components\TextEntry;

class ProcessInfolist
{
    public static function schema(): array
    {
        return [
            Section::make('Process Information')
                ->description('Document workflow process configuration for this office')
                ->schema([
                    Grid::make(2)
                        ->schema([
                            TextEntry::make('classification.name')
                                ->label('Document Classification')
                                ->badge()
                                ->color('warning'),
                            
                            TextEntry::make('office.name')
                                ->label('Processing Office')
                                ->badge()
                                ->color('success')
                                ->icon('heroicon-o-building-office'),
                        ]),
                ])
                ->columns(1),

            Section::make('Available Office Actions')
                ->description('Actions that this process undergoes.')
                ->schema([
                    TextEntry::make('available_actions')
                        ->label('Actions')
                        ->getStateUsing(function ($record) {
                            // Get all active ActionTypes for this office from the Document Tracking System
                            $actionTypes = ActionType::where('office_id', $record->office_id)
                                ->where('is_active', true)
                                ->get();

                            if ($actionTypes->isEmpty()) {
                                return '<div class="text-center py-6 text-gray-500">
                                    <svg class="mx-auto h-12 w-12 text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                    <p class="text-sm">No actions configured for this office</p>
                                </div>';
                            }
                            
                            // Display actions as enhanced cards
                            $html = '<div class="grid gap-3">';
                            
                            foreach ($actionTypes as $actionType) {
                                $actionName = $actionType->name ?? 'Unnamed Action';
                                $statusName = $actionType->status_name ?? 'No Status';
                                
                                $html .= '<div class="flex items-center justify-between p-4 bg-gradient-to-r from-blue-50 to-indigo-50 rounded-lg border border-blue-200 hover:shadow-md transition-all duration-200">
                                    <div class="flex items-center space-x-3">
                                        <div class="flex-shrink-0 w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                                            <svg class="w-4 h-4 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd" />
                                            </svg>
                                        </div>
                                        <div>
                                            <h4 class="font-semibold text-gray-900 text-sm">' . $actionName . '</h4>
                                            <p class="text-xs text-gray-600">Document processing action</p>
                                        </div>
                                    </div>
                                    <div class="flex items-center space-x-2">
                                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                                        </svg>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-600 text-white">
                                            ' . $statusName . '
                                        </span>
                                    </div>
                                </div>';
                            }
                            
                            $html .= '</div>';
                            
                            // Add simple count summary
                            $html .= '<div class="mt-4 text-center">
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-gray-100 text-gray-800">
                                    ' . $actionTypes->count() . ' action(s) available
                                </span>
                            </div>';
                            
                            return $html;
                        })
                        ->html()
                        ->columnSpanFull(),
                ]),
        ];
    }
}
