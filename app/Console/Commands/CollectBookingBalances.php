<?php

namespace App\Console\Commands;

use App\Payments\BalanceCollector;
use Illuminate\Console\Command;

/**
 * Daily balance collection (TPX decision 3): D-45 request, D-37 and D-32 reminders, D-30 formal notice
 * and a 48-hour cure period. Does nothing unless BOOKING_BALANCE_COLLECTION_ENABLED is on. Never cancels.
 *
 *   php artisan bookings:collect-balances --dry-run   list what would be sent, send nothing
 */
class CollectBookingBalances extends Command
{
    protected $signature = 'bookings:collect-balances {--dry-run : List what would be sent without sending or recording anything}';

    protected $description = 'Send balance requests, reminders and overdue notices for deposit bookings';

    public function handle(BalanceCollector $collector): int
    {
        $dryRun = (bool) $this->option('dry-run');
        if (!$dryRun && !BalanceCollector::enabled()) {
            $this->info('Balance collection is switched off (BOOKING_BALANCE_COLLECTION_ENABLED) or its migration has not run.');

            return self::SUCCESS;
        }

        $lines = $collector->run($dryRun);
        foreach ($lines as $line) {
            $this->line(($dryRun ? '[dry run] ' : '') . $line);
        }
        $this->info(count($lines) . ' action(s)' . ($dryRun ? ' would be taken.' : '.'));

        return self::SUCCESS;
    }
}
