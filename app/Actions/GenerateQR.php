<?php

namespace App\Actions;

use SimpleSoftwareIO\QrCode\Facades\QrCode;

class GenerateQR
{
    public function __invoke(string $code, array $documentData = []): string
    {
        $data = array_merge([
            'code' => $code,
            'generated_at' => now()->toDateTimeString(),
        ], $documentData);

        $qr = QrCode::size(300)
            ->format('svg')
            ->generate(json_encode($data));

        return base64_encode($qr);
    }
}
