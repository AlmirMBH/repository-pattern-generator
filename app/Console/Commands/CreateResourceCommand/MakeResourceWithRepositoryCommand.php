<?php

namespace App\Console\Commands\CreateResourceCommand;

use App\Console\Commands\CreateResourceCommand\CommandTraits\ClassesToCreateDataTrait;
use App\Console\Commands\CreateResourceCommand\CommandTraits\CreateDataAccessLayerFoldersTrait;
use App\Console\Commands\CreateResourceCommand\Constants\Constants;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class MakeResourceWithRepositoryCommand extends Command
{
    use CreateDataAccessLayerFoldersTrait;
    use ClassesToCreateDataTrait;

    // TODO: Check how route placeholders are added the first time and prevent duplicates
    // TODO: Prevent duplicate entries in the service provider
    // TODO: Enable multiple data types in tests (e.g. string, int, bool, etc.)
    // TODO: Format arrays in the test stub (indentation)
    // TODO: Add route to fetch query logs (pagination, sorting, filtering, etc.);
    // TODO: Define the key variables in .env and add log channel dynamically
    // TODO: Export Postman collection for all endpoints
    // TODO: Add PHPDoc to all classes
    // TODO: Create tests for the resource commands (in package)
    // TODO: Create a package (starter kit)

    // TODO: Readme
    // TODO: Mass assignment columns must be specified in the model; otherwise tests will fail
    // TODO: DB seeding must be done manually every time after tests are run, if not using a testing DB
    // TODO: Explain what column types can be tested
    // TODO: A testing DB needs to be set in phpunit.xml and .env.testing
    // TODO: The code might be the same in some commands; the purpose is easy copying and pasting only what you need
    protected $signature = 'make:resource {name : The name of the Eloquent model} {--repository : Include a repository}';
    protected $description = 'Generate a resource with an optional repository';


    public function handle(): void
    {
        $modelName = $this->argument('name');
        $includeRepository = $this->option('repository');

        $this->createModelFactoryMigration($modelName);

        $classesToCreate = $this->getDataToCreateClasses($modelName, $includeRepository);

        foreach ($classesToCreate as $class) {
            $fileToCreateContent = $this->getStubFileWithContent($class);
            $this->createFile($class['path'], $class['name'], $fileToCreateContent);
        }

        if (file_exists(app_path(Constants::EXISTING_REPOSITORY_SERVICE_PROVIDER))) {
            $this->addProviderToConfig();
        }

        $this->clearCache();

        $this->info(Constants::RESOURCE_CREATED);
    }

    public function createModelFactoryMigration(string $modelName): void
    {
        // TODO: Check if migration and factory exist, not only class
        if ($this->classExists($modelName)) {
            return;
        }

        Artisan::call('make:model', [
            'name' => $modelName,
            '--factory' => true,
            '--migration' => true,
        ]);

        $this->info("Model $modelName and related migration and factory created!");
    }

    private function classExists(string $modelName): bool
    {
        $classExists = false;
        $class = "App/Models/$modelName.php";

        if (file_exists($class)) {
            $this->info("Class $class already exists!");
            $classExists = true;
        }

        return $classExists;
    }

    private function getStubFileWithContent(array $data): string
    {
        $stubPath = base_path($data['stubFile']);
        $stubContents = file_get_contents($stubPath);

        return str_replace(array_keys($data['replacements']), array_values($data['replacements']), $stubContents);
    }

    private function createFile(string $path, string $fileName, string $content): void
    {
        $basePath = "$path/$fileName";
        $appPath = app_path($basePath);

        if (! file_exists($appPath) && $path !== Constants::ROUTES_PATH) {
            file_put_contents($appPath, $content);
            $this->info("$fileName created!");
        } elseif ($path === Constants::REPOSITORY_SERVICE_PROVIDER_PATH) { // created once, then updated
            file_put_contents($appPath, $content);
            $this->info("$fileName updated!");
        } elseif ($path === Constants::ROUTES_PATH) { // existing api.php used
            file_put_contents($basePath, $content);
            $this->info("$fileName updated!");
        } else {
            $this->info($fileName . " already exists!");
        }
    }

    private function addProviderToConfig(): void
    {
        $repositoryServiceProvider = 'App\Providers\RepositoryServiceProvider';
        $configPath = config_path('app.php');
        $fileContents = file_get_contents($configPath);

        $pattern = "/'providers' => ServiceProvider::defaultProviders\(\)->merge\(([^)]+)\)/";

        if (preg_match($pattern, $fileContents, $matches)) {
            $providersArray = eval('return ' . $matches[1] . ';');
            $providersArray[] = $repositoryServiceProvider;

            $newProvidersString = '[' . PHP_EOL . implode(',' . PHP_EOL, array_map(function ($provider) {
                    return '    ' . $provider . '::class';
                }, $providersArray)) . PHP_EOL . ']';

            $fileContents = str_replace($matches[1], $newProvidersString, $fileContents);
            file_put_contents($configPath, $fileContents);

            $this->info(Constants::REPOSITORY_SERVICE_PROVIDER_ADDED_TO_CONFIG);
        } else {
            $this->info( Constants::PROVIDERS_ARRAY_NOT_FOUND_IN_CONFIG);
        }
    }

    private function clearCache(): void
    {
        Artisan::call('optimize');
        $this->info(Constants::CACHE_CLEARED);

        $process = new Process(['composer', 'dump-autoload']);
        $process->run();

        $process->isSuccessful()
            ? $this->info(Constants::COMPOSER_DUMP_AUTO_LOADED)
            : throw new ProcessFailedException($process);
    }
}
