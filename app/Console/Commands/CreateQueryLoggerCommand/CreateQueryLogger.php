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
        $this->addMiddlewareAndEndpointToRoutes($queryLoggerName);
        $this->createQueryLoggerController($queryLoggerName);
        $this->addLoggingChannel($queryLoggerName);
        $this->addEnvVariables($queryLoggerName);
        $this->addEnvVariableKeys($queryLoggerName);

        // TODO: Add logic to the controller to fetch query logs via .env file by specific search criteria
        // TODO: Add a web route and admin panel to monitor queries
        // TODO: If controller e.g. Car exists, a query logger with the same name cannot be created; fix it
    }

    private function createMiddleware(string $queryLoggerName): void
    {
        $fileExists = file_exists(base_path('app/Http/Middleware') . '/' . $queryLoggerName . '.php');

        if ($fileExists) {
            $this->info("$queryLoggerName middleware already exists!");
        } else {
            $contents = file_get_contents(base_path('app/Console/Commands/CreateQueryLoggerCommand/ClassTemplates/middleware.stub'));
            $contents = str_replace('{{ middlewareName }}', $queryLoggerName, $contents);
            file_put_contents(base_path('app/Http/Middleware') . '/' . $queryLoggerName . '.php', $contents);
            $this->info("$queryLoggerName middleware created successfully!");
        }
    }

    private function addMiddlewareToKernel(string $queryLoggerName): void
    {
        $aliasName = Str::camel($queryLoggerName);
        $middlewareClass = "\\App\\Http\\Middleware\\{$queryLoggerName}::class";
        $middlewareLineForApi = "\t\t\t\\App\\Http\\Middleware\\{$queryLoggerName}::class,";
        $aliasLine = "\t\t'{$aliasName}' => {$middlewareClass},";

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

    private function addMiddlewareAndEndpointToRoutes(string $queryLoggerName): void
    {
        $routesPrefix = str_replace('_', '-', Str::snake($queryLoggerName));
        $queryLoggerCamelCaseName = Str::camel($queryLoggerName);

        $middlewareAndRouteExists = $this->dataAlreadyInsertedInFile($queryLoggerName);

        if ($middlewareAndRouteExists) {
            $this->info("$queryLoggerName route already exists in api.php!");
        } else {
            $middlewareAndRoute =
                "Route::middleware('{$queryLoggerCamelCaseName}')->group(function(){
    Route::controller(App\Http\Controllers\Api\\" . $queryLoggerName . "Controller::class)->group(function () {
         Route::prefix('$routesPrefix')->group(function () {
            Route::get('/query-logs', 'getLogs')->name('getQueryLogs');
        });
    });
});";

            file_put_contents(base_path('routes/api.php'), "\n\n$middlewareAndRoute", FILE_APPEND);
            $this->info("$queryLoggerName route added to api.php!");
        }
    }

    private function createQueryLoggerController(string $queryLoggerName): void
    {
        $queryLoggerControllerName = $queryLoggerName . 'Controller';
        $queryLoggerControllerPath = base_path('app/Http/Controllers/Api') . '/' . $queryLoggerControllerName . '.php';

        $fileExists = file_exists($queryLoggerControllerPath);

        if ($fileExists) {
            $this->info("$queryLoggerControllerName already exists!");
        } else {
            $contents = file_get_contents(base_path('app/Console/Commands/CreateQueryLoggerCommand/ClassTemplates/query_logger_controller.stub'));

            $contents = str_replace('{{ modelName }}', $queryLoggerName, $contents);

            file_put_contents($queryLoggerControllerPath, $contents);
            $this->info("$queryLoggerControllerName created successfully!");
        }
    }

    private function addLoggingChannel(string $queryLoggerName): void
    {
        $queryLoggerNameSnakeCase = Str::snake($queryLoggerName);
        $loggingConfigPath = config_path('logging.php');
        $loggingConfigContents = file_get_contents($loggingConfigPath);

        if (strpos($loggingConfigContents, $queryLoggerNameSnakeCase)) {
            $this->info("$queryLoggerName channel already exists in logging.php.");
            return;
        }

        $newChannelConfig = "config('app.query_logger_channel') => [
                'driver' => 'single',
                'path' => storage_path('logs/' . config('app.query_logs_file_name')),
                'days' => config('app.keep_query_logs_days'),
                'level' => 'debug',
            ],
        ";

        // In the context of the preg_replace function and the regular expression provided, $1, $2, and $3 are capturing groups.
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
            Constants::QUERY_LOGGER_CHANNEL => Constants::QUERY_LOGGER_CHANNEL . "=$queryLoggerNameSnakeCase\n",
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
            $this->info("$queryLoggerName environment variables: " . implode(', ', $infoMessage) . " added to .env!");
        } else {
            $this->info("All $queryLoggerName environment variables already exist in .env!");
        }
    }

    private function addEnvVariableKeys(string $queryLoggerName): void
    {
        $appConfigPath = base_path('config/app.php');
        $appFileContents = file_get_contents($appConfigPath);
        $missingVariables = false;
        $queryLoggerVariables = '';
        $infoMessage = [];

        $variablesToCheck = [
            Constants::QUERY_LOGGER_ENVIRONMENT_KEY => 'QUERY_LOGGER_ENVIRONMENT',
            Constants::QUERY_LOGGER_CHANNEL_KEY => 'QUERY_LOGGER_CHANNEL',
            Constants::QUERY_LOG_FILE_NAME_KEY => 'QUERY_LOG_FILE_NAME',
            Constants::KEEP_QUERY_LOGS_DAYS_KEY => 'KEEP_QUERY_LOGS_DAYS',
            Constants::HIGH_PERFORMANCE_QUERY_MEMORY_KEY => 'HIGH_PERFORMANCE_QUERY_MEMORY',
            Constants::HIGH_PERFORMANCE_QUERY_TIME_KEY => 'HIGH_PERFORMANCE_QUERY_TIME',
            Constants::MID_PERFORMANCE_QUERY_MEMORY_KEY => 'MID_PERFORMANCE_QUERY_MEMORY',
            Constants::MID_PERFORMANCE_QUERY_TIME_KEY => 'MID_PERFORMANCE_QUERY_TIME',
            Constants::LOW_PERFORMANCE_QUERY_MEMORY_KEY => 'LOW_PERFORMANCE_QUERY_MEMORY',
            Constants::LOW_PERFORMANCE_QUERY_TIME_KEY => 'LOW_PERFORMANCE_QUERY_TIME'
        ];

        foreach ($variablesToCheck as $key => $value) {
            if (!Str::contains($appFileContents, $key)) {
                $missingVariables = true;
                $queryLoggerVariables .= "\n\t'$key' => env('$value'),";
                $infoMessage[] = $key;
            }
        }

        if (!$missingVariables) {
            $this->info("All $queryLoggerName environment variable keys already exist in app.php!");
            return;
        }

        $newAppConfig = preg_replace('/(\s*\]\s*(?:;|\)\s*;))/s', "$queryLoggerVariables$1", $appFileContents);
        file_put_contents($appConfigPath, $newAppConfig);
        $this->info("$queryLoggerName environment variables: " . implode(', ', $infoMessage) . " added to app.php!");
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
