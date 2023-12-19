<?php

namespace App\Console\Commands\ResourceCommand;

use App\Console\Commands\ResourceCommand\CommandTraits\ClassesToCreateDataTrait;
use App\Console\Commands\ResourceCommand\CommandTraits\CreateDataAccessLayerFoldersTrait;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\ServiceProvider;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class MakeResourceWithRepositoryCommand extends Command
{
    use CreateDataAccessLayerFoldersTrait;
    use ClassesToCreateDataTrait;

    // TODO: Generate CRUD tests for created models
    // TODO: Export Postman collection for all endpoints
    // TODO: Publish clean repo to GitHub
    // TODO: Write README.md
    // TODO: Create tests for the resource commands (in package)
    protected $signature = 'make:resource {name : The name of the Eloquent model} {--repository : Include a repository}';
    protected $description = 'Generate an Eloquent model with an optional repository';

    private string $controllerPath = 'Http/Controllers/Api';
    private string $repositoryPath = 'DataAccessLayer/Repositories';
    private string $servicesPath = 'DataAccessLayer/Services';
    private string $interfacesPath = 'DataAccessLayer/Interfaces';
    private string $repositoryServiceProviderPath = 'Providers';
    private string $routesPath = 'routes';


    public function handle(): void
    {
        $modelName = $this->argument('name');
        $includeRepository = $this->option('repository');

        $this->createModelFactoryMigration($modelName);

        if ($includeRepository) {
            $this->createDataAccessLayerFolders();

            $classesToCreate = $this->getClassesToCreateData($modelName);

            foreach ($classesToCreate as $class) {
                $this->createClasses($class);
            }
        }


        if (file_exists(app_path('Providers/RepositoryServiceProvider.php'))) {
            $this->addProviderToConfig();
        }

        $this->clearCache();
    }

    public function createModelFactoryMigration(string $modelName): void
    {
        $class = "App/Models/$modelName.php";

        if ($this->classExists($class)) {
            return;
        }

        Artisan::call('make:model', [
            'name' => $modelName,
            '--factory' => true,
            '--migration' => true,
        ]);

        $this->info("Model $modelName and related migration and factory created!");
    }

    private function classExists(string $class): bool
    {
        $classExists = false;

        if (file_exists($class)) {
            $this->info("Class $class already exists!");
            $classExists = true;
        }

        return $classExists;
    }

    private function createClasses(array $data): void
    {
        $fileToCreateContent = $this->getFileContent($data);
        $this->createFile($data['path'], $data['class'], $fileToCreateContent);
    }

    private function getFileContent(array $data): string
    {
        $stubPath = base_path($data['stubFile']);
        $stubContents = file_get_contents($stubPath);

        return str_replace(array_keys($data['replacements']), array_values($data['replacements']), $stubContents);
    }

    private function createFile(string $path, string $fileName, string $content): void
    {
        $basePath = "$path/$fileName";
        $appPath = app_path($basePath);

        if (! file_exists($appPath) && $path !== $this->routesPath) {
            file_put_contents($appPath, $content);
            $this->info("$fileName created!");
        } elseif ($path === $this->repositoryServiceProviderPath) { // created once, then updated
            file_put_contents($appPath, $content);
            $this->info("$fileName updated!");
        } elseif ($path === $this->routesPath) { // existing api.php used
            file_put_contents($basePath, $content);
            $this->info("$fileName updated!");
        } else {
            $this->info($fileName . " already exists!");
        }
    }

    private function clearCache(): void
    {
        Artisan::call('optimize');
        $this->info('Cache cleared!');

        $process = new Process(['composer', 'dump-autoload']);
        $process->run();

        $process->isSuccessful()
            ? $this->info('Composer dump-auto-loaded!')
            : throw new ProcessFailedException($process);
    }

    private function addProviderToConfig(): void
    {
        $repositoryServiceProvider = 'App\Providers\RepositoryServiceProvider';

        $configPath = config_path('app.php');

        // Read the contents of the file
        $fileContents = file_get_contents($configPath);

        // Define the 'providers' array pattern
        $pattern = "/'providers' => ServiceProvider::defaultProviders\(\)->merge\(([^)]+)\)/";

        // Find the 'providers' array using the defined pattern
        if (preg_match($pattern, $fileContents, $matches)) {
            // Decode the 'providers' array
            $providersArray = eval('return ' . $matches[1] . ';');

            // Add the new provider without ::class
            $providersArray[] = $repositoryServiceProvider;

            // Encode the modified array back to string
            $newProvidersString = '[' . PHP_EOL . implode(',' . PHP_EOL, array_map(function ($provider) {
                    return '    ' . $provider . '::class';
                }, $providersArray)) . PHP_EOL . ']';


            // Replace the old 'providers' array with the new one in the file contents
            $fileContents = str_replace($matches[1], $newProvidersString, $fileContents);

            // Write the updated configuration back to the file
            file_put_contents($configPath, $fileContents);

            $this->info('RepositoryServiceProvider added to config/app.php!');
        } else {
            // Handle the case where the 'providers' array was not found
            $this->info( "Error: Unable to find 'providers' array in the configuration file.");
        }
    }
}
