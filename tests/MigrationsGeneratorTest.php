<?php

/*
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
 * @version    4.0.0
 * @author     Cartalyst LLC
 * @license    Cartalyst PSL
 * @copyright  (c) 2011-2019, Cartalyst LLC
 * @link       https://cartalyst.com
 */

namespace Cartalyst\Workshop\Tests;

use Mockery as m;
use PHPUnit\Framework\TestCase;
use Cartalyst\Workshop\Generators\MigrationsGenerator;

class MigrationsGeneratorTest extends TestCase
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
    protected function tearDown(): void
    {
        $this->addToAssertionCount(1);

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

        $this->assertSame('AlterFooTable', $this->generator->getMigrationClass());
    }

    /** @test */
    public function it_can_generate_seeders()
    {
        $this->prepare();

        $this->files->shouldReceive('exists')->once()->andReturn(true);

        $this->generator->shouldReceive('getStub')->once()->with('seeder.stub');
        $this->generator->shouldReceive('getStub')->twice()->with('database_seeder.stub');

        $this->generator->seeder();
    }

    /** @test */
    public function it_can_get_migrations_path_and_classes()
    {
        $this->prepare();

        $this->files->shouldReceive('exists')->once()->andReturn(true);

        $this->generator->shouldReceive('getStub')->once()->with('migration.stub');
        $this->generator->shouldReceive('getStub')->once()->with('seeder.stub');
        $this->generator->shouldReceive('getStub')->twice()->with('database_seeder.stub');

        $this->generator->create('bar', [
            'name' => 'string',
        ]);

        $this->generator->seeder();

        $this->assertStringContainsString('foo/bar/resources/database/migrations', $this->generator->getMigrationPath());
        $this->assertSame('CreateBarTable', $this->generator->getMigrationClass());
        $this->assertSame('Foo\Bar\Database\Seeds\BarTableSeeder', $this->generator->getSeederClass());
    }

    /** @test */
    public function it_can_create_seeder_fields()
    {
        $this->prepare();

        $this->files->shouldReceive('exists')->atLeast()->once()->andReturn(true);

        $this->generator->shouldReceive('getStub')->atLeast()->once()->with('migration.stub');
        $this->generator->shouldReceive('getStub')->atLeast()->once()->with('seeder.stub');
        $this->generator->shouldReceive('getStub')->twice()->with('database_seeder.stub');

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

        $this->generator->shouldReceive('getStub')->once()->with('migration.stub');
        $this->generator->shouldReceive('getStub')->once()->with('seeder.stub');
        $this->generator->shouldReceive('getStub')->twice()->with('database_seeder.stub');

        $this->generator->create('test', [
            'name'  => 'string|nullable|default:test',
            'age'   => 'integer|nullable|unsigned',
            'email' => 'test|unique',
        ]);

        $this->generator->seeder();
    }

    /**
     * @test
     */
    public function it_throws_a_logic_exception_if_seeder_class_already_exists()
    {
        $this->expectException('LogicException');

        require_once __DIR__.'/stubs/seeder.php';

        $files = m::mock('Illuminate\Filesystem\Filesystem');

        $files->shouldReceive('exists')->once()->andReturn(true);
        $files->shouldReceive('get')->atLeast()->once()->andReturn('{{studly_vendor}}{{new_arg}}');

        $generator = new MigrationsGenerator('foo/bar', $files);

        $generator->create('foo', [
            'name' => 'boolean',
        ]);

        $generator->seeder();
    }

    /**
     * @test
     */
    public function it_throws_a_logic_exception_if_the_extension_does_not_exist()
    {
        $this->expectException('LogicException');

        $files = m::mock('Illuminate\Filesystem\Filesystem');

        $files->shouldReceive('exists')->once()->andReturn(true);
        $files->shouldReceive('get')->atLeast()->once();

        $generator = new MigrationsGenerator('foo/bar', $files);

        $generator->create('foo', [
            'name' => 'boolean',
        ]);
        $generator->seeder();
    }
}
