<?php namespace Cartalyst\Workshop\Tests;
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

use Mockery as m;
use PHPUnit_Framework_TestCase;
use Cartalyst\Workshop\Generators\Generator;

class GeneratorTest extends PHPUnit_Framework_TestCase {

	/**
	 * Close mockery.
	 *
	 * @return void
	 */
	public function tearDown()
	{
		m::close();
	}

	/** @test */
	public function it_can_be_instantiated()
	{
		$files = m::mock('Illuminate\Filesystem\Filesystem');
		$files->shouldReceive('isDirectory')->once()->andReturn(true);

		$generator = new GeneratorStub('foo/bar', $files);

		$this->assertInstanceOf('Cartalyst\Workshop\Generators\Generator', $generator);
	}

	/**
	 * @test
	 * @runInSeparateProcess
	*/
	public function it_can_set_and_retrieve_the_stubs_dir()
	{
		$files = m::mock('Illuminate\Filesystem\Filesystem');

		$files->shouldReceive('isDirectory')->once()->andReturn(true);

		$generator = new GeneratorStub('foo/bar', $files);

		$generator->setStubsDir('test');

		$this->assertEquals('test', $generator->getStubsDir());
	}

	/** @test */
	public function it_can_prepare_content_from_a_stub_file()
	{
		$files = m::mock('Illuminate\Filesystem\Filesystem');

		$files->shouldReceive('isDirectory')->once()->andReturn(true);
		$files->shouldReceive('get')->once()->with('foo.stub')->andReturn('{{studly_vendor}}{{new_arg}}');

		$generator = new GeneratorStub('foo/bar', $files);

		$this->assertEquals('Foobar', $generator->prepare('foo.stub', ['new_arg' => 'bar']));
	}

}

class GeneratorStub extends Generator {

}
