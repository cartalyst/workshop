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
use Str;
use InvalidArgumentException;

class ExtensionGenerator extends Generator {

	protected $blocks = [
		'lang' => [
			'en' => [
				'general.stub',
				'table.stub',
			]
		],
		'database' => [
			'migrations' => [
				'.gitkeep',
			],
			'seeds' => [
				'.gitkeep',
			],
		],
	];

	protected $extension;

	/**
	 * Constructor.
	 *
	 * @param \Illuminate\Filesystem\Filesystem  $files
	 * @return void
	 */
	public function __construct($extension, Filesystem $files, $html = null, $form = null)
	{
		parent::__construct($extension, $files, $html, $form);
	}

	/**
	 * Create a new extension.
	 *
	 * @return string
	 */
	public function create($model = false, $controllers = false)
	{
		if ($model)
		{
			$this->createModel();
		}

		if ($controllers)
		{
			$this->createControllers();
		}

		if ( ! $this->files->isDirectory($this->path))
		{
			$this->files->makeDirectory($this->path, 0777, true);
		}

		$this->writeComposerFile();

		$this->writeExtensionFile();

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

		array_set($this->blocks, 'src.Models', [
			$name => 'model.stub',
		]);

		$this->process();
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
	public function createController($name = null, $location = 'Admin')
	{
		$name = ucfirst(($name ?: $this->extension->name).'Controller');

		$location = ucfirst($location);

		if (in_array($location, ['Admin', 'Frontend',]))
		{
			$stub = Str::lower($location).'-controller.stub';
		}
		else
		{
			$stub = 'controller.stub';
		}

		array_set($this->blocks, 'src.Controllers', [
			$location => [
				$name => $stub,
			],
		]);

		$this->process(null, [], [
			'location' => $location,
		]);
	}

	/**
	 * {@inheritDoc}
	 */
	public function writeComposerFile()
	{
		$content = $this->prepare($this->stubsPath.'composer.json', null);

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
	 * {@inheritDoc}
	 */
	public function writeExtensionFile()
	{
		$content = $this->prepare($this->stubsPath.'extension.stub', null, [
			'require_exported' => '[]',
		]);

		$this->files->put($this->path.'/extension.php', $content);
	}

}
