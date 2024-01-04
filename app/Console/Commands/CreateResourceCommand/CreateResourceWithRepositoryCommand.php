<?php

namespace App\Console\Commands\CreateResourceCommand;

use App\Console\Commands\CreateResourceCommand\CommandTraits\ClassesToCreateDataTrait;
use App\Console\Commands\CreateResourceCommand\CommandTraits\CreateDataAccessLayerFoldersTrait;
use App\Console\Commands\CreateResourceCommand\Constants\Constants;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Str;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class CreateResourceWithRepositoryCommand extends Command
{
    use CreateDataAccessLayerFoldersTrait;
    use ClassesToCreateDataTrait;
    use CommandTraits\ModelValidationTrait;

    // TODO: Add tests, record not found for update, delete, show and all
    // TODO: Enable json and enum columns in the test stub
    // TODO: Format arrays in the test stub (indentation)
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

        $this->createDataAccessLayerFolders();
        $this->createResource($modelName);

        $filesToCreate = $this->getDataToCreateFiles($modelName, $includeRepository);

        foreach ($filesToCreate as $file) {
            $stubFileWithContents = $this->getStubFileWithContent($file);
            $this->createFile($file, $stubFileWithContents, $modelName);
        }

        if (file_exists(base_path(Constants::REPOSITORY_SERVICE_PROVIDER_PATH . '/' . Constants::REPOSITORY_SERVICE_PROVIDER_FILE_NAME))) {
            $this->addProviderToConfig();
        }

        $this->clearCache();

        $this->info(Constants::RESOURCE_CREATED);
    }

    public function createResource(string $modelName): void
    {
        $modelExists = $this->checkIfModelExists($modelName);
        $factoryExists = $this->checkIfFactoryExist($modelName);
        $migrationExists = $this->checkIfMigrationExists($modelName);

        if (! $modelExists) {
            Artisan::call('make:model', [
                'name' => $modelName,
            ]);
        }

        if (! $factoryExists) {
            Artisan::call('make:factory', [
                'name' => "{$modelName}Factory",
                '--model' => $modelName,
            ]);
        }

        if (! $migrationExists) {
            $modelName = lcfirst(Str::snake(Str::plural($modelName)));
            Artisan::call('make:migration', [
                'name' => "create_{$modelName}_table",
            ]);
        }
    }

    private function getStubFileWithContent(array $data): string
    {
        $stubPath = base_path($data['stubFile']);
        $stubContents = file_get_contents($stubPath);

        return str_replace(array_keys($data['replacements']), array_values($data['replacements']), $stubContents);
    }

    private function createFile(array $file, string $stubFileWithContents, string $modelName): void
    {
        $path = $file['path'];
        $fileName = $file['name'];
        $basePath = "$path/$fileName";

        if (! file_exists($basePath)) {
            file_put_contents($basePath, $stubFileWithContents);
            $this->info("$fileName created!");
        } elseif ($file['name'] === Constants::REPOSITORY_SERVICE_PROVIDER_FILE_NAME || $file['name'] === Constants::EXISTING_ROUTES_FILE_NAME) {
            if (! $this->dataAlreadyInsertedInFile($file, $modelName)) {
                $file['append'] ? file_put_contents($basePath, $file['append'], FILE_APPEND) : file_put_contents($basePath, $stubFileWithContents);
                $this->info("$fileName updated!");
            } else {
                $this->info("Data already inserted in $fileName");
            }
        }
        else {
            $this->info("$fileName already exists!");
        }
    }

    private function dataAlreadyInsertedInFile(array $class, string $modelName): bool
    {
        // Check if the controller is added to api and repository interface to repository service provider
        $data = $modelName . ($class['path'] === Constants::ROUTES_PATH ? 'Controller' : 'RepositoryInterface');

        $fileContents = file_get_contents(base_path($class['path'] . '/' . $class['name']));

        if (Str::contains($fileContents, $data)){
            return true;
        }

        return false;
    }

    private function addProviderToConfig(): void
    {
        $repositoryServiceProvider = 'App\Providers\RepositoryServiceProvider';
        $configPath = config_path('app.php');
        $fileContents = file_get_contents($configPath);

        if (Str::contains($fileContents, $repositoryServiceProvider)) {
            $this->info('RepositoryServiceProvider already added to config/app.php!');
            return;
        }

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
