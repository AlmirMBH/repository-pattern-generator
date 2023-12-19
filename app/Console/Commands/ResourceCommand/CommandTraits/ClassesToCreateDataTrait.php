<?php

namespace App\Console\Commands\ResourceCommand\CommandTraits;

use Illuminate\Support\Str;

trait ClassesToCreateDataTrait
{
    private function getClassesToCreateData(string $modelName): array
    {
        $baseRepositoryInterface = [
            'class' => 'BaseRepositoryInterface.php',
            'modelName' => ucfirst($modelName),
            'path' => $this->interfacesPath,
            'stubFile' => 'app/Console/Commands/ResourceCommand/ClassTemplates/base_repository_interface.stub',
            'replacements' => []
        ];

        $customRepositoryInterface = [
            'class' => $modelName . 'RepositoryInterface.php',
            'modelName' => ucfirst($modelName),
            'path' => $this->interfacesPath,
            'stubFile' => 'app/Console/Commands/ResourceCommand/ClassTemplates/custom_repository_interface.stub',
            'replacements' => [
                '{{ modelName }}' => ucfirst($modelName),
            ]
        ];

        $baseRepository = [
            'class' => 'BaseRepository.php',
            'modelName' => ucfirst($modelName),
            'path' => $this->repositoryPath,
            'stubFile' => 'app/Console/Commands/ResourceCommand/ClassTemplates/base_repository.stub',
            'replacements' => []
        ];


        $customRepository = [
            'class' => $modelName . 'Repository.php',
            'modelName' => ucfirst($modelName),
            'path' => $this->repositoryPath,
            'stubFile' => 'app/Console/Commands/ResourceCommand/ClassTemplates/repository.stub',
            'replacements' => [
                '{{ modelName }}' => ucfirst($modelName)
            ]
        ];

        $repositoryService = [
            'class' => $modelName . 'Service.php',
            'modelName' => ucfirst($modelName),
            'path' => $this->servicesPath,
            'stubFile' => 'app/Console/Commands/ResourceCommand/ClassTemplates/service.stub',
            'replacements' => [
                '{{ modelName }}' => ucfirst($modelName),
                '{{ repositoryName }}' => lcfirst($modelName) . 'Repository'
            ]
        ];

        $controller = [
            'class' => $modelName . 'Controller.php',
            'modelName' => ucfirst($modelName),
            'path' => $this->controllerPath,
            'stubFile' => 'app/Console/Commands/ResourceCommand/ClassTemplates/controller.stub',
            'replacements' => [
                '{{ modelName }}' => ucfirst($modelName),
                '{{ serviceName }}' => lcfirst($modelName) . 'Service',
            ]
        ];

        $routeGroupController = ucfirst($modelName) . "Controller";
        $routesPrefix = str_replace('_', '-', Str::snake(Str::plural($modelName)));
        $paramModelName = lcfirst($modelName);
        $routeNamePlural = Str::plural($modelName);

        $routes = [
            'class' => 'api.php',
            'modelName' => ucfirst($modelName),
            'path' => $this->routesPath,
            'stubFile' => $this->routesPath . '/api.php',
            'replacements' => [
                '// {{ classImport }}' =>
                    'use App\Http\Controllers\Api\\' . $routeGroupController . ';' . PHP_EOL . '// {{ classImport }}',
                '// {{ routesPlaceholder }}' => "Route::controller($routeGroupController::class)->group(function () {
     Route::prefix('$routesPrefix')->group(function () {
        Route::get('/', 'index')->name('list$routeNamePlural');
        Route::post('/', 'create')->name('create$modelName');
        Route::get('/{{$paramModelName}Id}', 'show')->name('get$modelName');
        Route::put('/{{$paramModelName}Id}', 'update')->name('update$modelName');
        Route::delete('/{{$paramModelName}Id}', 'delete')->name('delete$modelName');
    });
});

// {{ routesPlaceholder }}",
            ],
        ];

        $repositoryServiceProvider = [
            'class' => 'RepositoryServiceProvider.php',
            'modelName' => $modelName,
            'path' => $this->repositoryServiceProviderPath,
            'stubFile' => file_exists(app_path('Providers/RepositoryServiceProvider.php'))
                ? 'app/Providers/RepositoryServiceProvider.php'
                : 'app/Console/Commands/ResourceCommand/ClassTemplates/repository_service_provider.stub',
            'replacements' => [
                '// {{ interfaceAndRepositoryImports }}' =>
                'use App\DataAccessLayer\Interfaces\\' . $modelName . 'RepositoryInterface;' . PHP_EOL .
                'use App\DataAccessLayer\Repositories\\' . $modelName . 'Repository;' . PHP_EOL .
                '// {{ interfaceAndRepositoryImports }}',
                '// {{ interfaceRepositoryBinding }}' =>
                    '$this->app->bind(' . $modelName . 'RepositoryInterface::class,' . $modelName . 'Repository::class);' . PHP_EOL .
                    '        // {{ interfaceRepositoryBinding }}'
            ]
        ];

        return [
            $baseRepositoryInterface,
            $customRepositoryInterface,
            $baseRepository,
            $customRepository,
            $repositoryService,
            $controller,
            $repositoryServiceProvider,
            $routes
        ];
    }
}
