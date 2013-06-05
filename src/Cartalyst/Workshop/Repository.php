<?php namespace Cartalyst\Workshop;
/**
 * Part of the Themes package.
 *
 * NOTICE OF LICENSE
 *
 * Licensed under the 3-clause BSD License.
 *
 * This source file is subject to the 3-clause BSD License that is
 * bundled with this package in the LICENSE file.  It is also available at
 * the following URL: http://www.opensource.org/licenses/BSD-3-Clause
 *
 * @package    Themes
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

	/**
	 * Create a new package instance.
	 *
	 * @param  string  $vendor
	 * @param  string  $name
	 * @param  string  $author
	 * @param  string  $email
	 * @return void
	 */
	public function __construct($vendor, $name, $author, $email, $uri, $version = null, $description = null)
	{
		parent::__construct($vendor, $name, $author, $email);

		$this->uri = $uri;

		if (isset($version))
		{
			$this->version = $version;
		}

		if (isset($description))
		{
			$this->description = $description;
		}
	}

}
