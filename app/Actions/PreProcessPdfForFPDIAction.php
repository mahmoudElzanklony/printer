<?php

namespace App\Actions;

use Ordinary9843\Ghostscript;

class PreProcessPdfForFPDIAction
{
    public function preprocess(string $inputPdfPath): string
    {
        if (!file_exists($inputPdfPath)) {
            throw new \Exception("Input PDF file not found: {$inputPdfPath}");
        }
        $pathInfo = pathinfo($inputPdfPath);
        $outputPdfPath = $pathInfo['dirname'] . '/' . $pathInfo['filename'] . '_preprocessed.pdf';
        try {
            copy($inputPdfPath, $outputPdfPath);
            $binPath = 'C:/PROGRA~1/gs/GS1006~1.0/bin/gswin64c.exe';
//            $binPath = '/usr/bin/gs'; // for linux
            $tmpPath = sys_get_temp_dir();
            $ghostscript = new Ghostscript($binPath, $tmpPath);
            $ghostscript->convert($outputPdfPath, 1.4);
            if (!file_exists($outputPdfPath) || filesize($outputPdfPath) == 0) {
                throw new \Exception("Failed to create preprocessed PDF file");
            }
            return $outputPdfPath;
        } catch (\Exception $e) {
            \Log::error("Ghostscript error", [
                'message' => $e->getMessage(),
                'binPath' => $binPath,
                'tmpPath' => $tmpPath,
            ]);
            if (file_exists($outputPdfPath)) {
                unlink($outputPdfPath);
            }
            throw new \Exception("Ghostscript preprocessing failed: " . $e->getMessage());
        }
    }
}
