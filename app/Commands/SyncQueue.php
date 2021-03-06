<?php namespace AlertME\Commands;

use AlertME\DataSource;
use AlertME\Sync;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;

class SyncQueue extends Command implements ShouldQueue {

    use InteractsWithQueue, SerializesModels;

    public $sync_id;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct($sync_id)
    {
        $this->sync_id = $sync_id;
    }

    /**
     * Execute the command.
     *
     * @return void
     */
    public function handle()
    {
        \Log::info('[' . $this->job->getJobId() . ':' . $this->attempts() . '] Sync started.');

        ini_set('memory_limit', '256M');

        if ($this->attempts() > 3) {
            \Log::info('[' . $this->job->getJobId() . ':' . $this->attempts() . '] Sync failed.');

        } else {

            $sync = Sync::find($this->sync_id);
            $datasources = DataSource::where('config_status', 1)->get();

            foreach ($datasources as $datasource) {
                $datasource->syncData($sync);
            }


            // TODO: Check if all data sources synced successfully


            $sync->sync_status = 1;
            $sync->save();

            \Log::info('[' . $this->job->getJobId() . ':' . $this->attempts() . '] Sync completed.');

        }

        return;
    }

}
