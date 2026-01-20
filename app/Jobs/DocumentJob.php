<?php

namespace App\Jobs;

use App\Models\Document;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use thiagoalessio\TesseractOCR\TesseractOCR;

class DocumentJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $document;
    protected $path;

    /**
     * Create a new job instance.
     */
    public function __construct(Document $document, $path)
    {
        $this->document = $document;
        $this->path = $path;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $ocrText = (new TesseractOCR($this->path))
            ->lang('eng')
            ->run();

        $documentType = $this->determineDocumentType($ocrText);

        $this->document->document_type = $documentType;
        $this->document->save();
    }

    /**
     * Determine the document type based on OCR text.
     *
     * @param string $ocrText
     * @return string
     */
    private function determineDocumentType($ocrText)
    {
        if (stripos($ocrText, 'Baptismal') !== false) {
            return 'Baptismal Certificate';
        } elseif (stripos($ocrText, 'Marriage') !== false) {
            return 'Marriage Certificate';
        } elseif (stripos($ocrText, 'Death') !== false || stripos($ocrText, 'Burial') !== false) {
            return 'Death Certificate';
        }

        return 'Unknown Document Type';
    }
}
