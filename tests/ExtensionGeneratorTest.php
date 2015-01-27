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
use Cartalyst\Workshop\Generators\ExtensionGenerator;

class ExtensionGeneratorTest extends PHPUnit_Framework_TestCase
{
    /**
     * Generator instance.
     *
     * @var \Cartalyst\Workshop\Generators\ExtensionGenerator
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

        $generator = m::mock('Cartalyst\Workshop\Generators\ExtensionGenerator[getStub]', ['foo/bar', $files]);

        $this->files     = $files;
        $this->generator = $generator;
    }

    /** @test */
    public function it_can_generate_textareas()
    {
        $this->generator->shouldReceive('getStub')->once()->with('composer.json');
        $this->generator->shouldReceive('getStub')->once()->with('extension.stub');

        $this->generator->create('foo/bar');
    }

    /** @test */
    public function it_can_create_models()
    {
        $this->generator->shouldReceive('getStub')->once()->with('model.stub');

        $this->generator->createModel('foo');
    }

    /** @test */
    public function it_can_create_widgets()
    {
        $this->generator->shouldReceive('getStub')->once()->with('widget.stub');

        $this->generator->createWidget('foo');
    }

    /** @test */
    public function it_can_create_controllers()
    {
        $this->generator->shouldReceive('getStub')->once()->with('admin-controller.stub');

        $this->generator->createController('foo');

        $this->generator->shouldReceive('getStub')->once()->with('frontend-controller.stub');

        $this->generator->createController('foo', 'Frontend');

        $this->generator->shouldReceive('getStub')->once()->with('controller.stub');

        $this->generator->createController('foo', 'Other');

        $this->generator->shouldReceive('getStub')->once()->with('admin-controller.stub');

        $this->generator->createController('foo', 'Admin', [
            'columns' => [
                [
                    'field' => 'foo',
                ],
                [
                    'field' => 'bar',
                ],
            ],
        ]);
    }

    /** @test */
    public function it_can_create_service_providers()
    {
        $this->generator->shouldReceive('getStub')->once()->with('service-provider.stub');
        $this->generator->shouldReceive('getStub')->once()->with('boot.stub');
        $this->generator->shouldReceive('getStub')->once()->with('register.stub');
        $this->generator->shouldReceive('getStub')->once()->with('providers.stub');
        $this->generator->shouldReceive('getStub')->once()->with('empty-providers.stub');
        $this->files->shouldReceive('exists')->twice()->andReturn(true);

        $this->generator->writeServiceProvider('foo');
    }

    /** @test */
    public function it_can_write_permissions()
    {
        $this->generator->shouldReceive('getStub')->once()->with('permissions.stub');
        $this->generator->shouldReceive('getStub')->once()->with('empty-permissions.stub');
        $this->files->shouldReceive('exists')->twice()->andReturn(true);

        $this->generator->writePermissions('foo');
    }

    /** @test */
    public function it_can_write_routes()
    {
        $this->generator->shouldReceive('getStub')->once()->with('routes.stub');
        $this->generator->shouldReceive('getStub')->once()->with('empty-extension-closure.stub');
        $this->files->shouldReceive('exists')->twice()->andReturn(true);

        $this->generator->writeRoutes('foo');
    }

    /** @test */
    public function it_can_write_admin_routes()
    {
        $this->generator->shouldReceive('getStub')->once()->with('routes.stub');
        $this->generator->shouldReceive('getStub')->once()->with('empty-extension-closure.stub');
        $this->generator->shouldReceive('getStub')->once()->with('admin-routes.stub');
        $this->files->shouldReceive('exists')->twice()->andReturn(true);

        $this->generator->writeRoutes('foo', true);
    }

    /** @test */
    public function it_can_write_frontend_routes()
    {
        $this->generator->shouldReceive('getStub')->once()->with('routes.stub');
        $this->generator->shouldReceive('getStub')->once()->with('empty-extension-closure.stub');
        $this->generator->shouldReceive('getStub')->once()->with('frontend-routes.stub');
        $this->files->shouldReceive('exists')->twice()->andReturn(true);

        $this->generator->writeRoutes('foo', false, true);
    }

    /** @test */
    public function it_can_write_menus()
    {
        $this->files->shouldReceive('getRequire')->once()->andReturn([]);
        $this->files->shouldReceive('exists')->once()->andReturn(true);

        $this->generator->writeMenus('foo');
    }

    /** @test */
    public function it_can_write_language_files()
    {
        $this->generator->shouldReceive('getStub')->once()->with('lang/en/common.stub');
        $this->generator->shouldReceive('getStub')->once()->with('lang/en/message.stub');
        $this->generator->shouldReceive('getStub')->once()->with('lang/en/permissions.stub');

        $this->generator->writeLang('foo');
    }
}
