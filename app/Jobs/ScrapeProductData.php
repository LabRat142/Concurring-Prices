<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ScrapeProductData implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $query;
    protected $stores;

    /**
     * Create a new job instance.
     */
    public function __construct($query, $stores)
    {
        $this->query = $query;
        $this->stores = escapeshellarg(json_encode($stores));
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $pythonScript = base_path('scripts/scraper.py');
        $command = escapeshellcmd("python $pythonScript '{$this->query}' '{$this->stores}'");
        shell_exec($command);
    }
}
