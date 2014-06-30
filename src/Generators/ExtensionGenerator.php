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
		$this->path = str_replace('extensions', 'workbench', $this->path);

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
	 * Creates a new model.
	 *
	 * @param  string  $name
	 * @return void
	 */
	public function createModel($name = null)
	{
		$className = studly_case(ucfirst($name ?: $this->extension->name));

		$content = $this->prepare($this->stubsPath.'model.stub', [
			'class_name'  => $className,
			'table'       => strtolower(Str::plural($name)),
			'lower_model' => strtolower($name),
		]);

		$path = $this->path.'/src/Models/'.$className.'.php';

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
		$name = studly_case(ucfirst($name ?: $this->extension->name));

		$content = $this->prepare($this->stubsPath.'widget.stub', [
			'class_name' => $name,
		]);

		$path = $this->path.'/src/Widgets/'.$name.'.php';

		$this->ensureDirectory($path);

		$this->files->put($path, $content);
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
		if (isset($args['columns']))
		{
			$cols = "'id',\n";

			foreach ($args['columns'] as $column)
			{
				$cols .= "\t\t\t'".$column['field']."',\n";
			}

			$cols .= "\t\t\t'created_at',\n";

			$args['columns'] = trim($cols);
		}
		else
		{
			$args['columns'] = "'*',";
		}

		$controllerName = studly_case(ucfirst(($name ? Str::plural($name): $this->extension->name).'Controller'));

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
			'class_name'         => $controllerName,
			'location'           => $location,
			'model'              => studly_case(ucfirst($name)),
			'lower_model'        => studly_case(strtolower($name)),
			'plural_name'        => studly_case(ucfirst(Str::plural($name))),
			'plural_lower_model' => strtolower(Str::plural($name)),
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
		$this->updateResource('routes', $resource);
	}

	/**
	 * Writes the register section.
	 *
	 * @param  string  $resource
	 * @return void
	 */
	public function writeRegister($resource)
	{
		$this->updateResource('register', $resource);
	}

	/**
	 * Writes the boot section.
	 *
	 * @param  string  $resource
	 * @return void
	 */
	public function writeBoot($resource)
	{
		$this->updateResource('boot', $resource);
	}

	/**
	 * Writes the permissions section.
	 *
	 * @param  string  $resource
	 * @return void
	 */
	public function writePermissions($resource)
	{
		$content = $this->files->get($this->path.'/extension.php');

		$newResources = $this->prepare($this->stubsPath.'permissions'.'.stub', [
			'plural_name'        => ucfirst(Str::plural($resource)),
			'model'              => ucfirst($resource),
			'lower_model'        => strtolower($resource),
			'plural_lower_model' => strtolower(Str::plural($resource)),
		]);

		preg_match('/'.'\''.'permissions'.'\' => function\(.*?\)\s*\n\s*{\s*return \[\n(.*?)\s*?\];/s', $content, $oldResources);

		$oldResources = last($oldResources);

		if (strpos(trim($oldResources), trim($newResources)) !== false)
		{
			return;
		}

		$resources = $oldResources."\n\n".$newResources;

		$stub = 'empty-permissions.stub';

		$resourceReplacement = $this->prepare($this->stubsPath.$stub, [
			'content' => trim($resources),
			'type'    => 'permissions',
		]);

		$content = preg_replace(
			"/'permissions' => function\s*.*?},/s",
			rtrim($resourceReplacement),
			$content
		);

		$this->files->put($this->path.'/extension.php', $content);
	}

	/**
	 * Writes the menu items.
	 *
	 * @param  string  $resource
	 * @return void
	 */
	public function writeMenus($resource)
	{
		$content = $this->files->get($this->path.'/extension.php');

		$newMenu = [
			'slug'  => 'admin-'.$this->extension->lowerVendor.'-'.$this->extension->lowerName.'-'.strtolower($resource),
			'name'  => Str::plural(Str::title($resource)),
			'class' => 'fa fa-circle-o',
			'uri'   => $this->extension->lowerName.'/'.Str::plural(Str::lower($resource)),
		];

		$menus = array_get($this->files->getRequire($this->path.'/extension.php'), 'menus');

		$children = [];

		if ($admin = array_get($menus, 'admin'))
		{
			foreach ($admin as $child)
			{
				if ($children = array_get($child, 'children'))
				{
					foreach ($children as $_child)
					{
						if ($_child === $newMenu)
						{
							return;
						}
					}
				}
			}
		}

		if ( ! $children)
		{
			$children = [
				$newMenu,
			];
		}
		else
		{
			$children[] = $newMenu;
		}

		$menus['admin'][0]['children'] = $children;

		$newMenu = "'menus' => [\n\n\t\t".$this->wrapArray($menus, "\t")."\n\t],\n\n";

		$content = preg_replace(
			"/'menus' => \[(.*)\]\s*,/s",
			rtrim($newMenu),
			$content
		);

		$this->files->put($this->path.'/extension.php', $content);
	}

	/**
	 * Writes the data grid language files.
	 *
	 * @param  array  $columns
	 * @return void
	 */
	public function writeLang($resource)
	{
		$this->ensureDirectory($this->path.'/lang/en/'.strtolower(Str::plural($resource)).'/general.php');

		$generalMainPath = $this->path.'/lang/en/general.php';

		if ( ! $this->files->exists($generalMainPath))
		{
			$generalMain = $this->prepare($this->stubsPath.'lang/en/general-main.stub');

			$this->files->put($generalMainPath, $generalMain);
		}

		$stub = $this->stubsPath.'lang/en/general.stub';

		$content = $this->prepare($stub, [
			'model'        => ucfirst($resource),
			'lower_model'  => strtolower($resource),
			'plural_model' => Str::title(Str::plural($resource)),
		]);

		$this->files->put($this->path.'/lang/en/'.strtolower(Str::plural($resource)).'/general.php', $content);

		$stub = $this->stubsPath.'lang/en/message.stub';

		$content = $this->prepare($stub, [
			'model'       => ucfirst($resource),
			'lower_model' => strtolower($resource),
		]);

		$this->files->put($this->path.'/lang/en/'.strtolower(Str::plural($resource)).'/message.php', $content);

		$stub = $this->stubsPath.'lang/en/permissions.stub';

		$content = $this->prepare($stub, [
			'model'       => ucfirst($resource),
			'plural_name' => ucfirst(Str::plural($resource)),
		]);

		$this->files->put($this->path.'/lang/en/'.strtolower(Str::plural($resource)).'/permissions.php', $content);
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
	 * Updates extension resources on extension.php.
	 *
	 * @param  string  $type
	 * @param  string  $resource
	 * @return void
	 */
	protected function updateResource($type, $resource, $stub = null)
	{
		$content = $this->files->get($this->path.'/extension.php');

		$newResources = $this->prepare($this->stubsPath.$type.'.stub', [
			'plural_name'        => studly_case(ucfirst(Str::plural($resource))),
			'model'              => studly_case(ucfirst($resource)),
			'lower_model'        => studly_case(strtolower($resource)),
			'plural_lower_model' => strtolower(Str::plural($resource)),
		]);

		preg_match('/'.'\''.$type.'\' => function\(.*?\)\s*\n\s*{(.*?)\s*},/s', $content, $oldResources);

		preg_match('/'.'\''.$type.'\' => function\(.*?\)\s*\n\s*{(.*?)\s*},/s', $newResources, $newResources);

		$oldResources = last($oldResources);
		$newResources = last($newResources);

		if (strpos($oldResources, $newResources) !== false)
		{
			return;
		}

		$resources = $oldResources."\n".$newResources;

		$stub = $stub ?: 'empty-extension-closure.stub';

		$resourceReplacement = $this->prepare($this->stubsPath.$stub, [
			'content' => trim($resources),
			'type'    => $type,
		]);

		$content = preg_replace(
			"/'{$type}' => function\s*.*?},/s",
			rtrim($resourceReplacement),
			$content
		);

		$this->files->put($this->path.'/extension.php', $content);
	}

}
