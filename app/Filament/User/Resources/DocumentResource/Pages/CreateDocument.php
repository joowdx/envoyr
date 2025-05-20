<?php

namespace App\Filament\User\Resources\DocumentResource\Pages;

use App\Filament\User\Resources\DocumentResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateDocument extends CreateRecord
{
    protected static string $resource = DocumentResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_id'] = Auth::user()->id;
        $data['office_id'] = Auth::user()->office_id;
        $data['section_id'] = Auth::user()->section_id;
        return $data;
    }
}
