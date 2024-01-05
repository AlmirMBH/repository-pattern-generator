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
        $this->addEnvVariables();

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

    // TODO: Finish this method
    private function addMiddlewareToKernel(string $queryLoggerName): void
    {
        $fileContents = file_get_contents(base_path('app/Http/Kernel.php'));
        $middlewareExists = Str::contains($fileContents, $queryLoggerName);

        if ($middlewareExists) {
            $this->info('Query logger middleware already exists in Kernel.php!');
        } else {
            $middleware = "protected \$middleware = [
        \App\Http\Middleware\{$queryLoggerName}::class,
    ];";
        }
    }

    private function addEnvVariables(): void
    {
        // TODO: Add default values to the variables in .env
        // LOW_PERFORMANCE_QUERY_MEMORY = 30000000 && LOW_PERFORMANCE_QUERY_TIME = 100
        // MID_PERFORMANCE_QUERY_MEMORY = 20000000 && MID_PERFORMANCE_QUERY_TIME = 50
        // HIGH_PERFORMANCE_QUERY_MEMORY = 12000000 && HIGH_PERFORMANCE_QUERY_TIME = 20
        // QUERY_LOGGER_ENVIRONMENT query_logger_env
        $envFileContents = file_get_contents(base_path('.env'));

        $variablesToCheck = [
            'QUERY_LOGGER_ENV',
            'LOW_PERFORMANCE_QUERY_MEMORY',
            'LOW_PERFORMANCE_QUERY_TIME',
            'MID_PERFORMANCE_QUERY_MEMORY',
            'MID_PERFORMANCE_QUERY_TIME',
            'HIGH_PERFORMANCE_QUERY_MEMORY',
            'HIGH_PERFORMANCE_QUERY_TIME'
        ];

        $missingVariables = false;

        foreach ($variablesToCheck as $variable) {
            if (!Str::contains($envFileContents, $variable)) {
                $missingVariables = true;
                if ($variable === 'QUERY_LOGGER_ENV') {
                    $envFileContents .= "$variable=local\n";
                    $this->info('QUERY_LOGGER_ENV added to .env!');
                } else {
                    $envFileContents .= "$variable=\n";
                    $this->info("$variable added to .env!");
                }
            }
        }

        if ($missingVariables) {
            file_put_contents(base_path('.env'), $envFileContents);
        } else {
            $this->info('All query logger environment variables already exist in .env!');
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
