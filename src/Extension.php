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

namespace Cartalyst\Workshop;

use Illuminate\Support\Str;

class Extension
{
    /**
     * The vendor name of the package.
     *
     * @var string
     */
    public $vendor;

    /**
     * The snake-cased version of the vendor.
     *
     * @var string
     */
    public $lowerVendor;

    /**
     * The studly-cased version of the vendor
     *
     * @var string
     */
    public $studlyVendor;

    /**
     * The name of the package.
     *
     * @var string
     */
    public $name;

    /**
     * The snake-cased version of the package.
     *
     * @var string
     */
    public $lowerName;

    /**
     * The studly-cased version of the package
     *
     * @var string
     */
    public $studlyName;

    /**
     * The name of the author.
     *
     * @var string
     */
    public $author;

    /**
     * The email address of the author.
     *
     * @var string
     */
    public $email;

    /**
     * The description of the extension.
     *
     * @var string
     */
    public $description;

    /**
     * The version of the extension.
     *
     * @var string
     */
    public $version;

    /**
     * The required dependencies of the extension.
     *
     * @var string
     */
    public $require;

    /**
     * Create a new package instance.
     *
     * @param  string  $slug
     * @param  string  $author
     * @param  string  $email
     * @return void
     */
    public function __construct($slug, $author = null, $email = null)
    {
        $slug   = explode('/', $slug);
        $vendor = head($slug);
        $name   = last($slug);

        $this->name         = ucfirst($name);
        $this->email        = $email;
        $this->vendor       = ucfirst($vendor);
        $this->author       = $author;
        $this->lowerName    = Str::snake($name, '-');
        $this->lowerVendor  = Str::snake($vendor, '-');
        $this->studlyName   = Str::studly($name);
        $this->studlyVendor = Str::studly($vendor);
    }

    /**
     * Get the full package name.
     *
     * @return string
     */
    public function getFullName()
    {
        return $this->lowerVendor.'/'.$this->lowerName;
    }
}
