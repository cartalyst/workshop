<?php namespace Cartalyst\Workshop;
/**
 * Part of the Themes package.
 *
 * NOTICE OF LICENSE
 *
 * Licensed under the 3-clause BSD License.
 *
 * This source file is subject to the 3-clause BSD License that is
 * bundled with this package in the LICENSE file.  It is also available at
 * the following URL: http://www.opensource.org/licenses/BSD-3-Clause
 *
 * @package    Themes
 * @version    2.0.0
 * @author     Cartalyst LLC
 * @license    BSD License (3-clause)
 * @copyright  (c) 2011 - 2013, Cartalyst LLC
 * @link       http://cartalyst.com
 */

use Illuminate\Workbench\PackageCreator;

class ExtensionCreator extends PackageCreator {

	/**
	 * The building blocks of the package.
	 *
	 * @param  array
	 */
	protected $blocks = array(
		'SupportFiles',
		'SupportDirectories',
		'PublicDirectory',
		'TestDirectory',
		'LanguageFiles',
		'ExtensionFile',
	);

	protected $languageStubs = array(
		'permissions',
	);

	/**
	 * Create a new package stub.
	 *
	 * @param  \Illuminate\Workbench\Package  $package
	 * @param  string  $path
	 * @param  bool    $plain
	 * @return string
	 */
	public function create(Package $package, $path, $plain = true)
	{
		if ( ! $package instanceof Repository)
		{
			throw new \InvalidArgumentException("Package must be a valid Extension repository for Workshop.");
		}

		return parent::create($package, $path, false);
	}

	protected function writeLanguageFiles(Package $package, $directory)
	{
		$this->files->makeDirectory($langDirectory = $directory.'/lang/en', 0777, true);

		foreach ($this->getLanguageStubs() as $stub)
		{
			list($name, $stub) = $stub;

			$stub = $this->formatPackageStub($package, $stub);

			$this->files->put("$langDirectory/$name.php", $stub);
		}
	}

	protected function getLanguageStubs()
	{
		$stubs = array();

		foreach ($this->languageStubs as $name)
		{
			$stubs[] = array($name, $this->files->get(__DIR__."/stubs/lang/$name.stub"));
		}

		return $stubs;
	}

	protected function writeExtensionFile(Package $package, $directory)
	{
		$stub = $this->getExtensionStub();

		$stub = $this->formatPackageStub($package, $stub);

		$this->files->put($directory.'/extension.php', $stub);
	}

	protected function getExtensionStub()
	{
		return $this->files->get(__DIR__.'/stubs/extension.stub');
	}

}
