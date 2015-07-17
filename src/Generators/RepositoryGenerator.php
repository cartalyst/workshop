<?php

/**
 * Part of the Workshop package.
 *
 * NOTICE OF LICENSE
 *
 * Licensed under the Cartalyst PSL License.
 *
 * This source file is subject to the Cartalyst PSL License that is
 * bundled with this package in the license.txt file.
 *
 * @package    Workshop
 * @version    2.0.5
 * @author     Cartalyst LLC
 * @license    Cartalyst PSL
 * @copyright  (c) 2011-2015, Cartalyst LLC
 * @link       http://cartalyst.com
 */

namespace Cartalyst\Workshop\Generators;

use Illuminate\Support\Str;

class RepositoryGenerator extends AbstractGenerator
{
    /**
     * Create a new repository.
     *
     * @param  string  $model
     * @return void
     */
    public function create($model)
    {
        $model = $this->sanitize($model);
        $model = ucfirst(Str::studly($model));

        $repositoryInterface = Str::studly($model.'RepositoryInterface');

        $repositoryName = Str::studly($model.'Repository');

        $stub = $this->getStub('repository-interface.stub');

        $content = $this->prepare($stub, [
            'model'                => $model,
            'lower_model'          => Str::lower($model),
            'class_name'           => $repositoryName,
            'repository_interface' => $repositoryInterface,
        ]);

        $filePath = $this->path."/src/Repositories/{$model}/";

        $this->ensureDirectory($filePath);

        $this->files->put($filePath.$repositoryInterface.'.php', $content);

        $stub = $this->getStub('repository.stub');

        $content = $this->prepare($stub, [
            'model'                => $model,
            'lower_model'          => Str::lower($model),
            'class_name'           => $repositoryName,
            'repository_interface' => $repositoryInterface,
        ]);

        $this->files->put($filePath.$repositoryName.'.php', $content);

        // Write event handler interface
        $stub = $this->getStub('event-handler-interface.stub');

        $content = $this->prepare($stub, [
            'model'       => $model,
            'lower_model' => Str::lower($model),
        ]);

        $handlerPath = $this->path."/src/Handlers/{$model}/";

        $this->ensureDirectory($handlerPath);

        $this->files->put($handlerPath.$model.'EventHandlerInterface.php', $content);

        // Write event handler
        $stub = $this->getStub('event-handler.stub');

        $content = $this->prepare($stub, [
            'model'       => $model,
            'lower_model' => Str::lower($model),
        ]);

        $this->files->put($handlerPath.$model.'EventHandler.php', $content);

        // Write data handler interface
        $stub = $this->getStub('data-handler-interface.stub');

        $content = $this->prepare($stub, [
            'model' => $model,
        ]);

        $handlerPath = $this->path."/src/Handlers/{$model}/";

        $this->ensureDirectory($handlerPath);

        $this->files->put($handlerPath.$model.'DataHandlerInterface.php', $content);

        // Write data handler
        $stub = $this->getStub('data-handler.stub');

        $content = $this->prepare($stub, [
            'model' => $model,
        ]);

        $this->files->put($handlerPath.$model.'DataHandler.php', $content);

        // Write validator interface
        $stub = $this->getStub('validator-interface.stub');

        $content = $this->prepare($stub, [
            'model'       => $model,
            'lower_model' => Str::lower($model),
        ]);

        $validatorPath = $this->path."/src/Validator/{$model}/";

        $this->ensureDirectory($validatorPath);

        $this->files->put($validatorPath.$model.'ValidatorInterface.php', $content);

        // Write validator
        $stub = $this->getStub('validator.stub');

        $content = $this->prepare($stub, [
            'model' => $model,
        ]);

        $this->files->put($validatorPath.$model.'Validator.php', $content);
    }
}
