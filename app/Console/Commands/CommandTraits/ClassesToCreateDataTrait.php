<?php

namespace App\Console\Commands\CommandTraits;

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

        $routes = [
            'class' => 'api.php',
            'modelName' => ucfirst($modelName),
            'path' => $this->routesPath,
            'stubFile' =>'app/Console/ClassTemplates/routes.stub',
            'replacements' => []
        ];

        return [
            $baseRepositoryInterface,
            $customRepositoryInterface,
            $baseRepository,
            $customRepository,
            $repositoryService,
            $controller,
//            $routes
        ];
    }
}
