<?php namespace Cartalyst\Workshop;
/**
 * Part of the Workshop package.
 *
 * NOTICE OF LICENSE
 *
 * Licensed under the 3-clause BSD License.
 *
 * This source file is subject to the 3-clause BSD License that is
 * bundled with this package in the LICENSE file.  It is also available at
 * the following URL: http://www.opensource.org/licenses/BSD-3-Clause
 *
 * @package    Workshop
 * @version    2.0.0
 * @author     Cartalyst LLC
 * @license    BSD License (3-clause)
 * @copyright  (c) 2011 - 2013, Cartalyst LLC
 * @link       http://cartalyst.com
 */

use Illuminate\Workbench\Package;

class Repository extends Package {

	public $uri;

	public $version = '0.1.0';

	public $description = '';

	public $require = array();

	public function formatUriAttribute($uri)
	{
		if ( ! is_string($uri) and ! is_numeric($uri))
		{
			return 'null';
		}

		return $this->exportVar($uri);
	}

	public function formatRequireAttribute(array $require)
	{
		if (empty($require))
		{
			return 'array()';
		}

		return $this->exportVar($require, 1);
	}

	/**
	 * Formats attributes for use within stubs.
	 */
	public function getFormattedAttributes()
	{
		$me = $this;
		$attributes = get_object_vars($me);

		array_walk($attributes, function(&$value, $key) use ($me)
		{
			if (method_exists($me, $method = 'format'.studly_case($key).'Attribute'))
			{
				$value = $me->$method($value);
			}
		});

		return $attributes;
	}

	/**
	 * Takes var export and tidies it up.
	 */
	protected function exportVar($var, $indentNewLines = 0)
	{
		$replacements = array(
			'/[ ]{2}/' => "\t",
			'/array \(/' => 'array(',
			'/[0-9]+ =\> /' => '',
		);

		$exported = var_export($var, true);
		$exported = preg_replace(array_keys($replacements), array_values($replacements), $exported);

		if ($indentNewLines > 0)
		{
			$exported = explode("\n", $exported);
			array_walk($exported, function(&$line, $key) use ($indentNewLines)
			{
				if ($key > 0) $line = str_repeat("\t", $indentNewLines).$line;
			});
			$exported = implode("\n", $exported);
		}

		return $exported;
	}

}
