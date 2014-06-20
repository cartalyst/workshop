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
	public function create($model, $interface = true, $bindRoutes = false)
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

		if ($bindRoutes)
		{
			$this->writeRegisterAndRoutes($model);
		}
	}

	/**
	 * {@inheritDoc}
	 */
	protected function writeRegisterAndRoutes($model)
	{
		$registerReplacement = $this->prepare($this->stubsPath.'repository-registration.stub', [
			'model' => ucfirst($model),
		]);

		$extensionContent = $this->files->get($this->path.'/extension.php');

		$extensionContent = preg_replace(
			"/'register' => function\s*.*?},/s",
			rtrim($registerReplacement),
			$extensionContent
		);

		$routesReplacement = $this->prepare($this->stubsPath.'routes.stub');

		$extensionContent = preg_replace(
			"/'routes' => function\s*.*?},/s",
			rtrim($routesReplacement),
			$extensionContent
		);

		$this->files->put($this->path.'/extension.php', $extensionContent);
	}

}
