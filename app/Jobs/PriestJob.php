<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Models\Priest;
use Illuminate\Queue\InteractsWithQueue;

class PriestJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $data;
    protected $image;

    /**
     * Create a new job instance.
     */
    public function __construct($data, $image)
    {
        $this->data = $data;
        $this->image = $image;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        if ($this->image) {
            $file = $this->image->getClientOriginalExtension();
            $image = 'priest_' . time() . '.' . $file;
            $this->image->move(public_path('assets/priests'), $image);
            $this->data['image'] = $image;
        }

        Priest::create($this->data);
    }
}