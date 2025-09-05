<?php

namespace App\Actions;

use SimpleSoftwareIO\QrCode\Facades\QrCode;

class GenerateQR
{
    public function __invoke(string $code): string
    {
        $qr = QrCode::size(300)
            ->format('png')
            ->errorCorrection('H') 
            ->style('round')        
            ->eye('circle')        
            ->margin(1)            
            ->generate($code);

        return 'data:image/png;base64,'.base64_encode($qr);
    }
}