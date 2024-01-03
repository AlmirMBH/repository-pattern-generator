<?php

namespace App\Console\Commands\CreateQueryLoggerCommand;

use Illuminate\Console\Command;

class CreateQueryLogger extends Command
{
    protected $signature = 'make:query-logger-middleware {name : Name of the query logger}';
    protected $description = 'Command description';


    public function handle(): void
    {
        $queryLoggerName = ucfirst($this->argument('name'));

        $fileExists = file_exists(base_path('app/Http/Middleware') . '/' . $queryLoggerName . '.php');

        if ($fileExists) {
            $this->info('Query logger middleware already exists!');
            return;
        }

        $contents = file_get_contents(base_path('app/Console/Commands/CreateQueryLoggerCommand/ClassTemplates/middleware.stub'));
        file_put_contents(base_path('app/Http/Middleware') . '/' . $queryLoggerName . '.php', $contents);

        $this->info('Query logger middleware created!');

        // TODO: Add middleware to Kernel.php
        // TODO: Create a controller and service to fetch logs
        // TODO: Generate middleware and route in it to fetch query logs (pagination, sorting, filtering, etc.);
        // TODO: Define the key variables in .env and add log channel dynamically
        // TODO: Add a command to create a log channel
    }
}
