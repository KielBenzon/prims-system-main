<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Foundation\Queue\QueuesJobs;
use App\Models\Donation;

class DonationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, SerializesModels, QueuesJobs;

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
            $fileName = 'transaction_' . time() . '.' . $file;
            $this->image->move(public_path('assets/transactions'), $fileName);
            $this->data['transaction_id'] = $fileName;
        }

        Donation::create($this->data);
    }
}
