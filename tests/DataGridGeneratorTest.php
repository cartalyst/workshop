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
use Cartalyst\Workshop\Generators\DataGridGenerator;

class DataGridGeneratorTest extends PHPUnit_Framework_TestCase
{
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

        $html = m::mock('Illuminate\Html\HtmlBuilder');
        $form = m::mock('Illuminate\Html\FormBuilder');

        $generator = new DataGridGenerator('foo/bar', $files, $html, $form);

        $this->assertInstanceOf('Cartalyst\Workshop\Generators\AbstractGenerator', $generator);
    }

    /** @test */
    public function it_can_create_data_grids()
    {
        $files = m::mock('Illuminate\Filesystem\Filesystem');

        $files->shouldReceive('isDirectory')->times(10)->andReturn(true);
        $files->shouldReceive('exists')->times(10)->andReturn(true);
        $files->shouldReceive('getRequire')->once()->andReturn(['general' => []]);
        $files->shouldReceive('get')->times(9);
        $files->shouldReceive('put')->times(10);

        $html = m::mock('Illuminate\Html\HtmlBuilder');
        $html->shouldReceive('decode');
        $html->shouldReceive('link');

        $form = m::mock('Illuminate\Html\FormBuilder');
        $form->shouldReceive('checkbox')->times(5);

        $generator = new DataGridGenerator('foo/bar', $files, $html, $form);

        $generator->create('foo', 'admin', 'default', 'index', [
            [
                'field' => 'foo',
            ],
            [
                'field' => 'bar'
            ],
        ]);
    }
}
