<?php

namespace App\Actions;
use setasign\Fpdi\Fpdi;
use Mpdf\Mpdf;
use Illuminate\Support\Facades\Log;

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

            Log::info('AddFirstPageToPdf completed successfully', ['output_path' => $outputPath]);
        } catch (\Exception $e) {
            Log::error('AddFirstPageToPdf failed', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        } finally {
            $action->cleanupTemporaryFiles($newPagePdfPath, $processedPdfPath);
        }
    }

    private function generateFirstPagePdf(string $newPageHtml, string $outputPath): void
    {
        Log::info('generateFirstPagePdf: Starting PDF generation', ['output_path' => $outputPath]);

        // Setup temp directory
        $tempDir = storage_path('app/mpdf_temp');
        if (!is_dir($tempDir)) {
            @mkdir($tempDir, 0775, true);
            Log::info('generateFirstPagePdf: Created temp directory', ['temp_dir' => $tempDir]);
        }

        // Verify temp directory is writable
        if (!is_writable($tempDir)) {
            Log::warning('generateFirstPagePdf: Temp directory is not writable', ['temp_dir' => $tempDir]);
        } else {
            Log::info('generateFirstPagePdf: Temp directory is writable', ['temp_dir' => $tempDir]);
        }

        // Check for Arabic content
        $hasArabic = $this->containsArabic($newPageHtml);
        Log::info('generateFirstPagePdf: Arabic content check', ['has_arabic' => $hasArabic]);

        try {
            $mpdf = new Mpdf([
                'mode' => 'utf-8',
                'format' => 'A4',
                'orientation' => 'P',
                'rtl' => false,
                'useSubstitutions' => true,
                'default_font' => 'dejavusans',
                'autoScriptToLang' => true,
                'autoLangToFont' => true,
                'tempDir' => $tempDir,
            ]);

            Log::info('generateFirstPagePdf: mPDF instance created successfully');

            $css = $this->getInvoiceCss();
            Log::debug('generateFirstPagePdf: CSS prepared', ['css_length' => strlen($css)]);

            $mpdf->WriteHTML($css, \Mpdf\HTMLParserMode::HEADER_CSS);
            Log::debug('generateFirstPagePdf: CSS written to mPDF');

            $mpdf->WriteHTML($newPageHtml, \Mpdf\HTMLParserMode::HTML_BODY);
            Log::debug('generateFirstPagePdf: HTML body written to mPDF', ['html_length' => strlen($newPageHtml)]);

            $mpdf->Output($outputPath, \Mpdf\Output\Destination::FILE);

            if (file_exists($outputPath)) {
                $fileSize = filesize($outputPath);
                Log::info('generateFirstPagePdf: PDF generated successfully', [
                    'output_path' => $outputPath,
                    'file_size' => $fileSize,
                ]);
            } else {
                Log::error('generateFirstPagePdf: PDF file was not created', ['output_path' => $outputPath]);
            }
        } catch (\Exception $e) {
            Log::error('generateFirstPagePdf: Exception occurred', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    private function containsArabic(string $html): bool
    {
        return (bool) preg_match('/[\x{0600}-\x{06FF}\x{0750}-\x{077F}\x{08A0}-\x{08FF}]/u', $html);
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
