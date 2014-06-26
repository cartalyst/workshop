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

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;

class ExtensionGenerator extends Generator {

	/**
	 * Foundation blocks.
	 *
	 * @var array
	 */
	protected $blocks;

	/**
	 * Constructor.
	 *
	 * @param  string  $slug
	 * @param \Illuminate\Filesystem\Filesystem  $files
	 * @param array  $blocks
	 * @param \Illuminate\Html\HtmlBuilder  $html
	 * @param \Illuminate\Html\FormBuilder  $form
	 * @return void
	 */
	public function __construct($slug, Filesystem $files, $blocks = [], $html = null, $form = null)
	{
		parent::__construct($slug, $files, $html, $form);

		$this->blocks = $blocks;
	}

	/**
	 * Create a new extension.
	 *
	 * @return string
	 */
	public function create()
	{
		if ( ! $this->files->isDirectory($this->path))
		{
			$this->files->makeDirectory($this->path, 0777, true);
		}

		// Write composer.json
		$this->writeComposerFile();

		// Write extension.php
		$this->writeExtensionFile();

		// Process all other blocks
		$this->process($this->path, $this->blocks);
	}

	/**
	 * {@inheritDoc}
	 */
	public function prepare($path, $args = [])
	{
		$content = $this->files->get($path);

		foreach ((array) $this->extension as $key => $value)
		{
			$content = str_replace('{{'.snake_case($key).'}}', $value, $content);
		}

		foreach ($args as $key => $value)
		{
			$content = str_replace('{{'.snake_case($key).'}}', $value, $content);
		}

		return $content;
	}

	/**
	 * Creates a new model.
	 *
	 * @param  string  $name
	 * @return void
	 */
	public function createModel($name = null)
	{
		$name = ucfirst($name ?: $this->extension->name);

		$content = $this->prepare($this->stubsPath.'model.stub', [
			'class_name' => $name,
			'table'      => strtolower(Str::plural($name)),
		]);

		$path = $this->path.'/src/Models/'.$name.'.php';

		$this->ensureDirectory($path);

		$this->files->put($path, $content);
	}

	/**
	 * Creates a new widget.
	 *
	 * @param  string  $name
	 * @return void
	 */
	public function createWidget($name = null)
	{
		$name = ucfirst($name ?: $this->extension->name);

		array_set($this->blocks, 'src.Widgets', [
			$name => 'widget.stub',
		]);

		$this->process();
	}

	/**
	 * Creates a new controller.
	 *
	 * @param  string  $name
	 * @param  string  $location
	 * @return void
	 */
	public function createController($name = null, $location = 'Admin', $args = [])
	{
		$controllerName = ucfirst(($name ? Str::plural($name): $this->extension->name).'Controller');

		$location = ucfirst($location);

		if (in_array($location, ['Admin', 'Frontend']))
		{
			$stub = Str::lower($location).'-controller.stub';
		}
		else
		{
			$stub = 'controller.stub';
		}

		$args = array_merge($args, [
			'location'    => $location,
			'model'       => ucfirst($name),
			'lower_model' => strtolower($name),
			'plural_name' => ucfirst(Str::plural($name)),
		]);

		$content = $this->prepare($this->stubsPath.$stub, $args);

		$path = $this->path.'/src/Controllers/'.$location.'/'.$controllerName.'.php';

		$this->ensureDirectory($path);

		$this->files->put($path, $content);
	}

	/**
	 * Writes the composer.json file.
	 *
	 * @return void
	 */
	public function writeComposerFile()
	{
		$content = $this->prepare($this->stubsPath.'composer.json');

		$autoloads = [
			'database/migrations',
			'database/seeds',
		];

		$content = str_replace('{{classmap_autoloads}}', implode(",\n\t\t\t", array_map(function($autoload)
		{
			return '"'.$autoload.'"';
		}, $autoloads)), $content);

		$this->files->put($this->path.'/composer.json', $content);
	}

	/**
	 * Writes the extension.php file.
	 *
	 * @return void
	 */
	public function writeExtensionFile()
	{
		$content = $this->prepare($this->stubsPath.'extension.stub');

		$this->files->put($this->path.'/extension.php', $content);
	}

	/**
	 * Writes the routes section.
	 *
	 * @return void
	 */
	public function writeRoutes($resource)
	{
		$extensionContent = $this->files->get($this->path.'/extension.php');

		$routesReplacement = $this->prepare($this->stubsPath.'routes.stub', [
			'plural_name' => ucfirst(Str::plural($resource)),
		]);

		$extensionContent = preg_replace(
			"/'routes' => function\s*.*?},/s",
			rtrim($routesReplacement),
			$extensionContent
		);

		$this->files->put($this->path.'/extension.php', $extensionContent);
	}

	/**
	 * Writes the register section.
	 *
	 * @param  string  $resource
	 * @return void
	 */
	public function writeRegister($resource)
	{
		$extensionContent = $this->files->get($this->path.'/extension.php');

		$registerReplacement = $this->prepare($this->stubsPath.'register.stub', [
			'model' => ucfirst($resource),
		]);

		$extensionContent = preg_replace(
			"/'register' => function\s*.*?},/s",
			rtrim($registerReplacement),
			$extensionContent
		);

		$this->files->put($this->path.'/extension.php', $extensionContent);
	}

	/**
	 * Writes the boot section.
	 *
	 * @param  string  $resource
	 * @return void
	 */
	public function writeBoot($resource)
	{
		$extensionContent = $this->files->get($this->path.'/extension.php');

		$bootReplacement = $this->prepare($this->stubsPath.'boot.stub', [
			'model' => ucfirst($resource),
		]);

		$extensionContent = preg_replace(
			"/'boot' => function\s*.*?},/s",
			rtrim($bootReplacement),
			$extensionContent
		);

		$this->files->put($this->path.'/extension.php', $extensionContent);
	}

	/**
	 * Writes the permissions section.
	 *
	 * @param  string  $resource
	 * @return void
	 */
	public function writePermissions($resource)
	{
		$extensionContent = $this->files->get($this->path.'/extension.php');

		$permissionsReplacement = $this->prepare($this->stubsPath.'permissions.stub', [
			'model'       => ucfirst($resource),
			'plural_name' => ucfirst(Str::plural($resource)),
		]);

		$extensionContent = preg_replace(
			"/'permissions' => function\s*.*?},/s",
			rtrim($permissionsReplacement),
			$extensionContent
		);

		$this->files->put($this->path.'/extension.php', $extensionContent);
	}

	/**
	 * Writes the data grid language files.
	 *
	 * @param  array  $columns
	 * @return void
	 */
	public function writeLang($resource)
	{
		$this->ensureDirectory($this->path.'/lang/en/general.php');

		$stub = $this->stubsPath.'lang/en/general.stub';

		$content = $this->prepare($stub, [
			'model'       => ucfirst($resource),
			'lower_model' => strtolower($resource),
		]);

		$this->files->put($this->path.'/lang/en/general.php', $content);

		$stub = $this->stubsPath.'lang/en/message.stub';

		$content = $this->prepare($stub, [
			'model'       => ucfirst($resource),
			'lower_model' => strtolower($resource),
		]);

		$this->files->put($this->path.'/lang/en/message.php', $content);

		$stub = $this->stubsPath.'lang/en/permissions.stub';

		$content = $this->prepare($stub, [
			'model'       => ucfirst($resource),
			'plural_name' => ucfirst(Str::plural($resource)),
		]);

		$this->files->put($this->path.'/lang/en/permissions.php', $content);
	}

}
