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

class DataGridGenerator extends AbstractGenerator
{
    /**
     * Html builder instance.
     *
     * @var \Illuminate\Html\HtmlBuilder
     */
    protected $html;

    /**
     * Form builder instance.
     *
     * @var \Illuminate\Html\FormBuilder
     */
    protected $form;

    /**
     * Data grid templates.
     *
     * @var array
     */
    protected $dataGridTemplates = [
        'results.blade.stub',
        'filters.blade.stub',
        'pagination.blade.stub',
        'no_results.blade.stub',
        'no_filters.blade.stub',
    ];

    /**
     * Data grid columns.
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
     * @param  string  $slug
     * @param  \Illuminate\Filesystem\Filesystem  $files
     * @param  \Illuminate\Html\HtmlBuilder  $html
     * @param  \Illuminate\Html\FormBuilder  $form
     * @return void
     */
    public function __construct($slug, $files, $html, $form)
    {
        parent::__construct($slug, $files);

        $this->html = $html;
        $this->form = $form;
    }

    /**
     * Create a new data grid.
     *
     * @param  string  $name
     * @param  string  $themeArea
     * @param  string  $theme
     * @param  string  $viewName
     * @param  array  $columns
     * @param  string  $model
     * @return void
     */
    public function create($name, $themeArea = 'admin', $theme = 'default', $viewName = 'index', $columns = [], $model = null)
    {
        $model = $model ?: $name;

        $name  = $this->sanitize($name);
        $model = $this->sanitize($model);

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
            'href'    => '#',
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
            $trans = "{{{ trans('".$this->extension->lowerVendor."/".$this->extension->lowerName."::".Str::lower(Str::plural($model))."/model.general.{$column['content']}') }}}";

            $headers .= "\n\t\t\t\t\t".'<th class="sortable" data-sort="'.$column['content'].'">'.$trans.'</th>';
        }

        $headers = ltrim($headers);

        $includes = implode("\n", $includes);

        $lowerModel = Str::lower($model);

        $view = $this->prepare($stub, [
            'headers'            => $headers,
            'includes'           => $includes,
            'grid_name'          => $name,
            'lower_model'        => $lowerModel,
            'plural_lower_model' => Str::lower(Str::plural($lowerModel)),
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
            'plural_lower_model' => Str::lower(Str::plural($lowerModel)),
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
     * @param  string $name
     * @param  string $stub
     * @param  string $model
     * @return string
     */
    protected function processDataGridTemplate($name, $stub, $model)
    {
        $el = $this->prepareColumns($model);

        $columns = ("<td>".implode("</td>\n\t\t\t<td>", $el).'</td>');

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
     * @param  bool  $results
     * @return array
     */
    protected function prepareColumns($model)
    {
        $el = [];

        foreach ($this->dataGridColumns as $attributes) {
            $type = array_pull($attributes, 'type');

            if ($type) {
                if ($type === 'a') {
                    $elementContent = '<%= r.' . array_pull($attributes, 'content') . ' %>';

                    $link = ($this->html->decode($this->html->link('#', $elementContent, $attributes)));

                    $link = str_replace('href="#"', 'href="<%= r.edit_uri %>"', $link);

                    $el[] = $link;
                } elseif ($type === 'checkbox') {
                    $checkBoxName = array_pull($attributes, 'name');

                    $value = array_pull($attributes, 'value');

                    $value = '<%= r.' . $value . ' %>';

                    $el[] = ($this->html->decode($this->form->checkbox($checkBoxName, $value, null, $attributes)));
                }
            } else {
                $el[] = '<%= r.' . array_pull($attributes, 'content') . ' %>';
            }
        }

        return $el;
    }

    /**
     * Writes the data grid language file.
     *
     * @param  array  $columns
     * @return void
     */
    protected function writeLangFiles($columns, $model, $name = null)
    {
        $model = $model ?: $name;
        $model = Str::lower(Str::plural($model));

        $stub = $this->getStub('lang/en/model.stub');

        $filePath = $this->path.'/lang/en/'.Str::lower(Str::plural($model)).'/';

        $this->ensureDirectory($filePath);

        $filePath .= 'model.php';

        $values['id'] = 'Id';

        foreach ($columns as $column) {
            $values[$column['field']] = Str::title($column['field']);
        }

        $values['created_at'] = 'Created At';

        if ($this->files->exists($filePath)) {
            $trans = $this->files->getRequire($filePath);

            $values = array_merge($values, array_get($trans, 'general'));
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
     * @param  string  $dir
     * @return string
     */
    protected function getPath($themeArea, $theme, $model, $dir = 'views')
    {
        return $this->path.'/themes/'.$themeArea.'/'.$theme.'/packages/'.$this->extension->lowerVendor.'/'.$this->extension->lowerName.'/'.$dir.'/'.Str::lower(Str::plural($model)).'/';
    }
}
