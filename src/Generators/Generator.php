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

use Cartalyst\Workshop\Extension;
use Str;

abstract class Generator implements GeneratorInterface {

	/**
	 * Platform extension.
	 *
	 * @var \Cartalyst\Workshop\Extension
	 */
	protected $extension;

	/**
	 * Filesystem instance.
	 *
	 * @var \Illuminate\Filesystem\Filesystem
	 */
	protected $files;

	/**
	 * Html builder instance.
	 *
	 * @var \Illuminate\Html\HtmlBuilder
	 */
	protected $html;

	/**
	 * Form builder instance.
	 *
	 * @var \Illuminate\Html\FormBuilder
	 */
	protected $form;

	/**
	 * Workbench path.
	 *
	 * @var string
	 */
	protected $path;

	/**
	 * Stubs path.
	 *
	 * @var string
	 */
	protected $stubsPath;

	/**
	 * Constructor.
	 *
	 * @param \Cartalyst\Workshop\Extension  $extension
	 * @param \Illuminate\Filesystem\Filesystem  $files
	 * @param \Illuminate\Html\HtmlBuilder  $html
	 * @param \Illuminate\Html\FormBuilder  $form
	 * @return void
	 */
	public function __construct($extension, $files, $html = null, $form = null)
	{
		if ($extension instanceof Extension)
		{
			$this->extension = $extension;
		}
		else
		{
			$this->extension = new Extension($extension);
		}

		$this->files     = $files;
		$this->html      = $html;
		$this->form      = $form;
		$this->path      = base_path().'/workbench/'.$this->extension->getFullName();
		$this->stubsPath = __DIR__.'/..'.str_replace($this->path, '/stubs/', $this->path);
	}

	/**
	 * Process files and directories.
	 *
	 * @param  string  $path
	 * @param  array  $directories
	 * @param  array  $args
	 * @return void
	 */
	protected function process($path = null, $dir = null, $args = [])
	{
		$path = $path ?: $this->path;

		$dir = $dir ?: $this->blocks;

		if (is_array($dir))
		{
			foreach ($dir as $fileName => $filePath)
			{
				$subdir = $path;

				if ( ! is_numeric($fileName) && is_array($filePath) && strpos($fileName, '.stub') === false)
				{
					$subdir = $path.'/'.$fileName;
				}

				if ( ! is_array($filePath) && strpos($filePath, '.stub') !== false)
				{
					$this->processFile($path, $filePath, $args, $fileName);

					continue;
				}

				$this->process($subdir, $filePath, $args);
			}

			return;
		}

		$this->processFile($path, $dir, $args);

		$this->autoloads();
	}

	/**
	 * Process a file.
	 *
	 * @param  string  $directory
	 * @param  string  $file
	 * @param  array  $args
	 * @param  string  $overriddenFile
	 * @return void
	 */
	protected function processFile($directory, $file, $args = [], $overriddenFile = null)
	{
		$fileName = ! is_numeric($overriddenFile) && isset($overriddenFile) ? $overriddenFile : $file;

		$fullPath = __DIR__.'/..'.str_replace($this->path, '/stubs', $directory).'/'.$file;

		$stubPath = __DIR__.'/../stubs/'.$file;

		$baseDirectory = str_replace($this->path, '', $directory);

		$targetPath = str_replace($this->path.'/workbench/', '', $directory).'/';

		if (strpos($file, '.stub') !== false)
		{
			if (strpos($file, '-') !== false)
			{
				$prefix = ucfirst(last(explode('-', str_replace('.stub', '', $file))));

				$fileName = $overriddenFile ?: $this->extension->name.$prefix;
			}

			$name = $fileName;

			$args['class_name'] = $name;
			$args['plural_name'] = strtolower(Str::plural($name));

			$fileName = $name.'.php';

			if (strpos($fileName, '.stub'))
			{
				$fileName = str_replace('.stub', '.php', $file);
			}
		}

		$targetPath .= $fileName;

		if ( ! $this->files->exists($stubPath))
		{
			$parts = explode('/', $targetPath);

			$filename = array_pop($parts);

			$langDir = str_replace($this->path, __DIR__.'/../stubs', implode('/', $parts));

			if ($this->files->isDirectory($langDir))
			{
				$files = $this->files->allFiles($langDir);

				foreach ($files as $f)
				{
					if($f->getRelativePathname() === $file)
					{
						$stubPath = $langDir.'/'.$file;
					}
				}
			}
		}

		$this->ensureDirectory($targetPath);

		$content = $this->prepare($stubPath, $args);

		$this->files->put($targetPath, $content);
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

	public function getPath()
	{
		return $this->path;
	}

	/**
	 * Ensure the directory exists or create it.
	 *
	 * @param  string  $path
	 * @return void
	 */
	protected function ensureDirectory($path)
	{
		$dir = array_get(pathinfo($path), 'dirname');

		if ( ! $this->files->isDirectory($dir))
		{
			$this->files->makeDirectory($dir, 0777, true);
		}
	}

	/**
	 * Dump autoloads.
	 *
	 * @return void
	 */
	protected function autoloads()
	{
		app('composer')->setWorkingPath($this->path);
		app('composer')->dumpOptimized();
	}

}
