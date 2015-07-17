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
 * @version    2.0.5
 * @author     Cartalyst LLC
 * @license    Cartalyst PSL
 * @copyright  (c) 2011-2015, Cartalyst LLC
 * @link       http://cartalyst.com
 */

namespace Cartalyst\Workshop\Generators;

use Illuminate\Support\Str;
use Illuminate\Filesystem\Filesystem;

class ExtensionGenerator extends AbstractGenerator
{
    /**
     * Create a new extension.
     *
     * @return string
     */
    public function create()
    {
        $this->path = str_replace('extensions', 'workbench', $this->path);

        // Create extension dir
        $this->ensureDirectory($this->path);

        // Create database dirs
        $this->databaseDirs();

        // Write composer.json
        $this->writeComposerFile();

        // Write extension.php
        $this->writeExtensionFile();
    }

    /**
     * Creates a new model.
     *
     * @param  string  $name
     * @return void
     */
    public function createModel($name = null)
    {
        $name = $this->sanitize($name);

        $className = Str::studly(ucfirst($name ?: $this->extension->name));

        $content = $this->prepare($this->getStub('model.stub'), [
            'class_name'  => $className,
            'table'       => Str::lower(Str::plural($name)),
            'lower_model' => Str::lower($name),
        ]);

        $path = $this->path.'/src/Models/';

        $this->ensureDirectory($path);

        $path .= $className.'.php';

        $this->files->put($path, $content);
    }

    /**
     * Creates a new widget.
     *
     * @param  string  $name
     * @return void
     */
    public function createWidget($name = null)
    {
        $name = $this->sanitize($name);

        $name = Str::studly(ucfirst($name ?: $this->extension->name));

        $content = $this->prepare($this->getStub('widget.stub'), [
            'class_name' => $name,
        ]);

        $path = $this->path.'/src/Widgets/';

        $this->ensureDirectory($path);

        $path .= $name.'.php';

        $this->files->put($path, $content);
    }

    /**
     * Creates a new controller.
     *
     * @param  string  $name
     * @param  string  $area
     * @param  array  $args
     * @return void
     */
    public function createController($name = null, $area = 'Admin', $args = [])
    {
        $name = $this->sanitize($name);

        if (isset($args['columns'])) {
            $cols = "'id',\n";

            foreach ($args['columns'] as $column) {
                $cols .= "\t\t\t'".$column['field']."',\n";
            }

            $cols .= "\t\t\t'created_at',\n";

            $args['columns'] = trim($cols);
        } else {
            $args['columns'] = "'*',";
        }

        $controllerName = Str::studly(ucfirst(($name ? Str::plural($name): $this->extension->name).'Controller'));

        $area = ucfirst($area);

        if (in_array($area, ['Admin', 'Frontend'])) {
            $stub = Str::lower($area).'-controller.stub';
        } else {
            $stub = 'controller.stub';
        }

        $args = array_merge($args, [
            'class_name'         => $controllerName,
            'area'               => $area,
            'model'              => Str::studly(ucfirst($name)),
            'camel_model'        => Str::camel(Str::lower($name)),
            'plural_name'        => Str::studly(ucfirst(Str::plural($name))),
            'plural_lower_model' => Str::lower(Str::plural($name)),
        ]);

        $content = $this->prepare($this->getStub($stub), $args);

        $path = $this->path.'/src/Controllers/'.$area.'/';

        $this->ensureDirectory($path);

        $path .= $controllerName.'.php';

        $this->files->put($path, $content);
    }

    /**
     * Writes the composer.json file.
     *
     * @return void
     */
    public function writeComposerFile()
    {
        $content = $this->prepare($this->getStub('composer.json'));

        $autoloads = [
            'database/migrations',
            'database/seeds',
        ];

        $content = str_replace('{{classmap_autoloads}}', implode(",\n\t\t\t", array_map(function ($autoload) {
            return '"'.$autoload.'"';
        }, $autoloads)), $content);

        $this->files->put($this->path.'/composer.json', $content);
    }

    /**
     * Writes the extension.php file.
     *
     * @return void
     */
    public function writeExtensionFile()
    {
        $content = $this->prepare($this->getStub('extension.stub'));

        $this->files->put($this->path.'/extension.php', $content);
    }

    /**
     * Writes the routes section.
     *
     * @param  string  $resource
     * @param  bool  $adminRoutes
     * @param  bool  $frontendRoutes
     * @return void
     */
    public function writeRoutes($resource, $adminRoutes = null, $frontendRoutes = null)
    {
        $resource = $this->sanitize($resource);

        $content = $this->files->get($this->getExtensionPhpPath());

        $routes = null;

        if ($adminRoutes) {
            $routes .= $this->prepare($this->getStub('admin-routes.stub'), [
                'plural_name'        => Str::studly(ucfirst(Str::plural($resource))),
                'plural_lower_model' => Str::lower(Str::plural($resource)),
            ]);
        }

        if ($frontendRoutes) {
            $routes = $routes ? $routes."\n" : '';

            $routes .= $this->prepare($this->getStub('frontend-routes.stub'), [
                'plural_name'        => Str::studly(ucfirst(Str::plural($resource))),
                'plural_lower_model' => Str::lower(Str::plural($resource)),
            ]);
        }

        $newRoutes = $this->prepare($this->getStub('routes.stub'), [
            'routes' => rtrim($routes),
        ]);

        preg_match('/'.'\'routes\' => function\(.*?\)\s*\n\s*{(.*?)\s*},/s', $content, $oldRoutes);

        preg_match('/'.'\'routes\' => function\(.*?\)\s*\n\s*{(.*?)\s*},/s', $newRoutes, $newRoutes);

        $oldRoutes = last($oldRoutes);
        $newRoutes = last($newRoutes);

        if ($this->alreadyExists($oldRoutes, $newRoutes)) {
            return;
        }

        $routesContent = $oldRoutes."\n".$newRoutes;

        $stub = 'empty-extension-closure.stub';

        $resourceReplacement = $this->prepare($this->getStub($stub), [
            'content' => trim($routesContent),
            'type'    => 'routes',
        ]);

        $content = preg_replace(
            "/'routes' => function\s*.*?},/s",
            trim($resourceReplacement),
            $content
        );

        $this->files->put($this->getExtensionPhpPath(), $content);
    }

    /**
     * Writes the service provider.
     *
     * @param  string  $resource
     * @return void
     */
    public function writeServiceProvider($resource)
    {
        $resource = $this->sanitize($resource);

        $serviceProvider = Str::studly(ucfirst($resource));

        $content = $this->prepare($this->getStub('service-provider.stub'), [
            'provider'    => $serviceProvider,
            'plural_name' => Str::studly(ucfirst(Str::plural($resource))),
            'boot'        => trim($this->writeMethod('boot', $resource)),
            'register'    => trim($this->writeMethod('register', $resource)),
        ]);

        $dir = $this->path.'/src/Providers/';

        $this->ensureDirectory($dir);

        $this->files->put($dir.$serviceProvider.'ServiceProvider.php', $content);

        $extensionPhp = $this->files->get($this->getExtensionPhpPath());

        $newProviders = $this->prepare($this->getStub('providers.stub'), [
            'provider' => $serviceProvider,
        ]);

        preg_match('/\'providers\' => \[\s*\n\s*(.*?)\s*],/s', $extensionPhp, $oldProviders);

        $oldProviders = last($oldProviders);

        if ($this->alreadyExists(trim($oldProviders), trim($newProviders))) {
            return;
        }

        $resources = $oldProviders."\n\t".$newProviders;

        $stub = 'empty-providers.stub';

        $resourceReplacement = $this->prepare($this->getStub($stub), [
            'content' => trim($resources),
        ]);

        $content = preg_replace(
            '/\'providers\' => \[\s*\n\s*(.*?)\s*],/s',
            rtrim($resourceReplacement),
            $extensionPhp
        );

        $this->files->put($this->getExtensionPhpPath(), $content);
    }

    /**
     * Writes a method from its stub.
     *
     * @param  string  $name
     * @param  string  $resource
     * @return string
     */
    protected function writeMethod($name, $resource)
    {
        return $this->prepare($this->getStub($name.'.stub'), [
            'model'       => Str::studly(ucfirst($resource)),
            'lower_model' => Str::lower(Str::studly($resource)),
        ]);
    }

    /**
     * Writes the permissions section.
     *
     * @param  string  $resource
     * @return void
     */
    public function writePermissions($resource)
    {
        $resource = $this->sanitize($resource);

        $content = $this->files->get($this->getExtensionPhpPath());

        $newPermissions = $this->prepare($this->getStub('permissions.stub'), [
            'plural_name'        => ucfirst(Str::plural($resource)),
            'model'              => ucfirst($resource),
            'lower_model'        => Str::lower($resource),
            'plural_lower_model' => Str::lower(Str::plural($resource)),
        ]);

        preg_match('/'.'\'permissions\' => function\(.*?\)\s*\n\s*{(.*?)\s*},/s', $content, $oldPermissions);

        $oldPermissions = last($oldPermissions);

        if ($this->alreadyExists(trim($oldPermissions), trim($newPermissions))) {
            return;
        }

        $resources = $oldPermissions."\n\n".$newPermissions;

        $stub = 'empty-permissions.stub';

        $resourceReplacement = $this->prepare($this->getStub($stub), [
            'content' => trim($resources),
            'type'    => 'permissions',
        ]);

        $content = preg_replace(
            "/'permissions' => function\s*.*?},/s",
            rtrim($resourceReplacement),
            $content
        );

        $this->files->put($this->getExtensionPhpPath(), $content);
    }

    /**
     * Writes the menu items.
     *
     * @param  string  $resource
     * @return void
     */
    public function writeMenus($resource)
    {
        $resource = $this->sanitize($resource);

        $lowerResource = Str::lower($resource);

        $lowerName = $this->extension->lowerName;

        $lowerVendor = $this->extension->lowerVendor;

        $extensionPhpPath = $this->getExtensionPhpPath();

        $content = $this->files->get($extensionPhpPath);

        $newMenu = [
            'class' => 'fa fa-circle-o',
            'name'  => Str::plural(Str::title($resource)),
            'uri'   => $lowerName.'/'.Str::plural($lowerResource),
            'regex' => '/:admin\/'.$lowerName.'\/'.$lowerResource.'/i',
            'slug'  => 'admin-'.$lowerVendor.'-'.$lowerName.'-'.$lowerResource,
        ];

        $menus = array_get($this->files->getRequire($extensionPhpPath), 'menus');

        $children = [];

        if ($admin = array_get($menus, 'admin')) {
            foreach ($admin as $child) {
                if ($children = array_get($child, 'children')) {
                    foreach ($children as $_child) {
                        if ($_child === $newMenu) {
                            return;
                        }
                    }
                }
            }
        }

        if (! $children) {
            $children = [
                $newMenu,
            ];
        } else {
            $children[] = $newMenu;
        }

        $menus['admin'][0]['children'] = $children;

        $newMenu = "'menus' => [\n\n\t\t".$this->wrapArray($menus, "\t")."\n\t],\n\n";

        $content = preg_replace(
            "/'menus' => \[(.*)\]\s*,/s",
            rtrim($newMenu),
            $content
        );

        $this->files->put($extensionPhpPath, $content);
    }

    /**
     * Writes the language files.
     *
     * @param  string  $resource
     * @return void
     */
    public function writeLang($resource)
    {
        $resource = $this->sanitize($resource);

        $this->ensureDirectory($this->path.'/lang/en/'.Str::lower(Str::plural($resource)).'/');

        $stub = $this->getStub('lang/en/common.stub');

        $content = $this->prepare($stub, [
            'model'        => ucfirst($resource),
            'lower_model'  => Str::lower($resource),
            'plural_model' => Str::title(Str::plural($resource)),
        ]);

        $this->files->put($this->path.'/lang/en/'.Str::lower(Str::plural($resource)).'/common.php', $content);

        $stub = $this->getStub('lang/en/message.stub');

        $content = $this->prepare($stub, [
            'model'       => ucfirst($resource),
            'lower_model' => Str::lower($resource),
        ]);

        $this->files->put($this->path.'/lang/en/'.Str::lower(Str::plural($resource)).'/message.php', $content);

        $stub = $this->getStub('lang/en/permissions.stub');

        $content = $this->prepare($stub, [
            'model'       => ucfirst($resource),
            'plural_name' => ucfirst(Str::plural($resource)),
        ]);

        $this->files->put($this->path.'/lang/en/'.Str::lower(Str::plural($resource)).'/permissions.php', $content);
    }

    /**
     * Writes database directories.
     *
     * @return void
     */
    protected function databaseDirs()
    {
        $this->ensureDirectory($this->path.'/database/migrations');
        $this->ensureDirectory($this->path.'/database/seeds');

        $this->files->put($this->path.'/database/migrations/.gitkeep', '');
        $this->files->put($this->path.'/database/seeds/.gitkeep', '');
    }

    /**
     * Check if the new content already exists.
     *
     * @param  string  $oldContent
     * @param  string  $newContent
     * @return bool
     */
    protected function alreadyExists($oldContent, $newContent)
    {
        if (strpos($oldContent, $newContent) !== false) {
            return true;
        }

        return false;
    }
}
