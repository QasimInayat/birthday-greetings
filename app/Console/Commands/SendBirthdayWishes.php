<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

/**
 * Kept so existing crontabs, scripts and the Run Now button keep working.
 * The real work lives in events:send.
 */
class SendBirthdayWishes extends Command
{
    protected $signature = 'birthday:send-wishes {--dry-run : List who would be messaged without sending}';

    protected $description = 'Alias of events:send (kept for backwards compatibility)';

    public function handle()
    {
        return $this->call('events:send', [
            '--dry-run' => $this->option('dry-run'),
        ]);
    }
}
