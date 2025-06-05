<?php

namespace App\Filament\Forms;

use Filament\Forms\Components\FileUpload;

class AttachmentFileUpload extends FileUpload
{
    public static function make(string $name = 'file'): static
    {
        $static = app(static::class, ['name' => $name]);

        $static->configure();

        return $static;
    }

    protected function setUp(): void
    {
        $this
            ->storeFileNamesIn('path')
            ->directory('attachments')
            ->previewable(false)
            ->moveFiles()
            ->maxSize(1024 * 12)
            ->downloadable()
            ->rule('clamav')
            ->required();
    }
}
