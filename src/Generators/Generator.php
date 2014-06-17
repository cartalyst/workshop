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

	protected $extension;
	protected $files;
	protected $html;
	protected $form;
	protected $path;
	protected $basePath;

	public function __construct(Extension $extension, $files, $html = null, $form = null)
	{
		$this->extension = $extension;
		$this->files = $files;
		$this->html = $html;
		$this->form = $form;

		$this->path = base_path().'/workbench/'.$this->extension->getFullName();

		$this->basePath = __DIR__.'/..'.str_replace($this->path, '/stubs/', $this->path);
	}

	/**
	 * Create directories hierarchy.
	 *
	 * @param  string  $dir
	 * @param  string  $files
	 * @return void
	 */
	protected function process($path = null, $dir = [], $args = [])
	{
		$path = $path ?: $this->path;

		$dir = $dir ?: $this->blocks;

		if (is_array($dir))
		{
			foreach ($dir as $key => $d)
			{
				$subdir = $path;

				if ( ! is_numeric($key) && is_array($d) && strpos($key, '.stub') === false)
				{
					$subdir = $path.'/'.$key;
				}

				if ( ! is_array($d) && strpos($d, '.stub') !== false)
				{
					$this->processFile($path, $d, $key, $args);

					continue;
				}

				$this->process($subdir, $d, $args);
			}

			return;
		}

		$this->processFile($path, $dir, null, $args);
	}

	/**
	 * Process a file.
	 *
	 * @param  string  $directory
	 * @param  string  $file
	 * @return void
	 */
	protected function processFile($directory, $file, $overriddenFile = null, $args = [])
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

		$content = $this->prepare($stubPath, $file, $args);

		$this->files->put($targetPath, $content);
	}

	/**
	 * Ensure the directory exists or create it.
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

	public function prepare($path, $file, $args = [])
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

}
