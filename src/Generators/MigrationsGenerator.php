<?php

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
 * @version    3.0.9
 * @author     Cartalyst LLC
 * @license    Cartalyst PSL
 * @copyright  (c) 2011-2019, Cartalyst LLC
 * @link       http://cartalyst.com
 */

namespace Cartalyst\Workshop\Generators;

use LogicException;
use Illuminate\Support\Str;

class MigrationsGenerator extends AbstractGenerator
{
    /**
     * Migrations table.
     *
     * @var string
     */
    protected $table;

    /**
     * Migration's path.
     *
     * @var string
     */
    protected $migrationPath;

    /**
     * Migration's class.
     *
     * @var string
     */
    protected $migrationClass;

    /**
     * Seeder's class.
     *
     * @var string
     */
    protected $seederClass;

    /**
     * Migration's columns.
     *
     * @var array
     */
    protected $columns = [];

    /**
     * Increments column.
     *
     * @var bool
     */
    protected $increments;

    /**
     * Timestamp columns.
     *
     * @var bool
     */
    protected $timestamps;

    /**
     * Creates a new migration.
     *
     * @param  string  $table
     * @param  array  $columns
     * @param  bool  $increments
     * @param  bool  $timestamps
     * @return this
     */
    public function create($table, $columns = [], $increments = true, $timestamps = true)
    {
        $table = $this->sanitize($table, '/[^a-zA-Z0-9_-]/');

        $this->table      = Str::studly($table);
        $this->columns    = $columns;
        $this->increments = $increments;
        $this->timestamps = $timestamps;

        $mode     = 'Create';
        $stubPath = 'migration.stub';

        if (! $columns) {
            $mode     = 'Alter';
            $stubPath = 'migration-table.stub';
        }

        $this->migrationClass = $mode.$this->table.'Table';

        $this->ensureClassDoesNotExist($this->migrationClass);

        $columns = $this->prepareColumns($columns, $increments, $timestamps);

        $stub = $this->getStub($stubPath);

        $content = $this->prepare($stub, [
            'class_name' => $this->migrationClass,
            'table'      => Str::lower($table),
            'columns'    => $columns,
        ]);

        $migrationName = date('Y_m_d_His').'_'.Str::snake($this->migrationClass);

        $fileName = $migrationName.'.php';

        $dir = $this->path.'/resources/database/migrations/';

        $this->ensureExtension($dir);

        $filePath = $dir.$fileName;

        $this->migrationPath = $dir;

        $this->files->put($filePath, $content);

        return $this;
    }

    /**
     * Creates the seeder and updates the
     * seeders array on extension.php.
     *
     * @param  int  $records
     * @param  string  $table
     * @return this
     */
    public function seeder($records = 1, $table = null)
    {
        $table = $this->sanitize($table, '/[^a-zA-Z0-9_-]/');

        $namespace = $this->extension->studlyVendor.'\\'.$this->extension->studlyName.'\\Database\\Seeds';

        $table = $table ?: $this->table;

        $seederClass = Str::studly($table.'TableSeeder');

        $this->seederClass = $namespace.'\\'.$seederClass;

        $this->ensureClassDoesNotExist($this->seederClass);

        $stub    = $this->getStub('seeder.stub');
        $columns = $this->prepareSeederColumns($this->columns);

        $content = $this->prepare($stub, [
            'class_name' => $seederClass,
            'namespace'  => 'namespace '.$namespace.';',
            'records'    => $records,
            'table'      => Str::lower($table),
            'columns'    => $columns,
        ]);

        $dir = $this->path.'/resources/database/seeds/';

        $this->ensureExtension($dir);

        $filePath = $dir.$seederClass.'.php';

        $this->files->put($filePath, $content);

        // Add the new seeder to the seed runner
        $stub = $this->getStub('database_seeder.stub');

        $content = $this->prepare($stub, [
            'namespace' => 'namespace '.$namespace.';',
            'seeder'    => '$this->call(\''.$this->seederClass.'\');',
        ]);

        $filePath = $dir.'DatabaseSeeder.php';

        if (! $this->files->exists($filePath)) {
            $this->files->put($filePath, $content);
        } else {
            $content = $this->files->get($filePath);

            preg_match_all('/\$this->call.*?\n;?/s', $content, $matches);

            $seeders = head($matches);

            $seeders[] = '$this->call(\''.$this->seederClass.'\');';

            $seeders = implode($seeders, "\t\t");

            $stub = $this->getStub('database_seeder.stub');

            $content = $this->prepare($stub, [
                'namespace' => 'namespace '.$namespace.';',
                'seeder'    => $seeders,
            ]);

            $filePath = $dir.'DatabaseSeeder.php';

            $this->files->put($filePath, $content);
        }

        return $this;
    }

    /**
     * Returns the migration path.
     *
     * @return string
     */
    public function getMigrationPath()
    {
        return $this->migrationPath;
    }

    /**
     * Returns the migration class.
     *
     * @return string
     */
    public function getMigrationClass()
    {
        return $this->migrationClass;
    }

    /**
     * Returns the seeder class.
     *
     * @return string
     */
    public function getSeederClass()
    {
        return $this->seederClass;
    }

    /**
     * Prepares the seeder columns.
     *
     * @param  array  $columns
     * @return string
     */
    protected function prepareSeederColumns($columns)
    {
        if (! $columns) {
            return;
        }

        $cols = [];

        foreach ($columns as $name => $type) {
            $name = $this->sanitize($name, '/[^a-zA-Z0-9_-]/');

            switch ($type) {
                case 'tinyInteger':
                case 'boolean':

                    $cols[] = "'$name' => ".'rand(0, 1)'.',';
                    break;

                case 'text':
                case 'mediumText':
                case 'longText':

                    $cols[] = "'$name' => ".'$faker->text()'.',';
                    break;

                case 'float':
                case 'double':
                case 'decimal':

                    $cols[] = "'$name' => ".'$faker->randomFloat()'.',';
                    break;

                case 'integer':
                case 'smallInteger':
                case 'mediumInteger':
                case 'bigInteger':

                    $cols[] = "'$name' => ".'$faker->randomDigit()'.',';
                    break;

                case 'dateTime':

                    $cols[] = "'$name' => ".'$faker->dateTime()'.',';
                    break;

                case 'time':

                    $cols[] = "'$name' => ".'$faker->time()'.',';
                    break;

                default:
                    $cols[] = "'$name' => ".'$faker->sentence()'.',';
                    break;
            }
        }

        if ($this->timestamps) {
            $cols[] = "'created_at' => ".'$faker->dateTime()'.',';
            $cols[] = "'updated_at' => ".'$faker->dateTime()'.',';
        }

        return implode("\n\t\t\t\t", $cols);
    }

    /**
     * Prepares the migration columns.
     *
     * @param  array  $columns
     * @param  bool  $increments
     * @param  bool  $timestamps
     * @return string
     */
    protected function prepareColumns($columns, $increments, $timestamps)
    {
        if (! $columns) {
            return;
        }

        $cols     = [];
        $nullable = '';
        $default  = '';

        if ($increments) {
            $cols[] = '$table->'."increments('id');";
        }

        foreach ($columns as $name => $type) {
            $name = $this->sanitize($name, '/[^a-zA-Z0-9_-]/');

            if (strpos($type, 'default') !== false) {
                $parts = explode('|', $type);

                foreach ($parts as $part) {
                    if (strpos($part, ':') !== false) {
                        $default = last(explode(':', $part));

                        $default = "->default('$default')";
                    }
                }
            } else {
                $default = '';
            }

            if (strpos($type, 'nullable') !== false) {
                $nullable = '->nullable()';
            } else {
                $nullable = '';
            }

            if (strpos($type, 'unsigned') !== false) {
                $unsigned = '->unsigned()';
            } else {
                $unsigned = '';
            }

            if (strpos($type, 'unique') !== false) {
                $unique = '->unique()';
            } else {
                $unique = '';
            }

            $type = head(explode('|', $type));

            $cols[] = '$table->'.$type."('$name'){$nullable}{$default}{$unsigned}{$unique};";
        }

        if ($timestamps) {
            $cols[] = '$table->'.'timestamps();';
        }

        return implode("\n\t\t\t", $cols);
    }

    /**
     * Ensures the extension exists.
     *
     * @param  string  $dir
     * @return void
     * @throws \LogicException
     */
    protected function ensureExtension($dir)
    {
        if (! $this->files->isDirectory($dir)) {
            throw new LogicException('Extension does not exist.');
        }
    }

    /**
     * Ensures a class does not exist.
     *
     * @param  string  $class
     * @return void
     * @throws \LogicException
     */
    protected function ensureClassDoesNotExist($class)
    {
        if (class_exists($class)) {
            throw new LogicException('This class already exists.');
        }
    }
}
