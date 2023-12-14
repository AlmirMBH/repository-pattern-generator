<?php

namespace App\Console\Commands;

use App\Console\Commands\CommandTraits\ClassesToCreateDataTrait;
use App\Console\Commands\CommandTraits\CreateDataAccessLayerFoldersTrait;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class MakeResourceWithRepositoryCommand extends Command
{
    use CreateDataAccessLayerFoldersTrait;
    use ClassesToCreateDataTrait;

    // TODO: Adjust controller, create routes, bind interfaces to repositories in RepositoryServiceProvider
    protected $signature = 'make:resource {name : The name of the Eloquent model} {--repository : Include a repository}';
    protected $description = 'Generate an Eloquent model with an optional repository';

    private string $controllerPath = 'Http/Controllers/Api';
    private string $repositoryPath = 'DataAccessLayer/Repositories';
    private string $servicesPath = 'DataAccessLayer/Services';
    private string $interfacesPath = 'DataAccessLayer/Interfaces';
    private string $routesPath = 'routes/api.php';


    public function handle(): void
    {
        $modelName = $this->argument('name');
        $includeRepository = $this->option('repository');

        // Create model, factory, and migration
        $this->createResource($modelName);

        // Create provider to bind interfaces to repositories
        if (! $this->classExists("App/Providers/RepositoryServiceProvider.php")) {
            Artisan::call('make:provider', ['name' => 'RepositoryServiceProvider']);
            $this->info('RepositoryServiceProvider created!');
        }

        // Create repository, service and interface, and routes
        if ($includeRepository) {
            $this->createDataAccessLayerFolders();

            $filesToCreate = $this->getClassesToCreateData($modelName);

            foreach ($filesToCreate as $data) {
                $this->createClasses($data);
            }
        }

        $this->clearCache();
    }

    public function createResource(string $modelName): void
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
        $fullPath = app_path("$path/$fileName");

        if (!file_exists($fullPath)) {
            file_put_contents($fullPath, $content);
            $this->info("$fileName created!");
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
}
