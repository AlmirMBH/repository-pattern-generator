<?php

namespace App\Console\Commands;

use App\Console\Commands\CommandTraits\CreateDataAccessLayerFoldersTrait;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class MakeResourceWithRepositoryCommand extends Command
{
    use CommandTraits\CreateResourceTrait;
    use CreateDataAccessLayerFoldersTrait;
    use CommandTraits\CreateBaseRepositoryInterfaceTrait;
    use CommandTraits\CreateCustomRepositoryInterfaceTrait;
    use CommandTraits\CreateFilesTrait;
    use CommandTraits\CreateBaseRepositoryTrait;
    use CommandTraits\CreateCustomRepositoryTrait;
    use CommandTraits\CreateRepositoryInterfaceTrait;
    use CommandTraits\CreateRepositoryServiceTrait;
    use CommandTraits\CreateRoutesTrait;

    // TODO: Add option to create a controller, routes, and register the interface in the service provider
    protected $signature = 'make:resource {name : The name of the Eloquent model} {--repository : Include a repository}';
    protected $description = 'Generate an Eloquent model with an optional repository';

    private string $repositoryPath = 'DataAccessLayer/Repositories';
    private string $servicesPath = 'DataAccessLayer/Services';
    private string $interfacesPath = 'DataAccessLayer/Interfaces';
    private string $routesPath = 'routes/api.php';


    public function handle(): void
    {
        $modelName = $this->argument('name');
        $includeRepository = $this->option('repository');

        // Create model, factory, controller, and migration
        $this->createResource($modelName);

        // Create repository, service, and interface, and routes
        if ($includeRepository) {
            $this->createDataAccessLayerFolders();
            $this->createBaseRepositoryInterface();
            $this->createCustomRepositoryInterface($modelName);
            $this->createBaseRepository();
            $this->createCustomRepository($modelName);
            $this->createRepositoryInterface($modelName);
            $this->createCustomService($modelName);
            $this->createRoutes($modelName);
        }

        $this->info("Generation complete!");

        Artisan::call('optimize');
        $this->info('Cache cleared');

        $process = new Process(['composer', 'dump-autoload']);
        $process->run();

        $process->isSuccessful()
            ? $this->info('Composer dump-auto-loaded!')
            : throw new ProcessFailedException($process);
    }
}
