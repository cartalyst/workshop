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
 * @version    1.0.0
 * @author     Cartalyst LLC
 * @license    Cartalyst PSL
 * @copyright  (c) 2011-2015, Cartalyst LLC
 * @link       http://cartalyst.com
 */

namespace Cartalyst\Workshop\Tests;

use PHPUnit_Framework_TestCase;
use Cartalyst\Workshop\Extension;

class ExtensionTest extends PHPUnit_Framework_TestCase
{

    /** @test */
    public function it_can_be_instantiated()
    {
        $extension = new Extension('foo_bar/baz');

        $this->assertEquals('Baz', $extension->name);
        $this->assertEquals('Foo_bar', $extension->vendor);

        $this->assertEquals('baz', $extension->lowerName);
        $this->assertEquals('foo_bar', $extension->lowerVendor);
        $this->assertEquals('Baz', $extension->studlyName);
        $this->assertEquals('FooBar', $extension->studlyVendor);
    }
}
