<?php

namespace App\Console\Commands\CreateQueryLoggerCommand;

use Illuminate\Console\Command;
use Illuminate\Support\Str;

class CreateQueryLogger extends Command
{
    protected $signature = 'make:query-logger {name : Name of the query logger middleware}';
    protected $description = 'Command description';


    public function handle(): void
    {
        $queryLoggerName = ucfirst($this->argument('name'));

        $this->createMiddleware($queryLoggerName);
        $this->createRoute($queryLoggerName);

        // TODO: Append the key variables in .env
        // TODO: Add logic to the controller to fetch query logs by specific search criteria
        // TODO: Add middleware to Kernel.php
        // TODO: Add a log channel to logging.php
    }

    private function createMiddleware(string $queryLoggerName): void
    {
        $fileExists = file_exists(base_path('app/Http/Middleware') . '/' . $queryLoggerName . '.php');

        if ($fileExists) {
            $this->info('Query logger middleware already exists!');
        } else {
            $contents = file_get_contents(base_path('app/Console/Commands/CreateQueryLoggerCommand/ClassTemplates/middleware.stub'));
            file_put_contents(base_path('app/Http/Middleware') . '/' . $queryLoggerName . '.php', $contents);
            $this->info('Query logger middleware created!');
        }
    }

    private function createRoute(string $queryLoggerName): void
    {
        $routesPrefix = str_replace('_', '-', Str::snake($queryLoggerName));
        $queryLoggerCamelCaseName = Str::camel($queryLoggerName);

        $middlewareAndRouteExists = $this->dataAlreadyInsertedInFile($queryLoggerName);

        if ($middlewareAndRouteExists) {
            $this->info('Query logger route already exists in api.php!');
        } else {
            $middlewareAndRoute =
                "Route::middleware('{$queryLoggerCamelCaseName}')->group(function(){
    Route::controller(App\Http\Controllers\Api\\" . $queryLoggerName . "Controller::class)->group(function () {
         Route::prefix('$routesPrefix')->group(function () {
            Route::post('/query-logs', 'getLogs')->name('getQueryLogs');
        });
    });
});";

        file_put_contents(base_path('routes/api.php'), $middlewareAndRoute, FILE_APPEND);
        $this->info('Query logger route added to api.php!');
        }
    }

    private function dataAlreadyInsertedInFile(string $queryLoggerCamelCaseName): bool
    {
        $data = $queryLoggerCamelCaseName . 'Controller';

        $fileContents = file_get_contents(base_path('routes/api.php'));

        if (Str::contains($fileContents, $data)){
            return true;
        }

        return false;
    }
}
