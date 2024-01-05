<?php

namespace App\Console\Commands\CreateQueryLoggerCommand;

use App\Console\Commands\CreateQueryLoggerCommand\Constants\Constants;
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
        $this->addMiddlewareToKernel($queryLoggerName);
        $this->addLoggingChannelToLogging($queryLoggerName);
        $this->addMiddlewareAndRoute($queryLoggerName);
//        $this->addEnvVariableKeys();
        $this->addEnvVariables($queryLoggerName);

        // TODO: Add logic to the controller to fetch query logs by specific search criteria
        // TODO: Add keys to app.php for query logger environment variables
        // TODO: Add a web route and admin panel to monitor queries
    }

    private function createMiddleware(string $queryLoggerName): void
    {
        $fileExists = file_exists(base_path('app/Http/Middleware') . '/' . $queryLoggerName . '.php');

        if ($fileExists) {
            $this->info("$queryLoggerName middleware already exists!");
        } else {
            $contents = file_get_contents(base_path('app/Console/Commands/CreateQueryLoggerCommand/ClassTemplates/middleware.stub'));
            file_put_contents(base_path('app/Http/Middleware') . '/' . $queryLoggerName . '.php', $contents);
            $this->info("$queryLoggerName middleware created successfully!");
        }
    }

    private function addMiddlewareToKernel(string $queryLoggerName): void
    {
        $aliasName = Str::camel($queryLoggerName);
        $middlewareClass = "\\App\\Http\\Middleware\\{$queryLoggerName}::class";
        $middlewareLineForApi = "\t\t\t\\App\\Http\\Middleware\\{$queryLoggerName}::class,";
        $aliasLine = "\t\t'{$aliasName}' => {$middlewareClass}";

        $kernelFilePath = base_path('app/Http/Kernel.php');
        $kernelFileContents = file_get_contents($kernelFilePath);

        if (strpos($kernelFileContents, $queryLoggerName)) {
            $this->info("$queryLoggerName middleware already exists in api middleware group!");
            return;
        }

        $kernelFileContentsWithApi = preg_replace('/(\'api\'\s*=>\s*\[\s*)(.*?)(\s*\],)/s', "'api' => [\n$2\n$middlewareLineForApi],", $kernelFileContents);
        $updatedKernelFileContents = preg_replace('/(protected\s*\$middlewareAliases\s*=\s*\[\s*)(.*?)(\s*\];)/s', "$1\n$2\n$aliasLine\n$3", $kernelFileContentsWithApi);

        file_put_contents($kernelFilePath, $updatedKernelFileContents);
        $this->info("$queryLoggerName middleware added to Kernel.php!");
    }

    private function addLoggingChannelToLogging(string $queryLoggerName): void
    {
        $queryLoggerNameSnakeCase = Str::snake($queryLoggerName);
        $loggingConfigPath = config_path('logging.php');
        $loggingConfigContents = file_get_contents($loggingConfigPath);

        if (strpos($loggingConfigContents, $queryLoggerNameSnakeCase)) {
            $this->info("$queryLoggerName channel already exists in logging.php.");
            return;
        }

        $newChannelConfig = "'$queryLoggerNameSnakeCase' => [
                'driver' => 'single',
                'path' => storage_path('logs/' . config('app.query_logs_file_name')),
                'days' => config('app.keep_query_logs_days'),
                'level' => 'debug',
            ],
        ";

        $newLoggingConfig = preg_replace('/(\'channels\'\s*=>\s*\[\s*.*?)\s*(\'stack\'\s*=>\s*\[.*?)\s*(\],)/s', "$1$newChannelConfig$2$3", $loggingConfigContents);

        file_put_contents($loggingConfigPath, $newLoggingConfig);

        $this->info('Query log channel added to logging.php successfully!');
    }

    private function addEnvVariables(string $queryLoggerName): void
    {
        $queryLoggerNameSnakeCase = Str::snake($queryLoggerName);
        $queryLoggerVariables = '';
        $missingVariables = false;
        $infoMessage = [];

        $envFileContents = file_get_contents(base_path('.env'));

        $variablesToCheck = [
            Constants::QUERY_LOGGER_ENVIRONMENT => Constants::QUERY_LOGGER_ENVIRONMENT . "=local\n",
            Constants::QUERY_LOG_FILE_NAME => Constants::QUERY_LOG_FILE_NAME . "=$queryLoggerNameSnakeCase.log\n",
            Constants::KEEP_QUERY_LOGS_DAYS => Constants::KEEP_QUERY_LOGS_DAYS . "=14\n",
            Constants::LOW_PERFORMANCE_QUERY_MEMORY => Constants::LOW_PERFORMANCE_QUERY_MEMORY . "=30000000\n",
            Constants::LOW_PERFORMANCE_QUERY_TIME => Constants::LOW_PERFORMANCE_QUERY_TIME . "=100\n",
            Constants::MID_PERFORMANCE_QUERY_MEMORY  => Constants::MID_PERFORMANCE_QUERY_MEMORY . "=20000000\n",
            Constants::MID_PERFORMANCE_QUERY_TIME => Constants::MID_PERFORMANCE_QUERY_TIME . "=50\n",
            Constants::HIGH_PERFORMANCE_QUERY_MEMORY => Constants::HIGH_PERFORMANCE_QUERY_MEMORY . "=12000000\n",
            Constants::HIGH_PERFORMANCE_QUERY_TIME => Constants::HIGH_PERFORMANCE_QUERY_TIME . "=20\n",
        ];

        foreach ($variablesToCheck as $variable => $value) {
            if (!Str::contains($envFileContents, $variable)) {
                $missingVariables = true;
                $queryLoggerVariables .= $value;
                $infoMessage[] = $variable;
            }
        }

        if ($missingVariables) {
            file_put_contents(base_path('.env'), $envFileContents . $queryLoggerVariables);
            $this->info("Query logger environment variables: " . implode(', ', $infoMessage) . " added to .env!");
        } else {
            $this->info('All query logger environment variables already exist in .env!');
        }
    }

    private function addMiddlewareAndRoute(string $queryLoggerName): void
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
