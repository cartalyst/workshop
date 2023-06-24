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

namespace Cartalyst\Workshop\Tests;

use PHPUnit\Framework\TestCase;
use Cartalyst\Workshop\Extension;

class ExtensionTest extends TestCase
{
    /** @test */
    public function it_can_be_instantiated()
    {
        $extension = new Extension('foo_bar/baz');

        $this->assertSame('Baz', $extension->name);
        $this->assertSame('Foo_bar', $extension->vendor);

        $this->assertSame('baz', $extension->lowerName);
        $this->assertSame('foo_bar', $extension->lowerVendor);
        $this->assertSame('Baz', $extension->studlyName);
        $this->assertSame('FooBar', $extension->studlyVendor);
    }
}
