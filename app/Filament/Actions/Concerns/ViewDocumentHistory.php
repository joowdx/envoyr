<?php

namespace App\Filament\Actions\Concerns;

use App\Models\Document;
use Illuminate\Support\HtmlString;

trait ViewDocumentHistory
{
    protected function getDocumentHistory(Document $record): HtmlString
    {
        $history = collect();

        // Add creation
        $history->push([
            'date' => $record->created_at,
            'event' => 'Document Created',
            'user' => $record->user->name,
            'details' => "Document '{$record->title}' was created.",
        ]);

        // Add publication if published
        if ($record->isPublished()) {
            $history->push([
                'date' => $record->published_at,
                'event' => 'Document Published',
                'user' => $record->publishedBy->name,
                'details' => "Document was published.",
            ]);
        }

        // Add transmission if transmitted
        if ($record->transmitted_at) {
            $history->push([
                'date' => $record->transmitted_at,
                'event' => 'Document Transmitted',
                'user' => $record->user->name,
                'details' => "Document was transmitted to {$record->transmittedToOffice->name}" .
                    ($record->transmittedToSection ? " ({$record->transmittedToSection->name})" : "") .
                    " by {$record->liaison->name}.",
            ]);
        }

        // Sort by date in descending order
        $history = $history->sortByDesc('date');

        $html = '<div class="space-y-4">';
        foreach ($history as $item) {
            $html .= <<<HTML
                <div class="flex gap-4">
                    <div class="flex-shrink-0">
                        <div class="w-2 h-2 mt-2 rounded-full bg-primary-500"></div>
                    </div>
                    <div class="flex-1">
                        <div class="text-sm font-medium text-gray-900">{$item['event']}</div>
                        <div class="text-sm text-gray-500">{$item['details']}</div>
                        <div class="mt-1 text-xs text-gray-400">
                            By {$item['user']} on {$item['date']->format('M d, Y h:i A')}
                        </div>
                    </div>
                </div>
            HTML;
        }
        $html .= '</div>';

        return new HtmlString($html);
    }
}
