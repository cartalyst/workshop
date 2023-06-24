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
use Cartalyst\Workshop\Generators\DataGridGenerator;

class DataGridGeneratorTest extends TestCase
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

        $html = m::mock('Collective\Html\HtmlBuilder');
        $form = m::mock('Collective\Html\FormBuilder');

        $generator = new DataGridGenerator('foo/bar', $files, $html, $form);

        $this->assertInstanceOf('Cartalyst\Workshop\Generators\AbstractGenerator', $generator);
    }

    /** @test */
    public function it_can_create_data_grids()
    {
        $files = m::mock('Illuminate\Filesystem\Filesystem');

        $files->shouldReceive('isDirectory')->times(8)->andReturn(true);
        $files->shouldReceive('exists')->times(9)->andReturn(true);
        $files->shouldReceive('getRequire')->once()->andReturn(['general' => []]);
        $files->shouldReceive('get')->times(8);
        $files->shouldReceive('put')->times(9);

        $html = m::mock('Collective\Html\HtmlBuilder');
        $html->shouldReceive('decode');
        $html->shouldReceive('link');

        $form = m::mock('Collective\Html\FormBuilder');
        $form->shouldReceive('checkbox')->times(4);

        $generator = new DataGridGenerator('foo/bar', $files, $html, $form);

        $generator->create('foo', 'admin', 'default', 'index', [
            [
                'field' => 'foo',
            ],
            [
                'field' => 'bar',
            ],
        ]);
    }
}
