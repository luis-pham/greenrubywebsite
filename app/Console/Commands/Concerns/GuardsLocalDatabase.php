<?php

namespace App\Console\Commands\Concerns;

use Illuminate\Support\Facades\DB;

/**
 * The draft importers' write guard (phase 2B/2C): --apply only on a local green_ruby_i18n_* clone (or the in-memory
 * test database). Production use needs --i-know-this-is-production and a separate authorisation.
 */
trait GuardsLocalDatabase
{
    /** Why the connection is not a local clone, or null when it is (or the in-memory test database). */
    protected function notLocal(): ?string
    {
        $connection = DB::connection();
        $driver = $connection->getDriverName();
        $database = (string) $connection->getDatabaseName();
        if ($driver === 'sqlite' && app()->environment('testing')) {
            return null;
        }
        if (app()->environment('production')) {
            return 'APP_ENV is production';
        }
        if (!str_starts_with($database, 'green_ruby_i18n_')) {
            return "database '{$database}' is not a local green_ruby_i18n_* clone";
        }
        $host = (string) config("database.connections.{$connection->getName()}.host");
        if (!in_array($host, ['127.0.0.1', 'localhost', '::1'], true)) {
            return "database host '{$host}' is not local";
        }
        $expected = getenv('I18N_DB');
        if ($expected !== false && $expected !== '' && $expected !== $database) {
            return "I18N_DB={$expected} but the connection uses '{$database}'";
        }

        return null;
    }
}
