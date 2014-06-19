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

class DataGridGenerator extends Generator {

	protected $dataGridTemplates = [
		'results.blade.stub',
		'filters.blade.stub',
		'pagination.blade.stub',
		'no_results.blade.stub',
		'no_filters.blade.stub',
	];

	protected $dataGridColumns = [
		[
			'type'  => 'checkbox',
			'name'  => 'entries[]',
			'value' => 'id',
		],
	];

	public function create($name, $themeType = 'admin', $theme = 'default', $viewName = 'index', $columns = [])
	{
		$this->dataGridColumns[] = [
			'type'    => 'a',
			'href'    => URL::toAdmin($this->extension->lowerName).'<%= r.id %>',
			'content' => 'id',
		];

		$this->dataGridColumns = array_merge($this->dataGridColumns, $columns);

		$contents = [];

		foreach ($this->dataGridTemplates as $template)
		{
			$contents[$template] = $this->processDataGridTemplate($name, $this->files->get($this->stubsPath.$template));
		}

		$basePath = $this->path.'/themes/'.$themeType.'/'.$theme.'/packages/'.$this->extension->lowerVendor.'/'.$this->extension->lowerName.'/views/';

		$dir = $basePath . 'grids/'.$name.'/';

		foreach ($contents as $file => $content)
		{
			$file = str_replace('.stub', '.php', $file);

			if ( ! $this->files->isDirectory($dir))
			{
				$this->files->makeDirectory($dir, 0777, true);
			}

			$this->files->put($dir.$file, $content);

			$file = str_replace('.blade.php', '', $file);

			$includes[] = "@include('{$this->extension->lowerVendor}/{$this->extension->lowerName}::grids/{$name}/{$file}')";
		}

		$stub = $this->stubsPath.'view-datagrid-index.blade.stub';

		$includes = implode("\n", $includes);

		$headers = ("<th>".implode("</th>\n\t\t\t<th class=\"sortable\">", $this->prepareColumns(false)).'</th>');

		$view = str_replace(["{{headers}}", "{{includes}}", "{{name}}"], [$headers, $includes, $name], $this->files->get($stub));

		foreach ((array) $this->extension as $key => $value)
		{
			$view = str_replace('{{'.snake_case($key).'}}', $value, $view);
		}

		$viewPath = $basePath.$viewName.'.blade.php';

		$this->files->put($viewPath, $view);
	}

	/**
	 * {@inheritDoc}
	 */
	public function prepare($path, $args = [])
	{
		return;
	}

	/**
	 * Process data grid templates.
	 *
	 * @param  string $content
	 * @return string
	 */
	protected function processDataGridTemplate($name, $content)
	{
		$el = $this->prepareColumns();

		$columns = ("<td>".implode("</td>\n\t\t\t<td>", $el).'</td>');

		$rows = count(head($this->dataGridColumns)) + 1;

		return str_replace(["{{columns}}", "{{name}}", "{{rows}}"], [$columns, $name, $rows], $content);
	}

	/**
	 * Prepare data grid columns.
	 *
	 * @param  bool  $results
	 * @return array
	 */
	protected function prepareColumns($results = true)
	{
		$el = [];

		foreach ($this->dataGridColumns as $attributes)
		{
			$type = array_pull($attributes, 'type');

			if ($type)
			{
				if ($type === 'a')
				{
					if ($results)
					{
						$url = array_pull($attributes, 'href');

						$elementContent = '<%= r.' . array_pull($attributes, 'content') . ' %>';

						$link = ($this->html->decode($this->html->link('#', $elementContent, $attributes)));

						$link = str_replace('href="#"', 'href="{{ URL::toAdmin(\''.$this->extension->lowerName.'/<%= r.id %>\') }}"', $link);

						$el[] = $link;
					}
					else
					{
						$value = array_pull($attributes, 'content');

						$content = "{{{ trans('{{lower_vendor}}/{{lower_name}}::table.{$value}') }}}";

						$el[] = $content;
					}
				}
				else if ($type === 'checkbox')
				{
					$checkBoxName = array_pull($attributes, 'name');

					$value = array_pull($attributes, 'value');

					if ($results)
					{
						$value = '<%= r.' . $value . ' %>';

						$el[] = ($this->html->decode($this->form->checkbox($checkBoxName, $value, null, $attributes)));
					}
					else
					{
						$attributes['name'] = 'checkAll';
						$attributes['id']   = 'checkAll';

						$el[] = ($this->html->decode($this->form->checkbox($checkBoxName, null, null, $attributes)));
					}
				}
			}
			else
			{
				if ($results)
				{
					$el[] = '<%= r.' . array_pull($attributes, 'content') . ' %>';
				}
				else
				{
					$value = array_pull($attributes, 'content');

					$el[] = "{{{ trans('{{lower_vendor}}/{{lower_name}}::table.{$value}') }}}";
				}
			}
		}

		return $el;
	}

}
