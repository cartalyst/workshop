<?php namespace Cartalyst\Workshop;
/**
 * Part of the Workshop package.
 *
 * NOTICE OF LICENSE
 *
 * Licensed under the 3-clause BSD License.
 *
 * This source file is subject to the 3-clause BSD License that is
 * bundled with this package in the LICENSE file.  It is also available at
 * the following URL: http://www.opensource.org/licenses/BSD-3-Clause
 *
 * @package    Workshop
 * @version    2.0.0
 * @author     Cartalyst LLC
 * @license    BSD License (3-clause)
 * @copyright  (c) 2011 - 2013, Cartalyst LLC
 * @link       http://cartalyst.com
 */

use Illuminate\Workbench\PackageCreator;

class ExtensionCreator extends PackageCreator {

	protected $components = array(
		'admin',
		'frontend',
		'config',
	);

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

	protected $adminBlocks = array(
		'ControllerFile',
		'PermissionsFile',
		'ThemeFile',
	);

	protected $languageStubs = array(
		'permissions',
	);

	/**
	 * Create a new package stub.
	 *
	 * @param  \Illuminate\Workbench\Package  $repository
	 * @param  string  $path
	 * @param  array    $components
	 * @return string
	 */
	public function create(Package $repository, $path, $components = true)
	{
		if ( ! $repository instanceof Repository)
		{
			throw new \InvalidArgumentException("Package must be a valid Extension repository for Workshop.");
		}

		if ($components === true)
		{
			$components = $this->components;
		}
		else
		{
			foreach ($components as $component)
			{
				if ( ! in_array($component, $this->components))
				{
					throw new \InvalidArgumentException("Component [$component] is not a valid component of an Extension to create.");
				}
			}
		}

		$directory = $this->createDirectory($repository, $path);

		foreach ($this->blocks as $block)
		{
			$this->{"write{$block}"}($repository, $directory, $plain);
		}

		foreach ($components as $component)
		{
			$this->{'create'.studly_case($component).'Component'}($p)
		}
	}

	protected function writeLanguageFiles(Repository $repository, $directory)
	{
		$this->files->makeDirectory($langDirectory = $directory.'/lang/en', 0777, true);

		foreach ($this->getLanguageStubs() as $stub)
		{
			list($name, $stub) = $stub;

			$stub = $this->formatPackageStub($repository, $stub);

			$this->files->put("$langDirectory/$name.php", $stub);
		}
	}

	protected function getLanguageStubs()
	{
		$stubs = array();

		foreach (array('permissions') as $name)
		{
			$stubs[] = array($name, $this->files->get(__DIR__."/stubs/lang/$name.stub"));
		}

		return $stubs;
	}

	protected function writeExtensionFile(Repository $repository, $directory)
	{
		$stub = $this->getExtensionStub();

		$stub = $this->formatPackageStub($repository, $stub);

		$this->files->put($directory.'/extension.php', $stub);
	}

	protected function getExtensionStub()
	{
		return $this->files->get(__DIR__.'/stubs/extension.stub');
	}

}
