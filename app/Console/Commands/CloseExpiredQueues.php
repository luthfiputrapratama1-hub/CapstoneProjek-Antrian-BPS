<?php

namespace App\Console\Commands;

use App\Models\Queue;
use Illuminate\Console\Command;

class CloseExpiredQueues extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'queues:close-expired';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Menutup antrean terbengkalai dari hari-hari sebelumnya yang masih berstatus waiting/calling/serving menjadi skipped agar tidak mencemari statistik';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $today = now()->toDateString();

        $affected = Queue::where('queue_date', '<', $today)
            ->whereIn('status', ['waiting', 'calling', 'serving'])
            ->update([
                'status' => 'skipped',
            ]);

        $this->info("Berhasil menutup {$affected} antrean sisa hari sebelumnya menjadi 'skipped'.");

        return self::SUCCESS;
    }
}
