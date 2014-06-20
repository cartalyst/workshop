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

class FormGenerator extends Generator {

	/**
	 * Create a new form.
	 *
	 * @param  array  $columns
	 * @param  bool  $interface
	 * @return void
	 */
	public function create($model, $columns = [])
	{
		$stub = $this->stubsPath.'form.stub';

		$el = [];

		$inputStub = $this->stubsPath.'form-input.stub';

		foreach ($columns as $col)
		{
			$el[] = $this->prepare($inputStub, [
				'field_name'  => $col['field'],
				'lower_model' => $model,
			]);
		}

		$content = $this->prepare($stub, [
			'columns' => implode("\n\t\t\t\t", $el),
		]);

		$this->ensureDirectory($this->path.'/themes/admin/default/packages/'.$this->extension->lowerVendor.'/'.$this->extension->lowerName.'/views/form.blade.php');

		$this->files->put($this->path.'/themes/admin/default/packages/'.$this->extension->lowerVendor.'/'.$this->extension->lowerName.'/views/form.blade.php', $content);

		$this->writeRegisterAndRoutes($this->extension->name);
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

		$bootReplacement = $this->prepare($this->stubsPath.'boot.stub', [
			'model' => ucfirst($model),
		]);

		$extensionContent = preg_replace(
			"/'boot' => function\s*.*?},/s",
			rtrim($bootReplacement),
			$extensionContent
		);

		$this->files->put($this->path.'/extension.php', $extensionContent);
	}

	/**
	 * {@inheritDoc}
	 */
	public function prepare($path, $args = [])
	{
		$content = parent::prepare($path, $args);

		foreach ((array) $this->extension as $key => $value)
		{
			$content = str_replace('{{'.snake_case($key).'}}', $value, $content);
		}

		return $content;
	}

}
