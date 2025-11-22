<?php

namespace App\Jobs;

use App\Actions\AddFirstPageToPdfAction;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class AddFirstPageToPdfJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of seconds the job can run before timing out.
     */
    public int $timeout = 120;

    /**
     * Create a new job instance.
     */
    public function __construct(
        protected string $existingPdfPath,
        protected string $newPageHtml,
        protected string $filename
    ) {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        AddFirstPageToPdfAction::addFirstPageToPdf($this->existingPdfPath, $this->newPageHtml, $this->filename);
    }
}

