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

class MigrationsGenerator extends Generator {

	protected $table;
	protected $columns;
	protected $increments;
	protected $timestamps;

	public function create($table, $columns = null, $increments = true, $timestamps = true)
	{
		$this->increments = $increments;
		$this->timestamps = $timestamps;
		$this->table = Str::studly($table);
		$this->columns = $columns;
		$migrationDate  = date('Y_m_d_His');
		$migrationClass = 'Create'.$this->table.'Table';
		$migrationName  = $migrationDate.'_'.snake_case($migrationClass);

		$stub = $this->basePath.'migration.stub';

		$columns = $this->prepareColumns($columns, $increments, $timestamps);

		$content = $this->prepare($stub, 'migration.stub', [
			'class_name' => $migrationClass,
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

		$this->files->put($filePath, $content);

		$ext = $this->path.'/extension.php';

		$extensionContent = $this->files->get($ext);

		$dgSourceStub = $this->basePath.'datagrid-route.stub';

		$dgSource = $this->files->get($dgSourceStub);

		$dgCols = array_keys($this->columns);

		$dgCols = array_map(function($col)
		{
			return '\''.$col.'\'';
		}, $dgCols);

		$dgCols = implode(",\n\t\t", $dgCols).',';

		$modelName = Str::singular($this->table);

		$model = $this->extension->vendor.'\\'.$this->extension->name.'\\'.'Models\\'.$modelName;

		$dgSource = str_replace(["{{model}}", "{{columns}}", "\n"], [$model, $dgCols, "\n\t\t\t"], $dgSource);

		$this->files->put($ext, $extensionContent);

		$extensionContent = str_replace("{{datagrid_source}}", $dgSource, $extensionContent);

		$this->files->put($ext, $extensionContent);

		return $this;
	}

	public function seeder($records = 1)
	{
		$seederClass = $this->table.'TableSeeder';

		$stub = $this->basePath.'seeder.stub';

		$loop = 'foreach(range(1, '.$records.') as $index)'."\n\t\t{";

		$endLoop = "}";

		$columns = $this->prepareSeederColumns($this->columns);

		$modelName = Str::singular($this->table);

		$model = $this->extension->vendor.'\\'.$this->extension->name.'\\'.'Models\\'.$modelName;

		$namespace = $this->extension->vendor.'\\'.$this->extension->name.'\\Database\\Seeds';

		$content = $this->prepare($stub, 'seeder.stub', [
			'class_name' => $seederClass,
			'namespace'  => 'namespace '.$namespace.';',
			'class'      => $modelName,
			'use'        => "\nuse ".$model.';',
			'loop'       => $loop,
			'end_loop'   => $endLoop,
			'table'      => Str::lower($this->table),
			'columns'    => $columns,
		]);

		$fileName = $seederClass.'.php';

		$dir = $this->path.'/database/seeds/';

		if ( ! $this->files->isDirectory($dir))
		{
			$this->files->makeDirectory($dir, 0777, true);
		}

		$filePath = $dir . $fileName;

		$this->files->put($filePath, $content);

		$ext = $this->path.'/extension.php';

		$extensionContent = $this->files->get($ext);

		$dgCols = array_keys($this->columns);

		if ($this->increments)
		{
			array_unshift($dgCols, "id");
		}

		if ($this->increments)
		{
			array_push($dgCols, "created_at");
		}

		$dgCols = array_map(function($col)
		{
			return '\''.$col.'\'';
		}, $dgCols);

		$dgCols = implode(",\n\t\t", $dgCols).',';

		$dgSourceStub = $this->basePath.'datagrid-route.stub';

		$dgSource = $this->files->get($dgSourceStub);

		$dgSource = str_replace(["{{model}}", "{{columns}}", "\n"], [$model, $dgCols, "\n\t\t\t"], $dgSource);

		$this->files->put($ext, $extensionContent);

		$extensionContent = str_replace("{{datagrid_source}}", $dgSource, $extensionContent);

		$this->files->put($ext, $extensionContent);

		$curS = $this->files->getRequire($ext);

		$currentSeeds = isset($curS['seeds']) ? $curS['seeds'] : [];

		$seeds = '';

		foreach ($currentSeeds as $s)
		{
			$seeds .= "'$s',\n\t\t";
		}

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
	}

	protected function datagrid()
	{

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
			if (strpos($type, 'default'))
			{
				$default = last(explode(':', last(explode('|', $type))));

				$default = "->default($default)";
			}

			if (strpos($type, '|'))
			{
				$type = head(explode('|', $type));

				$nullable = '->nullable()';
			}

			$cols[] = '$table->'.$type."('$name'){$nullable}{$default};";
		}

		if ($timestamps)
		{
			$cols[] = '$table->'."timestamps();";
		}

		return implode("\n\t\t\t", $cols);
	}

	/**
	 * {@inheritDoc}
	 */
	public function prepare($path, $file, $args = [])
	{
		$content = parent::prepare($path, $file, $args);

		$content = str_replace('{{table}}', 'posts', $content);

		return $content;
	}

}
