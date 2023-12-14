<?php

namespace App\Console\Commands\CommandTraits;

use Illuminate\Support\Str;

trait ClassesToCreateDataTrait
{
    private function getClassesToCreateData(string $modelName): array
    {
        $baseRepositoryInterface = [
            'class' => 'BaseRepositoryInterface.php',
            'modelName' => ucfirst($modelName),
            'path' => $this->interfacesPath,
            'stubFile' =>'app/Console/ClassTemplates/base_repository_interface.stub',
            'replacements' => []
        ];

        $customRepositoryInterface = [
            'class' => $modelName . 'RepositoryInterface.php',
            'modelName' => ucfirst($modelName),
            'path' => $this->interfacesPath,
            'stubFile' =>'app/Console/ClassTemplates/custom_repository_interface.stub',
            'replacements' => [
                '{{ modelName }}' => ucfirst($modelName),
            ]
        ];

        $baseRepository = [
            'class' => 'BaseRepository.php',
            'modelName' => ucfirst($modelName),
            'path' => $this->repositoryPath,
            'stubFile' =>'app/Console/ClassTemplates/base_repository.stub',
            'replacements' => []
        ];


        $customRepository = [
            'class' => $modelName . 'Repository.php',
            'modelName' => ucfirst($modelName),
            'path' => $this->repositoryPath,
            'stubFile' =>'app/Console/ClassTemplates/repository.stub',
            'replacements' => [
                '{{ modelName }}' => ucfirst($modelName)
            ]
        ];

        $repositoryService = [
            'class' => $modelName . 'Service.php',
            'modelName' => ucfirst($modelName),
            'path' => $this->servicesPath,
            'stubFile' =>'app/Console/ClassTemplates/service.stub',
            'replacements' => [
                '{{ modelName }}' => ucfirst($modelName),
                '{{ repositoryName }}' => lcfirst($modelName) . 'Repository'
            ]
        ];

        $controller = [
            'class' => $modelName . 'Controller.php',
            'modelName' => ucfirst($modelName),
            'path' => $this->controllerPath,
            'stubFile' =>'app/Console/ClassTemplates/controller.stub',
            'replacements' => [
                '{{ modelName }}' => ucfirst($modelName),
                '{{ serviceName }}' => lcfirst($modelName) . 'Service',
            ]
        ];

        $routeGroupController = ucfirst($modelName) . "Controller";
        $prefix = str_replace('_', '-', Str::snake($modelName));
        $paramModelName = lcfirst($modelName);

        $routes = [
            'class' => 'api.php',
            'modelName' => ucfirst($modelName),
            'path' => $this->routesPath,
            'stubFile' => $this->routesPath . '/api.php',
            'replacements' => [
                '// {{ classImport }}' =>
                    'use App\Http\Controllers\Api\\' . $routeGroupController . ';' . PHP_EOL . '// {{ classImport }}',
                '// {{ routesPlaceholder }}' => "Route::controller($routeGroupController::class)->group(function () {
     Route::prefix('$prefix')->group(function () {
        Route::get('/', 'list')->name('list{$modelName}s');
        Route::post('/', 'create')->name('create$modelName');
        Route::get('/{{$paramModelName}Id}', 'show')->name('get$modelName');
        Route::put('/{{$paramModelName}Id}', 'update')->name('update$modelName');
        Route::delete('/{{$paramModelName}Id}', 'delete')->name('delete$modelName');
    });
});

// {{ routesPlaceholder }}",
            ],
        ];

        return [
            $baseRepositoryInterface,
            $customRepositoryInterface,
            $baseRepository,
            $customRepository,
            $repositoryService,
            $controller,
            $routes
        ];
    }
}
