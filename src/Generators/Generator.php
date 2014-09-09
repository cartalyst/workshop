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
use Illuminate\Support\Str;

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
	protected static $stubsPath;

	/**
	 * Stubs fallback path.
	 *
	 * @var string
	 */
	protected $stubsFallback;

	/**
	 * Constructor.
	 *
	 * @param  \Cartalyst\Workshop\Extension  $extension
	 * @param  \Illuminate\Filesystem\Filesystem  $files
	 * @return void
	 */
	public function __construct($extension, $files)
	{
		if ($extension instanceof Extension)
		{
			$this->extension = $extension;
		}
		else
		{
			$this->extension = new Extension($extension);
		}

		$this->files = $files;

		$this->path = __DIR__.'/../../../../../workbench/'.$this->extension->getFullName();

		if ( ! $this->files->isDirectory($this->path))
		{
			$this->path = str_replace('workbench', 'extensions', $this->path);
		}

		$this->stubsFallback = __DIR__.'/..'.str_replace($this->path, '/stubs/', $this->path);
	}

	/**
	 * Sets the stubs directory.
	 *
	 * @param  string  $dir
	 * @return void
	 */
	public static function setStubsDir($dir)
	{
		static::$stubsPath = $dir;
	}

	/**
	 * Returns the stubs directory.
	 *
	 * @return string
	 */
	public static function getStubsDir()
	{
		return static::$stubsPath;
	}

	/**
	 * Returns the stub file path.
	 *
	 * @param  string  $path
	 * @return string
	 */
	public function getStub($path)
	{
		if ($this->files->exists(static::$stubsPath.'/'.$path))
		{
			return static::$stubsPath.$path;
		}

		return $this->stubsFallback.$path;
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
	 * Ensure the directory exists or create it.
	 *
	 * @param  string  $path
	 * @return void
	 */
	protected function ensureDirectory($path)
	{
		$path = str_replace('/', DIRECTORY_SEPARATOR, $path);

		if ( ! $this->files->isDirectory($path))
		{
			$this->files->makeDirectory($path, 0777, true);
		}
	}

	/**
	 * Wraps an array for text output.
	 *
	 * @param  array  $array
	 * @param  string  $indentation
	 * @return string
	 */
	protected function wrapArray($array, $indentation = null)
	{
		$self = $this;

		$indentation = $indentation . "\t";

		array_walk($array, function($value, $key) use ($indentation, &$text, $self)
		{
			if (is_array($value))
			{
				if ( ! is_numeric($key))
				{
					$text .= $indentation."'".$key."' => [\n\t";
				}
				else
				{
					$text .= $indentation."[\n\t";
				}

				$text .= $indentation.$self->wrapArray($value, $indentation) . "\n";

				$text .= $indentation."],\n";
			}

			if (is_string($value) && is_string($key))
			{
				$text .= $indentation."'".$key."' => '".$value."',\n";
			}
		});

		return trim($text);
	}

}
