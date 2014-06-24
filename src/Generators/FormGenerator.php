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
	public function create($model, $columns = [], $view = 'form')
	{
		$stub = $this->stubsPath.'form.blade.stub';

		$el = [];

		foreach ($columns as $col)
		{
			switch ($col['type']) {
				case 'text':
				case 'mediumText':
				case 'longText':
					$inputStub = $this->stubsPath.'form-textarea.stub';
					break;

				case 'boolean':
				case 'tinyInteger':
					$inputStub = $this->stubsPath.'form-checkbox.stub';
					break;

				case 'string':
				default:
					$inputStub = $this->stubsPath.'form-input.stub';
					break;
			}

			$el[] = $this->prepare($inputStub, [
				'field_name'  => $col['field'],
				'lower_model' => $model,
			]);
		}

		$content = $this->prepare($stub, [
			'columns' => implode("\n\t\t\t\t", $el),
		]);

		$this->ensureDirectory($this->path.'/themes/admin/default/packages/'.$this->extension->lowerVendor.'/'.$this->extension->lowerName.'/views/'.$view.'.blade.php');

		$this->files->put($this->path.'/themes/admin/default/packages/'.$this->extension->lowerVendor.'/'.$this->extension->lowerName.'/views/'.$view.'.blade.php', $content);
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
