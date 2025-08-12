<?php

namespace App\Actions;

use App\Models\Document;
use Spatie\LaravelPdf\Facades\Pdf;

class DownloadQR
{
    public function __invoke(Document $document)
    {
        $qrCode = (new GenerateQR)->__invoke($document->code);

        $pdf = Pdf::view('pdf.qr-code', [
            'qrCode' => $qrCode,
            'code' => $document->code,
        ])
            ->format('A4');

        $tempPath = sys_get_temp_dir().'/qr-code.pdf';

        $pdf->save($tempPath);

        $content = file_get_contents($tempPath);
        unlink($tempPath);

        return base64_encode($content);
    }
}
