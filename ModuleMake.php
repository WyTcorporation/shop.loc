<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;

class ModuleMake extends Command
{

    private $files;
    private string|null $path = null;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:module {name}
                                                   {--all}
                                                   {--controller}
                                                   {--request}
                                                   {--resource}
                                                   {--provider}
                                                   {--middleware}
                                                   {--policy}
                                                   {--event}
                                                   {--exception}
                                                   {--job}
                                                   {--listener}
                                                   {--model}
                                                   {--rule}
                                                   {--service}
                                                   {--migration}
                                                   {--seed}
                                                   {--repository}
                                                   {--path= : The location where the module should be created.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate a module structure and blueprint for classes';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(Filesystem $filesystem)
    {
        parent::__construct();

        $this->files = $filesystem;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {

        if ($this->option('all')) {
//            Application
            $this->input->setOption('controller', true);
            $this->input->setOption('request', true);
            $this->input->setOption('resource', true);
            $this->input->setOption('provider', true);
            $this->input->setOption('middleware', true);
            $this->input->setOption('policy', true);
//            Domain
            $this->input->setOption('event', true);
            $this->input->setOption('exception', true);
            $this->input->setOption('job', true);
            $this->input->setOption('listener', true);
            $this->input->setOption('model', true);
            $this->input->setOption('migration', true);
            $this->input->setOption('seed', true);
            $this->input->setOption('repository', true);
            $this->input->setOption('rule', true);
            $this->input->setOption('service', true);
        }

        if ($this->option('path')) {
            $this->path = $this->option('path');
        }

        if ($this->option('controller')) {
            $this->createController();
        }

        if ($this->option('request')) {
            $this->createRequest();
        }

        if ($this->option('resource')) {
            $this->createResources();
        }

        if ($this->option('provider')) {
            $this->createProvider();
        }

        if ($this->option('middleware')) {
            $this->createMiddleware();
        }

        if ($this->option('policy')) {
            $this->createPolicy();
        }

        if ($this->option('event')) {
            $this->createEvent();
        }

        if ($this->option('exception')) {
            $this->createException();
        }

        if ($this->option('job')) {
            $this->createJob();
        }

        if ($this->option('listener')) {
            $this->createListener();
        }

        if ($this->option('model')) {
            $this->createModel();
        }

        if ($this->option('migration')) {
            $this->createMigration();
        }

        if ($this->option('seed')) {
            $this->createSeeders();
        }

        if ($this->option('repository')) {
            $this->createRepository();
        }

        if ($this->option('rule')) {
            $this->createRule();
        }

        if ($this->option('service')) {
            $this->createService();
        }

    }

    private function createController()
    {
        $controller = Str::studly(class_basename($this->argument('name')));
        $modelName = Str::singular(Str::studly(class_basename($this->argument('name'))));

        $path = $this->getControllerPath($this->argument('name'));


        if ($this->alreadyExists($path)) {
            $this->error('Controller already exists!');
        } else {
            $this->makeDirectory($path);

            $stub = $this->files->get(base_path('resources/stubs/controller.stub'));
            $today = date("Y-m-d H:i:s");
            $stub = str_replace(
                [
                    'DummyDate',
                    'DummyNamespace',
                    'DummyRootNamespace',
                    'DummyClass',
                    'DummyFullModelClass',
                    'DummyModelClass',
                    'DummyModelVariable',
                    'DummyStoreRequest',
                    'DummyUpdateRequest',
                    'DummyNameStoreRequest',
                    'DummyNameUpdateRequest',
                    'DummyResource',
                    'DummyResourceCollection',
                    'DummyNameResource',
                    'DummyNameResourceCollection',
                ],
                [
                    $today,
                    "OVL\\" . str_replace('/', '\\', trim($this->argument('name'))) . "\\Application\\Http\\Controllers",
                    $this->laravel->getNamespace(),
                    $controller . 'Controller',
                    "OVL\\" . str_replace('/', '\\', trim($this->argument('name'))) . "\\Domain\\Models\\{$modelName}",
                    $modelName,
                    lcfirst(($modelName)),
                    "OVL\\" . str_replace('/', '\\', trim($this->argument('name'))) . "\\Application\\Http\\Requests\\Store{$modelName}Request",
                    "OVL\\" . str_replace('/', '\\', trim($this->argument('name'))) . "\\Application\\Http\\Requests\\Update{$modelName}Request",
                    "Store{$modelName}Request",
                    "Update{$modelName}Request",
                    "OVL\\" . str_replace('/', '\\', trim($this->argument('name'))) . "\\Application\\Http\\Resources\\{$modelName}Resource",
                    "OVL\\" . str_replace('/', '\\', trim($this->argument('name'))) . "\\Application\\Http\\Resources\\{$modelName}ResourceCollection",
                    "{$modelName}Resource",
                    "{$modelName}ResourceCollection",
                ],
                $stub
            );

            $this->files->put($path, $stub);
            $this->info('Controller created successfully.');
        }

        $this->createRoutes($controller, $modelName);
    }

    private function createRequest()
    {
        $modelName = Str::singular(Str::studly(class_basename($this->argument('name'))));

        $path = $this->getStoreRequestPath($this->argument('name'));

        if ($this->alreadyExists($path)) {
            $this->error('StoreRequests already exists!');
        } else {
            $this->makeDirectory($path);

            $stub = $this->files->get(base_path('resources/stubs/request.stub'));
            $today = date("Y-m-d H:i:s");
            $AccessName = strtoupper($modelName) . '_ACCESS';
            $stub = str_replace(
                [
                    'DummyDate',
                    'DummyNamespace',
                    'DummyPermissions',
                    'DummyRequest',
                    'AccessName'
                ],
                [
                    $today,
                    "OVL\\" . str_replace('/', '\\', trim($this->argument('name'))) . "\\Application\\Http\\Requests",
                    "OVL\\User\\Domain\\Enums\\UserPermissionsEnum",
                    "Store{$modelName}Request",
                    $AccessName
                ],
                $stub
            );

            $this->files->put($path, $stub);
            $this->info('StoreRequests created successfully.');
        }

        $path = $this->getUpdateRequestPath($this->argument('name'));

        if ($this->alreadyExists($path)) {
            $this->error('UpdateRequests already exists!');
        } else {
            $this->makeDirectory($path);

            $stub = $this->files->get(base_path('resources/stubs/request.stub'));
            $today = date("Y-m-d H:i:s");
            $AccessName = strtoupper($modelName) . '_ACCESS';
            $stub = str_replace(
                [
                    'DummyDate',
                    'DummyNamespace',
                    'DummyPermissions',
                    'DummyRequest',
                    'AccessName'
                ],
                [
                    $today,
                    "OVL\\" . str_replace('/', '\\', trim($this->argument('name'))) . "\\Application\\Http\\Requests",
                    "OVL\\User\\Domain\\Enums\\UserPermissionsEnum",
                    "Update{$modelName}Request",
                    $AccessName
                ],
                $stub
            );

            $this->files->put($path, $stub);
            $this->info('UpdateRequests created successfully.');
        }
    }

    private function createResources()
    {
        $modelName = Str::singular(Str::studly(class_basename($this->argument('name'))));

        $path = $this->getResourcesPath($this->argument('name'));

        if ($this->alreadyExists($path)) {
            $this->error('Resources already exists!');
        } else {
            $this->makeDirectory($path);

            $stub = $this->files->get(base_path('resources/stubs/resources.stub'));
            $today = date("Y-m-d H:i:s");
            $stub = str_replace(
                [
                    'DummyDate',
                    'DummyNamespace',
                    'DummyModelPath',
                    'DummyModel',
                    'DummyResource',
                ],
                [
                    $today,
                    "OVL\\" . str_replace('/', '\\', trim($this->argument('name'))) . "\\Application\\Http\\Resources",
                    "OVL\\{$modelName}\\Domain\\Models\\{$modelName}",
                    $modelName,
                    $modelName . 'Resource'
                ],
                $stub
            );

            $this->files->put($path, $stub);
            $this->info('Resource created successfully.');
        }

        $pathCollection = $this->getResourcesCollectionPath($this->argument('name'));

        if ($this->alreadyExists($pathCollection)) {
            $this->error('ResourceCollection already exists!');
        } else {
            $this->makeDirectory($pathCollection);

            $stub = $this->files->get(base_path('resources/stubs/resources_collection.stub'));
            $today = date("Y-m-d H:i:s");
            $stub = str_replace(
                [
                    'DummyDate',
                    'DummyNamespace',
                    'DummyResourceCollection',
                    'DummyName',
                    'DummyResource',
                ],
                [
                    $today,
                    "OVL\\" . str_replace('/', '\\', trim($this->argument('name'))) . "\\Application\\Http\\Resources",
                    $modelName . 'ResourceCollection',
                    lcfirst($modelName),
                    $modelName . 'Resource'
                ],
                $stub
            );

            $this->files->put($pathCollection, $stub);
            $this->info('ResourceCollection created successfully.');
        }
    }

    private function createMiddleware()
    {
        $modelName = Str::singular(Str::studly(class_basename($this->argument('name'))));

        $path = $this->getMiddlewarePath($this->argument('name'));

        if ($this->alreadyExists($path)) {
            $this->error('Middleware already exists!');
        } else {
            $this->makeDirectory($path);

            $stub = $this->files->get(base_path('resources/stubs/middleware.stub'));
            $today = date("Y-m-d H:i:s");
            $stub = str_replace(
                [
                    'DummyDate',
                    'DummyNamespace',
                ],
                [
                    $today,
                    "OVL\\" . str_replace('/', '\\', trim($this->argument('name'))) . "\\Application\\Http\\Middleware",
                ],
                $stub
            );

            $this->files->put($path, $stub);
            $this->info('Middleware created successfully.');
        }
    }

    private function createPolicy()
    {
        $modelName = Str::singular(Str::studly(class_basename($this->argument('name'))));

        $path = $this->getPolicyPath($this->argument('name'));

        if ($this->alreadyExists($path)) {
            $this->error('Controller already exists!');
        } else {
            $this->makeDirectory($path);

            $stub = $this->files->get(base_path('resources/stubs/policy.stub'));
            $today = date("Y-m-d H:i:s");
            $AccessName = strtoupper($modelName) . '_ACCESS';
            $dummyFuncModel = '$' . strtolower($modelName);
            $stub = str_replace(
                [
                    'DummyDate',
                    'DummyNamespace',
                    'DummyModel',
                    'DummyName',
                    'AccessName',
                    'DummyFuncNameModel',
                    'DummyFuncModel',
                ],
                [
                    $today,
                    "OVL\\" . str_replace('/', '\\', trim($this->argument('name'))) . "\\Application\\Policies",
                    "OVL\\" . str_replace('/', '\\', trim($this->argument('name'))) . "\\Domain\\Models\\{$modelName}",
                    "{$modelName}Policy",
                    $AccessName,
                    $modelName,
                    $dummyFuncModel
                ],
                $stub
            );

            $this->files->put($path, $stub);
            $this->info('Controller created successfully.');
        }
    }

    private function createProvider()
    {
        $modelName = Str::singular(Str::studly(class_basename($this->argument('name'))));

        $path = $this->getProviderPath($this->argument('name'));

        if ($this->alreadyExists($path)) {
            $this->error('Provider already exists!');
        } else {
            $this->makeDirectory($path);

            $stub = $this->files->get(base_path('resources/stubs/provider.stub'));
            $today = date("Y-m-d H:i:s");
            $stub = str_replace(
                [
                    'DummyDate',
                    'DummyNamespace',
                    'DummyNameProvider',
                    'DummyNameMerge',
                ],
                [
                    $today,
                    "OVL\\" . str_replace('/', '\\', trim($this->argument('name'))) . "\\Providers",
                    "{$modelName}Provider",
                    lcfirst($modelName)
                ],
                $stub
            );

            $this->files->put($path, $stub);
            $this->info('Provider created successfully.');
        }

        $routeServiceProviderPath = $this->getRouteServiceProviderPath($this->argument('name'));

        if ($this->alreadyExists($routeServiceProviderPath)) {
            $this->error('RouteServiceProvider already exists!');
        } else {
            $this->makeDirectory($routeServiceProviderPath);

            $stub = $this->files->get(base_path('resources/stubs/route_service_provider.stub'));
            $today = date("Y-m-d H:i:s");
            $stub = str_replace(
                [
                    'DummyDate',
                    'DummyNamespace',
                    'DummyModelName'
                ],
                [
                    $today,
                    "OVL\\" . str_replace('/', '\\', trim($this->argument('name'))) . "\\Providers",
                    lcfirst($modelName)
                ],
                $stub
            );

            $this->files->put($routeServiceProviderPath, $stub);
            $this->info('RouteServiceProvider created successfully.');
        }
    }

    private function createEvent()
    {
        $model = Str::singular(Str::studly(class_basename($this->argument('name'))));

        $path = $this->getEventPath($this->argument('name'));
        if ($this->alreadyExists($path)) {
            $this->error('Event already exists!');
        } else {
            $this->makeDirectory($path);
            $stub = $this->files->get(base_path('resources/stubs/event.stub'));
            $today = date("Y-m-d H:i:s");
            $stub = str_replace(
                [
                    'DummyDate',
                    'DummyNamespace'
                ],
                [
                    $today,
                    "OVL\\" . $model . "\\Domain\\Events",
                ],
                $stub
            );

            $this->files->put($path, $stub);
            $this->info('Event created successfully.');
        }
    }

    private function createException()
    {
        $model = Str::singular(Str::studly(class_basename($this->argument('name'))));

        $path = $this->getExceptionPath($this->argument('name'));
        if ($this->alreadyExists($path)) {
            $this->error('Exception already exists!');
        } else {
            $this->makeDirectory($path);
            $stub = $this->files->get(base_path('resources/stubs/exception.stub'));
            $today = date("Y-m-d H:i:s");
            $stub = str_replace(
                [
                    'DummyDate',
                    'DummyNamespace'
                ],
                [
                    $today,
                    "OVL\\" . $model . "\\Domain\\Exceptions",
                ],
                $stub
            );

            $this->files->put($path, $stub);
            $this->info('Exception created successfully.');
        }
    }

    private function createJob()
    {
        $model = Str::singular(Str::studly(class_basename($this->argument('name'))));

        $path = $this->getJobPath($this->argument('name'));
        if ($this->alreadyExists($path)) {
            $this->error('Job already exists!');
        } else {
            $this->makeDirectory($path);
            $stub = $this->files->get(base_path('resources/stubs/job.stub'));
            $today = date("Y-m-d H:i:s");
            $stub = str_replace(
                [
                    'DummyDate',
                    'DummyNamespace'
                ],
                [
                    $today,
                    "OVL\\" . $model . "\\Domain\\Jobs",
                ],
                $stub
            );

            $this->files->put($path, $stub);
            $this->info('Job created successfully.');
        }
    }

    private function createListener()
    {
        $model = Str::singular(Str::studly(class_basename($this->argument('name'))));

        $path = $this->getListenerPath($this->argument('name'));
        if ($this->alreadyExists($path)) {
            $this->error('Listener already exists!');
        } else {
            $this->makeDirectory($path);
            $stub = $this->files->get(base_path('resources/stubs/listener.stub'));
            $today = date("Y-m-d H:i:s");
            $stub = str_replace(
                [
                    'DummyDate',
                    'DummyNamespace'
                ],
                [
                    $today,
                    "OVL\\" . $model . "\\Domain\\Listeners",
                ],
                $stub
            );

            $this->files->put($path, $stub);
            $this->info('Listener created successfully.');
        }
    }

    private function createModel()
    {
        $model = Str::singular(Str::studly(class_basename($this->argument('name'))));

        $path = $this->getModelPath($this->argument('name'));
        if ($this->alreadyExists($path)) {
            $this->error('Model already exists!');
        } else {
            $this->makeDirectory($path);
            $stub = $this->files->get(base_path('resources/stubs/model.stub'));
            $today = date("Y-m-d H:i:s");
            $stub = str_replace(
                [
                    'DummyDate',
                    'DummyNamespace',
                    'DummyRootNamespace',
                    'DummyFactory',
                    'DummyModel',
                ],
                [
                    $today,
                    "OVL\\" . $model . "\\Domain\\Models",
                    $model,
                    $model . 'Factory',
                    $model,
                ],
                $stub
            );

            $this->files->put($path, $stub);
            $this->info('Model created successfully.');
        }

        $path = $this->getFactoryPath($this->argument('name'));
        if ($this->alreadyExists($path)) {
            $this->error('Factory already exists!');
        } else {
            $this->makeDirectory($path);
            $stub = $this->files->get(base_path('resources/stubs/factory.stub'));
            $today = date("Y-m-d H:i:s");
            $stub = str_replace(
                [
                    'DummyDate',
                    'DummyNamespace',
                    'DummyModelPath',
                    'DummyModel',
                    'DummyFactory',
                ],
                [
                    $today,
                    "OVL\\" . $model . "\\Infrastructure\\Database\\Factories",
                    "OVL\\" . $model . "\\Domain\\Models\\{$model}",
                    $model,
                    $model . 'Factory'
                ],
                $stub
            );

            $this->files->put($path, $stub);
            $this->info('Factory created successfully.');
        }
    }

    private function createMigration()
    {
        $table = Str::plural(Str::snake(class_basename($this->argument('name'))));
        $p = $this->path ?? $this->argument('name');
        $path = str_replace('\\', '/', trim($p));
        try {
            $this->call('make:migration', [
                'name' => "create_{$table}_table",
                '--create' => $table,
                '--path' => "src/" . $path . "/Infrastructure/Database/Migrations"
            ]);
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }
    }

    private function createSeeders()
    {
        $name = Str::singular(Str::studly(class_basename($this->argument('name'))));
        $tableName = Str::plural(Str::snake(class_basename($this->argument('name'))));
        $path = $this->getSeedersPath($this->argument('name'));
        if ($this->alreadyExists($path)) {
            $this->error('Seed already exists!');
        } else {
            $this->makeDirectory($path);
            $stub = $this->files->get(base_path('resources/stubs/seed.stub'));
            $today = date("Y-m-d H:i:s");
            $stub = str_replace(
                [
                    'DummyDate',
                    'DummyNamespace',
                    'DummySeeder',
                    'DummyTable'
                ],
                [
                    $today,
                    "OVL\\" . $name . "\\Infrastructure\\Database\\Seeders",
                    $name . 'Seeder',
                    $tableName
                ],
                $stub
            );

            $this->files->put($path, $stub);
            $this->info('Seed created successfully.');
        }

    }

    private function createRepository()
    {
        $name = Str::singular(Str::studly(class_basename($this->argument('name'))));
        $path = $this->getRepositoryPath($this->argument('name'));
        if ($this->alreadyExists($path)) {
            $this->error('Repository already exists!');
        } else {
            $this->makeDirectory($path);
            $stub = $this->files->get(base_path('resources/stubs/repository.stub'));
            $today = date("Y-m-d H:i:s");
            $stub = str_replace(
                [
                    'DummyDate',
                    'DummyNamespace'
                ],
                [
                    $today,
                    "OVL\\" . $name . "\\Infrastructure\\Repositories",
                ],
                $stub
            );

            $this->files->put($path, $stub);
            $this->info('Repository created successfully.');
        }

    }

    private function createRule()
    {
        $model = Str::singular(Str::studly(class_basename($this->argument('name'))));

        $path = $this->getRulePath($this->argument('name'));
        if ($this->alreadyExists($path)) {
            $this->error('Rule already exists!');
        } else {
            $this->makeDirectory($path);
            $stub = $this->files->get(base_path('resources/stubs/rule.stub'));
            $today = date("Y-m-d H:i:s");
            $stub = str_replace(
                [
                    'DummyDate',
                    'DummyNamespace'
                ],
                [
                    $today,
                    "OVL\\" . $model . "\\Domain\\Rules",
                ],
                $stub
            );

            $this->files->put($path, $stub);
            $this->info('Rule created successfully.');
        }
    }

    private function createService()
    {
        $model = Str::singular(Str::studly(class_basename($this->argument('name'))));

        $path = $this->getServicePath($this->argument('name'));
        if ($this->alreadyExists($path)) {
            $this->error('Service already exists!');
        } else {
            $this->makeDirectory($path);
            $stub = $this->files->get(base_path('resources/stubs/service.stub'));
            $today = date("Y-m-d H:i:s");
            $stub = str_replace(
                [
                    'DummyDate',
                    'DummyNamespace',
                    'DummyRequest',
                    'DummyNameRequest',
                    'DummyClass'
                ],
                [
                    $today,
                    "OVL\\" . $model . "\\Domain\\Services",
                    "OVL\\" . str_replace('/', '\\', trim($this->argument('name'))) . "\\Application\\Http\\Requests\\{$model}Request",
                    "{$model}Request",
                    "{$model}Service",
                ],
                $stub
            );

            $this->files->put($path, $stub);
            $this->info('Service created successfully.');
        }
    }

    private function createRoutes(string $controller, string $modelName): void
    {

        $routePath = $this->getRoutesPath($this->argument('name'));
        if ($this->alreadyExists($routePath)) {
            $this->error('Routes already exists!');
        } else {
            $this->makeDirectory($routePath);
            $stub = $this->files->get(base_path('resources/stubs/routes.stub'));
            $today = date("Y-m-d H:i:s");
            $stub = str_replace(
                [
                    'DummyDate',
                    'DummyClass',
                    'DummyRoutePrefix',
                    'DummyModelVariable',
                ],
                [
                    $today,
                    $controller . 'Controller',
                    Str::plural(Str::snake(lcfirst($modelName), '-')),
                    lcfirst($modelName)
                ],
                $stub
            );

            $this->files->put($routePath, $stub);
            $this->info('Routes created successfully.');
        }
        $this->createConfig();
    }

    private function createConfig(): void
    {
        $configPath = $this->getConfigPath($this->argument('name'));
        if ($this->alreadyExists($configPath)) {
            $this->error('Config already exists!');
        } else {
            $this->makeDirectory($configPath);
            $stub = $this->files->get(base_path('resources/stubs/config.stub'));
            $this->files->put($configPath, $stub);
            $this->info('Config created successfully.');
        }
    }

    private function makeDirectory($path)
    {
        if (!$this->files->isDirectory(dirname($path))) {
            $this->files->makeDirectory(dirname($path), 0777, true, true);
        }
        return $path;
    }


    private function getEventPath($name)
    {
        $model = Str::studly(class_basename($name));
        $path = $this->path ?? $name;
        return 'src/' . str_replace('\\', '/', $path) . '/Domain/Event/' . "{$model}Event.php";
    }

    private function getExceptionPath($name)
    {
        $model = Str::studly(class_basename($name));
        $path = $this->path ?? $name;
        return 'src/' . str_replace('\\', '/', $path) . '/Domain/Exceptions/' . "{$model}Exception.php";
    }

    private function getJobPath($name)
    {
        $model = Str::studly(class_basename($name));
        $path = $this->path ?? $name;
        return 'src/' . str_replace('\\', '/', $path) . '/Domain/Jobs/' . "{$model}Job.php";
    }

    private function getListenerPath($name)
    {
        $model = Str::studly(class_basename($name));
        $path = $this->path ?? $name;
        return 'src/' . str_replace('\\', '/', $path) . '/Domain/Listeners/' . "{$model}Listener.php";
    }

    private function getRulePath($name)
    {
        $model = Str::studly(class_basename($name));
        $path = $this->path ?? $name;
        return 'src/' . str_replace('\\', '/', $path) . '/Domain/Rules/' . "{$model}Rule.php";
    }

    private function getServicePath($name)
    {
        $model = Str::studly(class_basename($name));
        $path = $this->path ?? $name;
        return 'src/' . str_replace('\\', '/', $path) . '/Domain/Services/' . "{$model}Service.php";
    }

    private function getModelPath($name)
    {
        $model = Str::studly(class_basename($name));
        $path = $this->path ?? $name;
        return 'src/' . str_replace('\\', '/', $path) . '/Domain/Models/' . "{$model}.php";
    }

    private function getFactoryPath($name)
    {
        $model = Str::studly(class_basename($name));
        $path = $this->path ?? $name;
        return 'src/' . str_replace('\\', '/', $path) . '/Infrastructure/Database/Factories/' . "{$model}Factory.php";
    }

    private function getSeedersPath($name)
    {
        $model = Str::studly(class_basename($name));
        $path = $this->path ?? $name;
        return 'src/' . str_replace('\\', '/', $path) . '/Infrastructure/Database/Seeders/' . "{$model}Seeder.php";
    }

    private function getRepositoryPath($name)
    {
        $model = Str::studly(class_basename($name));
        $path = $this->path ?? $name;
        return 'src/' . str_replace('\\', '/', $path) . '/Infrastructure/Repositories/' . "{$model}Repository.php";
    }

    private function getControllerPath($argument)
    {
        $controller = Str::studly(class_basename($argument));
        $path = $this->path ?? $argument;
        return 'src/' . str_replace('\\', '/', $path) . "/Application/Http/Controllers/{$controller}Controller.php";
    }

    private function getRoutesPath($name): string
    {
        $path = $this->path ?? $name;
        return 'src/' . str_replace('\\', '/', $path) . "/routes.php";
    }

    private function getConfigPath($name): string
    {
        $path = $this->path ?? $name;
        return 'src/' . str_replace('\\', '/', $path) . "/config.php";
    }

    protected function alreadyExists($path): bool
    {
        return $this->files->exists($path);
    }

    private function getStoreRequestPath($name)
    {
        $model = Str::singular(Str::studly(class_basename($this->argument('name'))));
        $path = $this->path ?? $name;
        return 'src/' . str_replace('\\', '/', $path) . "/Application/Http/Requests/Store{$model}Request.php";
    }

    private function getUpdateRequestPath($name)
    {
        $model = Str::singular(Str::studly(class_basename($this->argument('name'))));
        $path = $this->path ?? $name;
        return 'src/' . str_replace('\\', '/', $path) . "/Application/Http/Requests/Update{$model}Request.php";
    }

    private function getResourcesPath($name)
    {
        $model = Str::singular(Str::studly(class_basename($this->argument('name'))));
        $path = $this->path ?? $name;
        return 'src/' . str_replace('\\', '/', $path) . "/Application/Http/Resources/{$model}Resource.php";
    }


    private function getResourcesCollectionPath($name)
    {
        $model = Str::singular(Str::studly(class_basename($this->argument('name'))));
        $path = $this->path ?? $name;
        return 'src/' . str_replace('\\', '/', $path) . "/Application/Http/Resources/{$model}ResourceCollection.php";
    }

    private function getMiddlewarePath($name)
    {
        $model = Str::singular(Str::studly(class_basename($this->argument('name'))));
        $path = $this->path ?? $name;
        return 'src/' . str_replace('\\', '/', $path) . "/Application/Http/Middleware/{$model}Middleware.php";
    }

    private function getPolicyPath($name)
    {
        $model = Str::singular(Str::studly(class_basename($this->argument('name'))));
        $path = $this->path ?? $name;
        return 'src/' . str_replace('\\', '/', $path) . "/Application/Policies/{$model}Policy.php";
    }

    private function getProviderPath($name)
    {
        $model = Str::singular(Str::studly(class_basename($this->argument('name'))));
        $path = $this->path ?? $name;
        return 'src/' . str_replace('\\', '/', $path) . "/Providers/{$model}Provider.php";
    }

    private function getRouteServiceProviderPath($name)
    {
        $model = Str::singular(Str::studly(class_basename($this->argument('name'))));
        $path = $this->path ?? $name;
        return 'src/' . str_replace('\\', '/', $path) . "/Providers/RouteServiceProvider.php";
    }


}
