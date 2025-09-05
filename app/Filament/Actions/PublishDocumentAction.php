<?php

namespace App\Filament\Actions;

use App\Models\Document;
use Exception;
use Filament\Actions\Action;
use Illuminate\Support\Facades\DB;

class PublishDocumentAction extends Action
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->name('publish-document');

        $this->label('Publish');

        $this->icon('heroicon-o-arrow-up-tray');

        $this->color('success');

        $this->requiresConfirmation();

        $this->modalHeading('Publish document');

        $this->modalDescription('Are you sure you want to publish this document?');

        $this->modalIcon('heroicon-o-arrow-up-tray');

        $this->action(function (Document $record): void {
            try {
                DB::transaction(function () use ($record) {
                    if ($record->isPublished()) {
                        throw new Exception('This document is already published.');
                    }

                    $record->update([
                        'published_at' => now(),
                    ]);
                });

                $this->success();
            } catch (Exception) {
                $this->failure();
            }
        });

        $this->visible(fn (Document $record) => $record->isDraft());

        $this->successNotificationTitle('Document published successfully');

        $this->failureNotificationTitle('Publishing failed');
    }
}