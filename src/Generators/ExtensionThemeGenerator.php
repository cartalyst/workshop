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
 * @version    5.0.0
 * @author     Cartalyst LLC
 * @license    Cartalyst PSL
 * @copyright  (c) 2011-2019, Cartalyst LLC
 * @link       https://cartalyst.com
 */

namespace Cartalyst\Workshop\Generators;

class ExtensionThemeGenerator extends AbstractGenerator
{
    /**
     * Writes the theme directories.
     *
     * @param string $area
     * @param string $theme
     *
     * @return void
     */
    public function create(string $area, string $theme = 'default'): void
    {
        $base = [
            'assets/js'  => 'script.js',
            'assets/css' => 'style.css',
            'views'      => '.gitkeep',
        ];

        $themeDirectory = $this->getFullPath("resources/themes/{$area}/{$theme}/packages/{$this->extension->lowerVendor}/{$this->extension->lowerName}/");

        foreach ($base as $dir => $file) {
            $this->ensureDirectory($themeDirectory.$dir);

            $this->files->put($themeDirectory.$dir.'/'.$file, null);
        }
    }
}
