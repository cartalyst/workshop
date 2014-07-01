<?php namespace Cartalyst\Workshop\Generators;
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

use Cartalyst\Workshop\Extension;
use URL;
use Illuminate\Support\Str;

class DataGridGenerator extends Generator {

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
			'type'    => 'checkbox',
			'name'    => 'entries[]',
			'value'   => 'id',
			'content' => 'id',
		],
	];

	/**
	 * Constructor.
	 *
	 * @param  \Cartalyst\Workshop\Extension  $extension
	 * @param  \Illuminate\Filesystem\Filesystem  $files
	 * @return void
	 */
	public function __construct($extension, $files, $html, $form, $stubsDir = null)
	{
		parent::__construct($extension, $files, $stubsDir);

		$this->html = $html;
		$this->form = $form;
	}

	/**
	 * Create a new data grid.
	 *
	 * @param  string  $name
	 * @param  string  $themeType
	 * @param  string  $theme
	 * @param  string  $viewName
	 * @param  array  $columns
	 * @return void
	 */
	public function create($name, $themeType = 'admin', $theme = 'default', $viewName = 'index', $columns = [], $model = null)
	{
		$this->writeLangFiles($columns, $model, $name);

		$basePath = $this->path.'/themes/'.$themeType.'/'.$theme.'/packages/'.$this->extension->lowerVendor.'/'.$this->extension->lowerName.'/views/';

		$dir = $basePath.'grids/'.$name.'/';

		$dgCols = [];

		foreach ($columns as $column)
		{
			$dgCols[]['content'] = $column['field'];
		}

		array_push($dgCols, ['content' => 'created_at']);

		$this->dataGridColumns[] = [
			'type'    => 'a',
			'href'    => URL::toAdmin($this->extension->lowerName.'/'.strtolower(Str::plural($model))).'<%= r.id %>/edit',
			'content' => 'id',
		];

		$this->dataGridColumns = array_merge($this->dataGridColumns, $dgCols);

		$contents = [];

		foreach ($this->dataGridTemplates as $template)
		{
			$templateContent = $this->processDataGridTemplate($name, $this->getStub($template), $model);

			$contents[$template] = $templateContent;
		}

		foreach ($contents as $file => $content)
		{
			// Write data grid templates
			$file = str_replace('.stub', '.php', $file);

			$this->ensureDirectory($dir);

			$this->files->put($dir.$file, $content);

			// Prepare view includes
			$file = str_replace('.blade.php', '', $file);

			$includes[] = "@include('{$this->extension->lowerVendor}/{$this->extension->lowerName}::grids/{$name}/{$file}')";
		}

		$stub = $this->getStub('view-admin-index.blade.stub');

		$columns = $this->dataGridColumns;

		array_shift($columns);

		$headers = '<th><input type="checkbox" name="checkAll" id="checkAll"></th>';

		foreach ($columns as $column)
		{
			$trans = "{{{ trans('".$this->extension->lowerVendor."/".$this->extension->lowerName."::".strtolower(Str::plural($model))."/table.{$column['content']}') }}}";

			$headers .= "\n\t\t\t".'<th class="sortable" data-sort="'.$column['content'].'">'.$trans.'</th>';
		}

		$headers = ltrim($headers);

		$includes = implode("\n", $includes);

		$lowerModel = strtolower($model);

		$view = $this->prepare($stub, [
			'headers'            => $headers,
			'includes'           => $includes,
			'grid_name'          => $name,
			'lower_model'        => $lowerModel,
			'plural_lower_model' => strtolower(Str::plural($lowerModel)),
		]);

		$lowerModel = $lowerModel ?: $name;

		$viewPath = $basePath.Str::plural($lowerModel).'/';

		$this->ensureDirectory($viewPath);

		$viewPath .= $viewName.'.blade.php';

		$this->files->put($viewPath, $view);
	}

	/**
	 * Process data grid templates.
	 *
	 * @param  string $stub
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

		foreach ($this->dataGridColumns as $attributes)
		{
			$type = array_pull($attributes, 'type');

			if ($type)
			{
				if ($type === 'a')
				{
					$url = array_pull($attributes, 'href');

					$elementContent = '<%= r.' . array_pull($attributes, 'content') . ' %>';

					$link = ($this->html->decode($this->html->link('#', $elementContent, $attributes)));

					$link = str_replace('href="#"', 'href="{{ URL::toAdmin(\''.$this->extension->lowerName.'/'.strtolower(Str::plural($model)).'/<%= r.id %>/edit\') }}"', $link);

					$el[] = $link;
				}
				else if ($type === 'checkbox')
				{
					$checkBoxName = array_pull($attributes, 'name');

					$value = array_pull($attributes, 'value');

					$value = '<%= r.' . $value . ' %>';

					$el[] = ($this->html->decode($this->form->checkbox($checkBoxName, $value, null, $attributes)));
				}
			}
			else
			{
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
		$model = strtolower(Str::plural($model));

		$stub = $this->getStub('lang/en/table.stub');

		$filePath = $this->path.'/lang/en/'.strtolower(Str::plural($model)).'/';

		$this->ensureDirectory($filePath);

		$filePath .= 'table.php';

		$values['id'] = 'Id';

		foreach ($columns as $column)
		{
			$values[$column['field']] = Str::title($column['field']);
		}

		$values['created_at'] = 'Created At';

		if ($this->files->exists($filePath))
		{
			$trans = $this->files->getRequire($filePath);

			$values = array_merge($trans, $values);
		}

		$trans = $this->wrapArray($values, '');

		$content = $this->prepare($stub, [
			'fields' => rtrim($trans),
		]);

		$this->files->put($filePath, $content);
	}

}
