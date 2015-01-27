## Usage

In this section we'll show how you can make use of the workshop generators.

### Generators

#### DataGrid Generator

The data grid generator can generate data grid view files accompanied with the js file required to initialize it and the help file and content block.

```php
use Cartalyst\Workshop\Generators\DataGridGenerator;

$generator = new DataGridGenerator('foo/bar', $app['files'], $app['html'], $app['form']);

$generator->create('post');
```

The create method can receive several arguments.

Argument     | Required | Type   | Default
------------ | -------- | ------ | -------
name		 | Yes      | string | -
themeArea    | No       | string | admin
theme        | No       | string | default
viewName     | No       | string | index
columns      | No       | array  | []
model        | No       | string | null

The create command will generate the following files and directories.

```
|-- lang
|   `-- en
|       `-- posts
|           `-- model.php
`-- themes
    `-- admin
        `-- default
            `-- packages
                `-- foo
                    `-- bar
                        |-- assets
                        |   `-- posts
                        |       `-- js
                        |           `-- index.js
                        `-- views
                            `-- posts
                                |-- content
                                |   `-- help.md
                                |-- grid
                                |   `-- index
                                |       |-- filters.blade.php
                                |       |-- no_filters.blade.php
                                |       |-- no_results.blade.php
                                |       |-- pagination.blade.php
                                |       `-- results.blade.php
                                |-- help.blade.php
                                `-- index.blade.php
```

#### Extension Generator

The extension generator can generate the core files and resources for an extension.

```php
use Cartalyst\Workshop\Generators\ExtensionGenerator;

$generator = new ExtensionGenerator('foo/bar', $app['files']);

$generator->create();
```

The create command will generate the following files and directories.

```
|-- composer.json
|-- database
|   |-- migrations
|   |   `-- .gitkeep
|   `-- seeds
|       `-- .gitkeep
`-- extension.php
```

##### Additional methods

- createModel($name)

Creates a model.

```php
$generator->createModel('post');
```

- createWidget($name)

Creates a widget.

```php
$generator->createWidget('post');
```

- createController($name, $area)

Creates a controller.

```php
$generator->createController('foo', 'admin');
```

- writeComposerFile()

Writes the composer.json file.

```php
$generator->writeComposerFile();
```

- writeExtensionFile()

Writes the extension.php file.

```php
$generator->writeExtensionFile();
```

- writeRoutes($resource, $adminRoutes, $frontendRoutes)

Writes the routes section on the extension.php file.

```php
$generator->writeRoutes('post', true, false);
```

- writeServiceProvider($resource)

Writes the service providers section on the extension.php file.

```php
$generator->writeServiceProvider('post');
```

- writePermissions($resource)

Writes the permissions section on the extension.php file.

```php
$generator->writePermissions('post');
```

- writeMenus($resource)

Writes the menus section on the extension.php file.

```php
$generator->writeMenus('post');
```

- writeLang($resource)

Writes the core language files. (common.php, message.php, permissions.php)

```php
$generator->writeLang('post');
```

#### Extension Theme Generator

The extension theme generator can generate theme directories.

```php
use Cartalyst\Workshop\Generators\ExtensionThemeGenerator;

$generator = new ExtensionThemeGenerator('foo/bar', $app['files']);

$generator->create('admin');
```

The create method can receive several arguments.

Argument | Required | Type   | Default
-------- | -------- | ------ | -------
area     | Yes      | string | -
theme    | No       | string | default

The create command will generate the following files and directories.

```
`-- themes
    `-- admin
        `-- default
            `-- packages
                `-- foo
                    `-- bar
                        |-- assets
                        |   |-- css
                        |   |   `-- style.css
                        |   `-- js
                        |       `-- script.js
                        `-- views
                            `-- .gitkeep
```

#### Form Generator

The form generator can generate form view files.

```php
use Cartalyst\Workshop\Generators\FormGenerator;

$generator = new FormGenerator('foo/bar', $app['files']);

$generator->create('post');
```

The create method can receive several arguments.

Argument     | Required | Type   | Default
------------ | -------- | ------ | -------
model		 | Yes      | string | -
columns      | No       | array  | []
view         | No       | string | form

The create command will generate the following files and directories.

```
|-- lang
|   `-- en
|       `-- posts
|           `-- model.php
`-- themes
    `-- admin
        `-- default
            `-- packages
                `-- foo
                    `-- bar
                        `-- views
                            `-- posts
                                `-- form.blade.php
```

#### Migrations Generator

The migrations generator can generate migrations and seeders.

```php
use Cartalyst\Workshop\Generators\MigrationsGenerator;

$generator = new MigrationsGenerator('foo/bar', $app['files']);

$generator->create('posts');
```

The create method can receive several arguments.

Argument     | Required | Type   | Default
------------ | -------- | ------ | -------
table		 | Yes      | string | -
columns      | No       | array  | []
increments   | No       | bool   | true
timestamps   | No       | bool   | true

The create command will generate the following files and directories.

```
`-- database
    `-- migrations
        `-- 2015_01_27_184239_alter_posts_table.php

```

#### Repository Generator

The repository generator can generate repositories and handlers that are used on the repositories.

```php
use Cartalyst\Workshop\Generators\RepositoryGenerator;

$generator = new RepositoryGenerator('foo/bar', $app['files']);

$generator->create('post');
```

The create method can receive one argument.

Argument     | Required | Type   | Default
------------ | -------- | ------ | -------
model		 | Yes      | string | -

The create command will generate the following files and directories.

```
`-- src
    |-- Handlers
    |   |-- PostDataHandler.php
    |   |-- PostDataHandlerInterface.php
    |   |-- PostEventHandler.php
    |   |-- PostEventHandlerInterface.php
    |   |-- PostValidator.php
    |   `-- PostValidatorInterface.php
    `-- Repositories
        |-- PostRepository.php
        `-- PostRepositoryInterface.php
```

### Stubs

Stub files are located under `src/stubs/*`, you can override the default stubs by setting a stubs directory using `Cartalyst\Workshop\Generators\AbstractGenerator::setStubsDir($dir)` and just override the stub files you need changed on that directory.
