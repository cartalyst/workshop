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
 * @version    1.0.0
 * @author     Cartalyst LLC
 * @license    Cartalyst PSL
 * @copyright  (c) 2011-2015, Cartalyst LLC
 * @link       http://cartalyst.com
 */

namespace Cartalyst\Workshop\Tests;

use Mockery as m;
use PHPUnit_Framework_TestCase;
use Cartalyst\Workshop\Generators\MigrationsGenerator;

class MigrationsGeneratorTest extends PHPUnit_Framework_TestCase
{
    /**
     * Generator instance.
     *
     * @var \Cartalyst\Workshop\Generators\MigrationsGenerator
     */
    protected $generator;

    /**
     * Filesystem mock.
     *
     * @var \Illuminate\Filesystem\Filesystem
     */
    protected $files;

    /**
     * Close mockery.
     *
     * @return void
     */
    public function tearDown()
    {
        m::close();
    }

    /**
     * Setup resources and dependencies.
     *
     * @return void
     */
    public function prepare()
    {
        $files = m::mock('Illuminate\Filesystem\Filesystem');

        $files->shouldReceive('isDirectory')->atLeast()->once()->andReturn(true);
        $files->shouldReceive('get')->atLeast()->once()->andReturn('{{studly_vendor}}{{new_arg}}');
        $files->shouldReceive('put')->atLeast()->once();

        $generator = m::mock('Cartalyst\Workshop\Generators\MigrationsGenerator[getStub]', ['foo/bar', $files]);

        $this->files     = $files;
        $this->generator = $generator;
    }

    /** @test */
    public function it_can_generate_migrations()
    {
        $this->prepare();

        $this->generator->shouldReceive('getStub')->once()->with('migration-table.stub');

        $this->generator->create('foo');

        $this->assertEquals('AlterFooTable', $this->generator->getMigrationClass());
    }

    /** @test */
    public function it_can_generate_seeders()
    {
        $this->prepare();

        $this->files->shouldReceive('exists')->once()->andReturn(true);
        $this->files->shouldReceive('getRequire')->once()->andReturn([]);

        $this->generator->shouldReceive('getStub')->once()->with('seeder.stub');

        $this->generator->seeder();
    }

    /** @test */
    public function it_can_get_migrations_path_and_classes()
    {
        $this->prepare();

        $this->files->shouldReceive('exists')->once()->andReturn(true);
        $this->files->shouldReceive('getRequire')->once()->andReturn([]);

        $this->generator->shouldReceive('getStub')->once()->with('migration.stub');
        $this->generator->shouldReceive('getStub')->once()->with('seeder.stub');

        $this->generator->create('bar', [
            'name' => 'string',
        ]);

        $this->generator->seeder();

        $this->assertContains('foo/bar/database/migrations', $this->generator->getMigrationPath());
        $this->assertEquals('CreateBarTable', $this->generator->getMigrationClass());
        $this->assertEquals('Foo\Bar\Database\Seeds\BarTableSeeder', $this->generator->getSeederClass());
    }

    /** @test */
    public function it_can_create_seeder_fields()
    {
        $this->prepare();

        $this->files->shouldReceive('exists')->atLeast()->once()->andReturn(true);
        $this->files->shouldReceive('getRequire')->atLeast()->once()->andReturn(['seeds' => ['Seeder']]);

        $this->generator->shouldReceive('getStub')->atLeast()->once()->with('migration.stub');
        $this->generator->shouldReceive('getStub')->atLeast()->once()->with('seeder.stub');

        $this->generator->create('baz', [
            'name' => 'boolean',
        ]);

        $this->generator->seeder();
    }

    /** @test */
    public function it_can_make_seeder_columns_nullable_default_or_unsigned()
    {
        $this->prepare();

        $this->files->shouldReceive('exists')->once()->andReturn(true);
        $this->files->shouldReceive('getRequire')->once()->andReturn([]);

        $this->generator->shouldReceive('getStub')->once()->with('migration.stub');
        $this->generator->shouldReceive('getStub')->once()->with('seeder.stub');

        $this->generator->create('test', [
            'name'  => 'string|nullable|default:test',
            'age'   => 'integer|nullable|unsigned',
            'email' => 'test|unique',
        ]);

        $this->generator->seeder();
    }

    /**
     * @test
     * @expectedException \LogicException
     */
    public function it_throws_a_logic_exception_if_seeder_class_already_exists()
    {
        require_once __DIR__.'/stubs/seeder.php';

        $files = m::mock('Illuminate\Filesystem\Filesystem');

        $files->shouldReceive('isDirectory')->atLeast()->once()->andReturn(true);
        $files->shouldReceive('exists')->once()->andReturn(true);
        $files->shouldReceive('get')->atLeast()->once()->andReturn('{{studly_vendor}}{{new_arg}}');
        $files->shouldReceive('put')->atLeast()->once();

        $generator = new MigrationsGenerator('foo/bar', $files);

        $generator->create('foo', [
            'name' => 'boolean',
        ]);

        $generator->seeder();
    }

    /**
     * @test
     * @expectedException \LogicException
     */
    public function it_throws_a_logic_exception_if_the_extension_does_not_exist()
    {
        $files = m::mock('Illuminate\Filesystem\Filesystem');

        $files->shouldReceive('isDirectory')->once()->andReturn(true);
        $files->shouldReceive('isDirectory')->twice()->andReturn(false);
        $files->shouldReceive('exists')->once()->andReturn(true);
        $files->shouldReceive('get')->atLeast()->once();

        $generator = new MigrationsGenerator('foo/bar', $files);

        $generator->create('foo', [
            'name' => 'boolean',
        ]);
        $generator->seeder();
    }
}
