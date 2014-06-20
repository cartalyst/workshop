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

class ExtensionThemeGenerator extends Generator {

	/**
	 * Writes the theme directories.
	 *
	 * @param  string  $location
	 * @param  string  $theme
	 * @return void
	 */
	public function create($location, $theme = 'default')
	{
		// Frontend packages
		array_set($this->blocks, "themes.{$location}.{$theme}", [
			'packages' => [
				$this->extension->lowerVendor => [
					$this->extension->lowerName => [
						'assets' => [
							'js' => [
								'script.js',
							],
							'css' => [
								'style.css',
							],
						],
						'views' => [
							'.gitkeep',
						],
					],
				],
			],
		]);

		$this->process(null, null, [
			'location' => $location,
		]);
	}

}
