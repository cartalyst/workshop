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
use Cartalyst\Workshop\Generators\FormGenerator;

class FormGeneratorTest extends PHPUnit_Framework_TestCase
{
    /**
     * Generator instance.
     *
     * @var \Cartalyst\Workshop\Generators\FormGenerator
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
    public function setUp()
    {
        $files = m::mock('Illuminate\Filesystem\Filesystem');
        $files->shouldReceive('isDirectory')->atLeast()->once()->andReturn(true);
        $files->shouldReceive('get')->atLeast()->once()->andReturn('{{studly_vendor}}{{new_arg}}');
        $files->shouldReceive('put')->atLeast()->once();

        $generator = m::mock('Cartalyst\Workshop\Generators\FormGenerator[getStub]', ['foo/bar', $files]);
        $generator->shouldReceive('getStub')->once()->with('lang/en/model.stub');
        $generator->shouldReceive('getStub')->once()->with('form.blade.stub');

        $this->files     = $files;
        $this->generator = $generator;
    }

    /** @test */
    public function it_can_generate_textareas()
    {
        $this->files->shouldReceive('exists')->atLeast()->once()->andReturn(false);

        $this->generator->shouldReceive('getStub')->once()->with('form-textarea.stub');

        $this->generator->create('foo', [
            [
                'type' => 'text',
                'field' => 'foo',
            ]
        ]);
    }

    /** @test */
    public function it_can_generate_checkboxes()
    {
        $this->files->shouldReceive('exists')->atLeast()->once()->andReturn(false);

        $this->generator->shouldReceive('getStub')->once()->with('form-checkbox.stub');

        $this->generator->create('foo', [
            [
                'type' => 'boolean',
                'field' => 'foo',
            ]
        ]);
    }

    /** @test */
    public function it_can_generate_inputs()
    {
        $this->files->shouldReceive('exists')->atLeast()->once()->andReturn(false);

        $this->generator->shouldReceive('getStub')->once()->with('form-input.stub');

        $this->generator->create('foo', [
            [
                'type' => 'string',
                'field' => 'foo',
            ]
        ]);
    }

    /** @test */
    public function it_will_merge_language_files_if_they_already_exist()
    {
        $this->files->shouldReceive('exists')->atLeast()->once()->andReturn(true);
        $this->files->shouldReceive('getRequire')->once()->andReturn(['general' => []]);

        $this->generator->shouldReceive('getStub')->once()->with('form-input.stub');

        $this->generator->create('foo', [
            [
                'type' => 'string',
                'field' => 'foo',
            ]
        ]);
    }
}
