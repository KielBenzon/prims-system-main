<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class BurialJob implements ShouldQueue
{
    use Queueable;

    protected $burial;
    protected $file;
    /**
     * Create a new job instance.
     */
    public function __construct($burial, $file)
    {
        $this->burial = $burial;
        $this->file = $file;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $file = $this->file->getClientOriginalExtension();
        $fileName = 'burial_' . time() . '.' . $file;
        $this->file->move(public_path('assets/documents'), $fileName);
        $this->burial->file_burial = $fileName;
        $this->burial->save();
    }
}
