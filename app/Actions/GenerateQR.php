<?php

namespace App\Actions;

use SimpleSoftwareIO\QrCode\Facades\QrCode;

class GenerateQR
{
    public function __invoke(string $code): string
    {
        $qr = QrCode::size(300)
            ->format('png')
            ->errorCorrection('H')  // Highest error correction level
            ->style('round')        // Rounded corners for better aesthetics
            ->eye('circle')         // Circular eye patterns
            ->margin(1)            // Minimal margin to maximize QR code size
            ->generate($code);

        // Return as data URI for <img> tag
        return 'data:image/png;base64,'.base64_encode($qr);
    }
}
