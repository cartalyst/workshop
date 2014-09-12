<?php namespace Cartalyst\Workshop\Generators;
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
 * @version    1.0.0
 * @author     Cartalyst LLC
 * @license    Cartalyst PSL
 * @copyright  (c) 2011-2014, Cartalyst LLC
 * @link       http://cartalyst.com
 */

use Illuminate\Support\Str;

class RepositoryGenerator extends Generator {

	/**
	 * Create a new repository.
	 *
	 * @param  string  $model
	 * @param  bool  $interface
	 * @return void
	 */
	public function create($model, $interface = true)
	{
		$model = Str::studly($model);

		$repositoryInterface = Str::studly(ucfirst($model).'RepositoryInterface');

		$repositoryName = Str::studly('Illuminate'.ucfirst($model).'Repository');

		$stub = $this->getStub('repository-interface.stub');

		$content = $this->prepare($stub, [
			'model'                => ucfirst($model),
			'lower_model'          => Str::lower($model),
			'class_name'           => $repositoryName,
			'repository_interface' => $repositoryInterface,
		]);

		$filePath = $this->path.'/src/Repositories/';

		$this->ensureDirectory($filePath);

		$this->files->put($filePath.$repositoryInterface.'.php', $content);

		$stub = $this->getStub('db-repository.stub');

		$content = $this->prepare($stub, [
			'model'                => ucfirst($model),
			'lower_model'          => Str::lower($model),
			'class_name'           => $repositoryName,
			'repository_interface' => $repositoryInterface,
		]);

		$this->files->put($filePath.$repositoryName.'.php', $content);
	}

}
