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
use Cartalyst\Workshop\Generators\RepositoryGenerator;

class RepositoryGeneratorTest extends PHPUnit_Framework_TestCase {

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

		$generator = new RepositoryGenerator('foo/bar', $files);

		$this->assertInstanceOf('Cartalyst\Workshop\Generators\Generator', $generator);
	}

	/** @test */
	public function it_can_create_repositories()
	{
		$files = m::mock('Illuminate\Filesystem\Filesystem');

		$files->shouldReceive('isDirectory')->twice()->andReturn(true);
		$files->shouldReceive('exists')->twice()->andReturn(false);
		$files->shouldReceive('get')->twice()->andReturn('{{studly_vendor}}{{new_arg}}');
		$files->shouldReceive('put')->twice();

		$generator = new RepositoryGenerator('foo/bar', $files);

		$generator->create('foo');
	}

}
