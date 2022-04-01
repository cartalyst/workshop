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
 * @version    7.0.0
 * @author     Cartalyst LLC
 * @license    Cartalyst PSL
 * @copyright  (c) 2011-2022, Cartalyst LLC
 * @link       https://cartalyst.com
 */

namespace Cartalyst\Workshop\Generators;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Collective\Html\FormBuilder;
use Collective\Html\HtmlBuilder;
use Illuminate\Filesystem\Filesystem;

class DataGridGenerator extends AbstractGenerator
{
    /**
     * The Illuminate Html builder instance.
     *
     * @var \Collective\Html\HtmlBuilder
     */
    protected $html;

    /**
     * The Illuminate Form Builder instance.
     *
     * @var \Collective\Html\FormBuilder
     */
    protected $form;

    /**
     * The Data grid templates.
     *
     * @var array
     */
    protected $dataGridTemplates = [
        'results.blade.stub',
        'filters.blade.stub',
        'pagination.blade.stub',
        'no_results.blade.stub',
    ];

    /**
     * The Data grid columns.
     *
     * @var array
     */
    protected $dataGridColumns = [
        [
            'type'                     => 'checkbox',
            'name'                     => 'entries[]',
            'value'                    => 'id',
            'content'                  => 'id',
            'input data-grid-checkbox' => '',
        ],
    ];

    /**
     * Constructor.
     *
     * @param string                            $slug
     * @param \Illuminate\Filesystem\Filesystem $files
     * @param \Collective\Html\HtmlBuilder      $html
     * @param \Collective\Html\FormBuilder      $form
     *
     * @return void
     */
    public function __construct(string $slug, Filesystem $files, HtmlBuilder $html, FormBuilder $form)
    {
        parent::__construct($slug, $files);

        $this->html = $html;
        $this->form = $form;
    }

    /**
     * Create a new data grid.
     *
     * @param string      $name
     * @param string      $themeArea
     * @param string      $theme
     * @param string      $viewName
     * @param array       $columns
     * @param string|null $model
     *
     * @return void
     */
    public function create(string $name, $themeArea = 'admin', $theme = 'default', string $viewName = 'index', array $columns = [], ?string $model = null): void
    {
        $model = $model ?: $name;

        $name  = $this->sanitize($name);
        $model = $this->sanitize($model);

        $lowerModel       = Str::lower($model);
        $pluralLowerModel = Str::plural($lowerModel);

        $this->writeLangFiles($columns, $model, $name);

        $basePath = $this->getPath($themeArea, $theme, $model);

        $dir = $basePath.'grid/'.$viewName.'/';

        $dgCols = [];

        foreach ($columns as $column) {
            $dgCols[]['content'] = $this->sanitize($column['field'], '/[^a-zA-Z0-9_-]/');
        }

        array_push($dgCols, ['content' => 'created_at']);

        $this->dataGridColumns[] = [
            'type'    => 'a',
            'content' => 'id',
        ];

        $this->dataGridColumns = array_merge($this->dataGridColumns, $dgCols);

        $contents = [];

        foreach ($this->dataGridTemplates as $template) {
            $templateContent = $this->processDataGridTemplate($name, $this->getStub($template), $model);

            $contents[$template] = $templateContent;
        }

        foreach ($contents as $file => $content) {
            // Write data grid templates
            $file = str_replace('.stub', '.php', $file);

            $this->ensureDirectory($dir);

            $this->files->put($dir.$file, $content);

            // Prepare view includes
            $file = str_replace('.blade.php', '', $file);

            $includes[] = "@include('{$this->extension->lowerVendor}/{$this->extension->lowerName}::".Str::lower(Str::plural($model))."/grid/{$name}/{$file}')";
        }

        $stub = $this->getStub('view-admin-index.blade.stub');

        $columns = $this->dataGridColumns;

        array_shift($columns);

        $headers = '<th><input data-grid-checkbox="all" type="checkbox"></th>';

        foreach ($columns as $column) {
            $trans = "{{{ trans('".$this->extension->lowerVendor.'/'.$this->extension->lowerName.'::'.$pluralLowerModel."/model.general.{$column['content']}') }}}";

            $headers .= "\n\t\t\t\t\t".'<th class="sortable" data-grid-sort="'.$column['content'].'">'.$trans.'</th>';
        }

        $headers = ltrim($headers);

        $includes = implode("\n", $includes);

        $view = $this->prepare($stub, [
            'headers'            => $headers,
            'includes'           => $includes,
            'grid_name'          => $name,
            'lower_model'        => $lowerModel,
            'plural_lower_model' => $pluralLowerModel,
        ]);

        $lowerModel = $lowerModel ?: $name;

        $viewPath = $basePath.'/';

        $this->ensureDirectory($viewPath);

        $viewPath .= $viewName.'.blade.php';

        $this->files->put($viewPath, $view);

        // Write index.js
        $jsStub = $this->getStub('index.js.stub');

        $js = $this->prepare($jsStub, [
            'grid_name' => $name,
        ]);

        $jsPath = $this->getPath($themeArea, $theme, $model, 'assets').'js';

        $this->ensureDirectory($jsPath);

        $this->files->put($jsPath.'/index.js', $js);

        // Write help files
        $helpStub = $this->getStub('help.blade.stub');

        $help = $this->prepare($helpStub, [
            'lower_model'        => $lowerModel,
            'plural_lower_model' => $pluralLowerModel,
        ]);

        $helpPath = $this->getPath($themeArea, $theme, $model);

        $this->files->put($helpPath.'help.blade.php', $help);

        $helpFilePath = $helpPath.'content/';

        $this->ensureDirectory($helpFilePath);

        $this->files->put($helpFilePath.'help.md', null);
    }

    /**
     * Process data grid templates.
     *
     * @param string $name
     * @param string $stub
     * @param string $model
     *
     * @return string
     */
    protected function processDataGridTemplate(string $name, string $stub, string $model): string
    {
        $el = $this->prepareColumns($model);

        $columns = ('<td>'.implode("</td>\n\t\t\t<td>", $el).'</td>');

        $rows = count($this->dataGridColumns) + 1;

        return $this->prepare($stub, [
            'columns'   => $columns,
            'rows'      => $rows,
            'grid_name' => $name,
        ]);
    }

    /**
     * Prepare data grid columns.
     *
     * @return array
     */
    protected function prepareColumns(): array
    {
        $el = [];

        foreach ($this->dataGridColumns as $attributes) {
            $type = Arr::pull($attributes, 'type');

            if ($type) {
                if ($type === 'a') {
                    $elementContent = '<%= r.'.Arr::pull($attributes, 'content').' %>';

                    $link = ($this->html->decode($this->html->link('#', $elementContent, $attributes)));

                    if (! is_null($link)) {
                        $link = str_replace('href="#"', 'href="<%= r.edit_uri %>"', $link);
                    }

                    $el[] = $link;
                } elseif ($type === 'checkbox') {
                    $checkBoxName = Arr::pull($attributes, 'name');

                    $value = Arr::pull($attributes, 'value');

                    $value = '<%= r.'.$value.' %>';

                    $el[] = ($this->html->decode($this->form->checkbox($checkBoxName, $value, null, $attributes)));
                }
            } else {
                $el[] = '<%= r.'.Arr::pull($attributes, 'content').' %>';
            }
        }

        return $el;
    }

    /**
     * Writes the data grid language file.
     *
     * @param array       $columns
     * @param string      $model
     * @param string|null $name
     *
     * @return void
     */
    protected function writeLangFiles(array $columns, string $model, ?string $name = null): void
    {
        $model = $model ?: $name;
        $model = Str::lower(Str::plural($model));

        $stub = $this->getStub('lang/en/model.stub');

        $filePath = $this->getFullPath('resources/lang/en/'.Str::lower(Str::plural($model)).'/');

        $this->ensureDirectory($filePath);

        $filePath .= 'model.php';

        $values['id'] = 'Id';

        foreach ($columns as $column) {
            $values[$column['field']] = Str::title($column['field']);
        }

        $values['created_at'] = 'Created At';

        if ($this->files->exists($filePath)) {
            $trans = $this->files->getRequire($filePath);

            $values = array_merge($values, Arr::get($trans, 'general'));
        }

        $trans = $this->wrapArray($values, "\t");

        $content = $this->prepare($stub, [
            'fields' => rtrim($trans),
        ]);

        $this->files->put($filePath, $content);
    }

    /**
     * Returns the workbench dir path.
     *
     * @param string $themeArea
     * @param string $themeName
     * @param string $model
     * @param string $dir
     *
     * @return string
     */
    protected function getPath(string $themeArea, string $themeName, string $model, string $dir = 'views'): string
    {
        $lowerPluralModel = Str::lower(Str::plural($model));

        $paths = [
            'resources',
            'themes',
            $themeArea,
            $themeName,
            'packages',
            $this->extension->lowerVendor,
            $this->extension->lowerName,
            $dir,
            $lowerPluralModel,
        ];

        return $this->getFullPath(implode('/', $paths).'/');
    }
}
