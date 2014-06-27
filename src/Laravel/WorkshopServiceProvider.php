<?php namespace Cartalyst\Workshop\Laravel;
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

use Illuminate\Support\ServiceProvider;

class WorkshopServiceProvider extends ServiceProvider {

	/**
	 * Bootstrap the application events.
	 *
	 * @return void
	 */
	public function boot()
	{
		$this->package('cartalyst/workshop', 'cartalyst/workshop', __DIR__.'/..');
	}

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
		$this->app['workshop.creator'] = $this->app->share(function($app)
		{
			return new ExtensionCreator($app['files'], $app['html'], $app['form']);
		});
	}

}
