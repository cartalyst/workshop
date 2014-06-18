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

use Artisan;
use Cartalyst\Workshop\Extension;
use LogicException;
use Str;

class MigrationsGenerator extends Generator {

	protected $table;
	protected $migrationPath;
	protected $migrationClass;
	protected $seederClass;
	protected $columns;
	protected $increments;
	protected $timestamps;

	public function create($table, $columns = null, $increments = true, $timestamps = true)
	{
		$this->increments     = $increments;
		$this->timestamps     = $timestamps;
		$this->columns        = $columns;
		$this->table          = Str::studly($table);
		$this->migrationClass = 'Create'.$this->table.'Table';
		$migrationDate        = date('Y_m_d_His');
		$migrationName        = $migrationDate.'_'.snake_case($this->migrationClass);

		if (class_exists($this->migrationClass))
		{
			throw new LogicException('This migration already exists.');
		}

		$stub = $this->stubsPath.'migration.stub';

		$columns = $this->prepareColumns($columns, $increments, $timestamps);

		$content = $this->prepare($stub, [
			'class_name' => $this->migrationClass,
			'table'      => Str::lower($table),
			'columns'    => $columns,
		]);

		$fileName = $migrationName.'.php';

		$dir = $this->path.'/database/migrations/';

		if ( ! $this->files->isDirectory($dir))
		{
			$this->files->makeDirectory($dir, 0777, true);
		}

		$filePath = $dir . $fileName;

		$this->migrationPath = $dir;

		$this->files->put($filePath, $content);

		Artisan::call('dump-autoload');

		return $this;
	}

	public function seeder($records = 1)
	{
		$namespace = $this->extension->vendor.'\\'.$this->extension->name.'\\Database\\Seeds';

		$seederClass = $this->table.'TableSeeder';

		$this->seederClass = $namespace.'\\'.$seederClass;

		if (class_exists($this->seederClass))
		{
			throw new LogicException('This seeder already exists.');
		}

		$stub    = $this->stubsPath.'seeder.stub';
		$columns = $this->prepareSeederColumns($this->columns);

		$content = $this->prepare($stub, [
			'class_name' => $seederClass,
			'namespace'  => 'namespace '.$namespace.';',
			'records'    => $records,
			'table'      => Str::lower($this->table),
			'columns'    => $columns,
		]);

		$dir = $this->path.'/database/seeds/';

		$filePath = $dir.$seederClass.'.php';

		$this->ensureDirectory($filePath);

		$this->files->put($filePath, $content);

		// Add the new seeder to the extension
		$ext = $this->path.'/extension.php';

		$currentSeeds = $this->files->getRequire($ext);

		$currentSeeds = isset($currentSeeds['seeds']) ? $currentSeeds['seeds'] : [];

		$seeds = null;

		foreach ($currentSeeds as $s)
		{
			$seeds .= "'$s',\n\t\t";
		}

		$extensionContent = $this->files->get($ext);

		if ( ! in_array("{$namespace}\\{$seederClass}", $currentSeeds))
		{
			$seeds .= "'{$namespace}\\{$seederClass}',";

			$extensionContent = preg_replace(
				"/('seeds' => \[)(\s*.*?)],/s",
				"'seeds' => [\n\n\t\t{$seeds}\n\n\t],",
				$extensionContent
			);

			$this->files->put($ext, $extensionContent);
		}

		Artisan::call('dump-autoload');

		return $this;
	}

	public function getMigrationPath()
	{
		return $this->migrationPath;
	}

	public function getMigrationClass()
	{
		return $this->migrationClass;
	}

	public function getSeederClass()
	{
		return $this->seederClass;
	}

	protected function prepareSeederColumns($columns)
	{
		if ( ! $columns)
		{
			return;
		}

		$cols = [];

		foreach ($columns as $name => $type)
		{
			$cols[] = "'$name' => ".'$faker->sentence(5)'.",";
		}

		return implode("\n\t\t\t\t", $cols);
	}

	protected function prepareColumns($columns, $increments, $timestamps)
	{
		if ( ! $columns)
		{
			return;
		}

		$cols = [];
		$nullable = '';
		$default = '';

		if ($increments)
		{
			$cols[] = '$table->'."increments('id');";
		}

		foreach ($columns as $name => $type)
		{
			if (strpos($type, 'default') !== false)
			{
				$parts = explode('|', $type);

				foreach ($parts as $part)
				{
					if (strpos($part, ':') !== false)
					{
						$default = last(explode(':', $part));

						$default = "->default('$default')";
					}
				}
			}
			else
			{
				$default = '';
			}

			if (strpos($type, 'nullable') !== false)
			{
				$nullable = '->nullable()';
			}
			else
			{
				$nullable = '';
			}

			if (strpos($type, 'unsigned') !== false)
			{
				$unsigned = '->unsigned()';
			}
			else
			{
				$unsigned = '';
			}

			$type = head(explode('|', $type));

			$cols[] = '$table->'.$type."('$name'){$nullable}{$default}{$unsigned};";
		}

		if ($timestamps)
		{
			$cols[] = '$table->'."timestamps();";
		}

		return implode("\n\t\t\t", $cols);
	}

}
