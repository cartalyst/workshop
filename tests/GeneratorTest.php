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
 * @version    8.0.0
 * @author     Cartalyst LLC
 * @license    Cartalyst PSL
 * @copyright  (c) 2011-2023, Cartalyst LLC
 * @link       https://cartalyst.com
 */

namespace Cartalyst\Workshop\Tests;

use Mockery as m;
use PHPUnit\Framework\TestCase;
use Cartalyst\Workshop\Generators\AbstractGenerator;

class GeneratorTest extends TestCase
{
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

    /** @test */
    public function it_can_be_instantiated()
    {
        $files = m::mock('Illuminate\Filesystem\Filesystem');

        $generator = new GeneratorStub('foo/bar', $files);

        $this->assertInstanceOf('Cartalyst\Workshop\Generators\AbstractGenerator', $generator);
    }

    /**
     * @test
     * @runInSeparateProcess
     */
    public function it_can_set_and_retrieve_the_stubs_dir()
    {
        $files = m::mock('Illuminate\Filesystem\Filesystem');

        $generator = new GeneratorStub('foo/bar', $files);

        $generator->setStubsDir('test');

        $this->assertSame('test', $generator->getStubsDir());
    }

    /** @test */
    public function it_can_prepare_content_from_a_stub_file()
    {
        $files = m::mock('Illuminate\Filesystem\Filesystem');

        $files->shouldReceive('get')->once()->with('foo.stub')->andReturn('{{studly_vendor}}{{new_arg}}');

        $generator = new GeneratorStub('foo/bar', $files);

        $this->assertSame('Foobar', $generator->prepare('foo.stub', ['new_arg' => 'bar']));
    }

    /**
     * @test
     */
    public function it_throws_an_exception_if_the_extension_does_not_exist()
    {
        $this->expectException('LogicException');

        $files = m::mock('Illuminate\Filesystem\Filesystem');

        $generator = new GeneratorStub('foo/bar', $files);
        $generator->check();
    }

    /**
     * @test
     */
    public function it_can_ensure_a_directory_exists()
    {
        $files = m::mock('Illuminate\Filesystem\Filesystem');

        $files->shouldReceive('isDirectory')->once()->andReturn(false);
        $files->shouldReceive('makeDirectory')->with('directory', 0777, true);

        $generator = new GeneratorStub('foo/bar', $files);
        $generator->ensure();
    }
}

class GeneratorStub extends AbstractGenerator
{
    public function check()
    {
        return $this->getExtensionPhpPath();
    }

    public function ensure()
    {
        $this->ensureDirectory('directory');
    }
}
