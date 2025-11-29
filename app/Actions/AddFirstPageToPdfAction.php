<?php

namespace App\Actions;
use setasign\Fpdi\Fpdi;
use Mpdf\Mpdf;
class AddFirstPageToPdfAction
{
    public static function addFirstPageToPdf($existingPdfPath, $newPageHtml , $filename)
    {
        $action = new self();
        $newPagePdfPath = storage_path('app/temp_first_page.pdf');
        $processedPdfPath = null;

        try {
            // Generate the first invoice page as PDF
            $action->generateFirstPagePdf($newPageHtml, $newPagePdfPath);

            // Preprocess existing PDF for FPDI compatibility
            $pdfToUse = $existingPdfPath;
            $processedPdfPath = $action->preprocessExistingPdf($existingPdfPath);
            if ($processedPdfPath && file_exists($processedPdfPath)) {
                $pdfToUse = $processedPdfPath;
            }

            // Merge PDFs
            $outputPath = public_path('orders_files/'.$filename);
            $action->mergePdfs($newPagePdfPath, $pdfToUse, $outputPath);
        } finally {
            $action->cleanupTemporaryFiles($newPagePdfPath, $processedPdfPath);
        }
    }

    private function generateFirstPagePdf(string $newPageHtml, string $outputPath): void
    {
        $mpdf = new Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4',
            'orientation' => 'P',
            'rtl' => true,
            'autoScriptToLang' => true,
            'autoLangToFont' => true,
        ]);

        $css = $this->getInvoiceCss();
        $mpdf->WriteHTML($css, \Mpdf\HTMLParserMode::HEADER_CSS);
        $mpdf->WriteHTML($newPageHtml, \Mpdf\HTMLParserMode::HTML_BODY);
        $mpdf->Output($outputPath, \Mpdf\Output\Destination::FILE);
    }

    private function getInvoiceCss(): string
    {
        return <<<CSS
            body { font-size: 11pt; }
            p { font-size: 11pt; margin-bottom: 8pt; }
            span { font-size: 11pt; }
            .font-weight-bold { font-weight: 700; }
            .mb-2 { margin-bottom: .5rem; }
            .mb-3 { margin-bottom: 1rem; }
            .mt-3 { margin-top: 1rem; }
            .p-2 { padding: .3rem; }
            .d-block { display: block; }
            .m-auto { margin-left: auto; margin-right: auto; }
            .text-left { text-align: left; }
            .text-right { text-align: right; }
            .container { width: 100%; max-width: 1140px; margin: 0 auto; padding: 0 15px; }
            .card { border: 1px solid #dee2e6; border-radius: .25rem; }
            .card-header { background-color: #f8f9fa; border-bottom: 1px solid #dee2e6; padding: 8pt; }
            .card-header p { font-size: 15pt; font-weight: bold; margin: 0; }
            .card-body { padding: 8pt; }
            img { max-width: 100%; height: auto; }
            CSS;
    }

    private function preprocessExistingPdf(string $existingPdfPath): ?string
    {
        if (!class_exists(\App\Actions\PreProcessPdfForFPDIAction::class)) {
            return null;
        }

        try {
            $preprocessor = new PreProcessPdfForFPDIAction();
            return $preprocessor->preprocess($existingPdfPath);
        } catch (\Throwable $e) {
            // Fallback to original PDF
            return null;
        }
    }

    private function importPdfPage(Fpdi $fpdi, string $pdfPath, int $pageNumber): void
    {
        $templateId = $fpdi->importPage($pageNumber);
        $size = $fpdi->getTemplateSize($templateId);
        $fpdi->AddPage($size['orientation'], [$size['width'], $size['height']]);
        $fpdi->useTemplate($templateId);
    }

    private function mergePdfs(string $newPagePdfPath, string $existingPdfPath, string $outputPath): void
    {
        $fpdi = new Fpdi();

        // Import the new first page
        $fpdi->setSourceFile($newPagePdfPath);
        $this->importPdfPage($fpdi, $newPagePdfPath, 1);

        // Import the existing PDF pages
        $pageCount = $fpdi->setSourceFile($existingPdfPath);
        for ($i = 1; $i <= $pageCount; $i++) {
            $this->importPdfPage($fpdi, $existingPdfPath, $i);
        }

        // Save the combined PDF
        $fpdi->Output($outputPath, 'F');
    }

    private function cleanupTemporaryFiles(?string $newPagePdfPath, ?string $processedPdfPath): void
    {
        if ($newPagePdfPath && file_exists($newPagePdfPath)) {
            @unlink($newPagePdfPath);
        }
        if ($processedPdfPath && file_exists($processedPdfPath)) {
            @unlink($processedPdfPath);
        }
    }

}
