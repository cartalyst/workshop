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

namespace Cartalyst\Workshop\Generators;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class FormGenerator extends AbstractGenerator
{
    /**
     * Create a new form.
     *
     * @param string $model
     * @param array  $columns
     * @param string $view
     *
     * @return void
     */
    public function create(string $model, array $columns = [], string $view = 'form'): void
    {
        $model = $this->sanitize($model);

        $lowerModel  = Str::lower($model);
        $pluralModel = Str::plural($model);

        $camelLowerModel  = Str::camel($lowerModel);
        $pluralLowerModel = Str::plural($lowerModel);
        $lowerPluralModel = Str::lower($pluralModel);

        $this->writeLangFiles($columns, $model);

        $stub = $this->getStub('form.blade.stub');

        $el = [];

        foreach ($columns as $col) {
            $col['field'] = $this->sanitize($col['field'], '/[^a-zA-Z0-9_-]/');

            switch ($col['type']) {
                case 'text':
                case 'mediumText':
                case 'longText':
                    $inputStub = $this->getStub('form-textarea.stub');

                    break;
                case 'boolean':
                case 'tinyInteger':
                    $inputStub = $this->getStub('form-checkbox.stub');

                    break;
                case 'string':
                default:
                    $inputStub = $this->getStub('form-input.stub');

                    break;
            }

            $el[] = $this->prepare($inputStub, [
                'field_name'         => $col['field'],
                'camel_model'        => $camelLowerModel,
                'plural_lower_model' => $lowerPluralModel,
            ]);
        }

        $content = $this->prepare($stub, [
            'columns'            => implode("\n\t\t\t\t\t\t\t\t", $el),
            'camel_model'        => $camelLowerModel,
            'plural_lower_model' => $lowerPluralModel,
        ]);

        $filePath = $this->getFullPath('resources/themes/admin/default/packages/'.$this->extension->lowerVendor.'/'.$this->extension->lowerName.'/views/'.$pluralLowerModel.'/');

        $this->ensureDirectory($filePath);

        $this->files->put($filePath.$view.'.blade.php', $content);
    }

    /**
     * Writes the form language file.
     *
     * @param array  $columns
     * @param string $model
     *
     * @return void
     */
    protected function writeLangFiles(array $columns, string $model): void
    {
        $stub = $this->getStub('lang/en/model.stub');

        $values = [];

        foreach ($columns as $column) {
            $values[$column['field']]         = Str::title($column['field']);
            $values[$column['field'].'_help'] = 'Enter the '.Str::title($column['field']).' here';
        }

        $filePath = $this->getFullPath('resources/lang/en/'.Str::lower(Str::plural($model)).'/');

        $this->ensureDirectory($filePath);

        $filePath .= 'model.php';

        if ($this->files->exists($filePath)) {
            $trans = $this->files->getRequire($filePath);

            $values = array_merge($values, Arr::get($trans, 'general'));
        }

        $trans = $this->wrapArray($values, "\t");

        $content = $this->prepare($stub, [
            'fields' => trim($trans),
        ]);

        $this->files->put($filePath, $content);
    }
}
