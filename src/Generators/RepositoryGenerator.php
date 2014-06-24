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
		$repositoryInterface = ucfirst($model).'RepositoryInterface';

		$repositoryName = 'Db'.ucfirst($model).'Repository';

		$stub = $this->stubsPath.'repository-interface.stub';

		$content = $this->prepare($stub, [
			'model'       => ucfirst($model),
			'lower_model' => strtolower($model),
			'class_name'  => $repositoryName,
			'repository_interface' => $repositoryInterface,
		]);

		$this->ensureDirectory($this->path.'/src/Repositories/'.$repositoryInterface.'.php');

		$this->files->put($this->path.'/src/Repositories/'.$repositoryInterface.'.php', $content);

		$stub = $this->stubsPath.'db-repository.stub';

		$content = $this->prepare($stub, [
			'model'       => ucfirst($model),
			'lower_model' => strtolower($model),
			'class_name'  => $repositoryName,
			'repository_interface' => $repositoryInterface,
		]);

		$this->files->put($this->path.'/src/Repositories/'.$repositoryName.'.php', $content);
	}

}
