<?php

namespace App\Actions;
use setasign\Fpdi\Fpdi;
use Dompdf\Dompdf;
use Dompdf\Options;
class AddFirstPageToPdfAction
{
    public static function addFirstPageToPdf($existingPdfPath, $newPageHtml , $filename)
    {
        // Step 1: Generate the first page as a PDF
        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($newPageHtml);
        // $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        // Save the new page as a temporary PDF
        $newPagePdfPath = storage_path('app/temp_first_page.pdf');
        file_put_contents($newPagePdfPath, $dompdf->output());

        // Step 2: Combine the new page with the existing PDF
        $fpdi = new Fpdi();

        // Import the new first page
        $newPageCount = $fpdi->setSourceFile($newPagePdfPath);
        for ($i = 1; $i <= $newPageCount; $i++) {
            $templateId = $fpdi->importPage($i);
            $size = $fpdi->getTemplateSize($templateId);

            $fpdi->AddPage($size['orientation'], [$size['width'], $size['height']]);
            $fpdi->useTemplate($templateId);
        }

        // Import the existing PDF pages
        $pageCount = $fpdi->setSourceFile($existingPdfPath);
        for ($i = 1; $i <= $pageCount; $i++) {
            $templateId = $fpdi->importPage($i);
            $size = $fpdi->getTemplateSize($templateId);

            $fpdi->AddPage($size['orientation'], [$size['width'], $size['height']]);
            $fpdi->useTemplate($templateId);
        }

        // Step 3: Save the combined PDF
        $outputPath = public_path('orders_files/'.$filename);
        $fpdi->Output($outputPath, 'F');

        // Clean up temporary file
        unlink($newPagePdfPath);

    }

}
