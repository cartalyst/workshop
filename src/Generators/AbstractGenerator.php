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

namespace Cartalyst\Workshop\Generators;

use LogicException;
use Illuminate\Support\Str;
use Cartalyst\Workshop\Extension;

abstract class AbstractGenerator
{
    /**
     * Platform extension instance.
     *
     * @var \Cartalyst\Workshop\Extension
     */
    protected $extension;

    /**
     * Filesystem instance.
     *
     * @var \Illuminate\Filesystem\Filesystem
     */
    protected $files;

    /**
     * Extension path.
     *
     * @var string
     */
    protected $path;

    /**
     * Stubs directory.
     *
     * @var string
     */
    protected static $stubsDir;

    /**
     * Default stubs directory.
     *
     * @var string
     */
    protected $defaultStubsDir;

    /**
     * Constructor.
     *
     * @param  \Cartalyst\Workshop\Extension|string  $extension
     * @param  \Illuminate\Filesystem\Filesystem  $files
     * @return void
     */
    public function __construct($extension, $files)
    {
        if (is_string($extension)) {
            $this->extension = new Extension($extension);
        } else {
            $this->extension = $extension;
        }

        $this->files = $files;

        $this->path = __DIR__.str_repeat('/..', 5).'/workbench/'.$this->extension->getFullName();

        if (! $this->files->isDirectory($this->path)) {
            $this->path = str_replace('workbench', 'extensions', $this->path);
        }

        $this->defaultStubsDir = __DIR__.'/..'.str_replace($this->path, '/stubs/', $this->path);
    }

    /**
     * Sets the stubs directory.
     *
     * @param  string  $dir
     * @return void
     */
    public static function setStubsDir($dir)
    {
        static::$stubsDir = $dir;
    }

    /**
     * Returns the stubs directory.
     *
     * @return string
     */
    public static function getStubsDir()
    {
        return static::$stubsDir;
    }

    /**
     * Returns the stub file path.
     *
     * @param  string  $path
     * @return string
     */
    public function getStub($path)
    {
        $overriddenPath = static::$stubsDir.DIRECTORY_SEPARATOR.$path;

        if ($this->files->exists($overriddenPath)) {
            return $overriddenPath;
        }

        return $this->defaultStubsDir.$path;
    }

    /**
     * {@inheritDoc}
     */
    public function prepare($path, $args = [])
    {
        $content = $this->files->get($path);

        foreach ((array) $this->extension as $key => $value) {
            $content = str_replace('{{'.Str::snake($key).'}}', $value, $content);
        }

        foreach ($args as $key => $value) {
            $content = str_replace('{{'.Str::snake($key).'}}', $value, $content);
        }

        return $content;
    }

    /**
     * Ensure the directory exists or create it.
     *
     * @param  string  $path
     * @return void
     */
    protected function ensureDirectory($path)
    {
        $path = str_replace('/', DIRECTORY_SEPARATOR, $path);

        if (! $this->files->isDirectory($path)) {
            $this->files->makeDirectory($path, 0777, true);
        }
    }

    /**
     * Wraps an array for text output.
     *
     * @param  array  $array
     * @param  string  $indentation
     * @return string
     */
    protected function wrapArray($array, $indentation = null)
    {
        $self = $this;

        $indentation = $indentation . "\t";

        array_walk($array, function ($value, $key) use ($indentation, &$text, $self) {
            if (is_array($value)) {
                if (! is_numeric($key)) {
                    $text .= $indentation."'".$key."' => [\n\t";
                } else {
                    $text .= $indentation."[\n\t";
                }

                $text .= $indentation.$self->wrapArray($value, $indentation) . "\n";

                $text .= $indentation."],\n";
            }

            if (is_string($value) && is_string($key)) {
                $text .= $indentation."'".$key."' => '".$value."',\n";
            }
        });

        return trim($text);
    }

    /**
     * Returns the extension.php file path.
     *
     * @return string
     * @throws \LogicException
     */
    protected function getExtensionPhpPath()
    {
        $path = $this->path.'/extension.php';

        if (! $this->files->exists($path)) {
            $path = str_replace('workbench', 'extensions', $path);

            if (! $this->files->exists($path)) {
                throw new LogicException('extension.php could not be found.');
            }
        }

        return $path;
    }

    /**
     * Sanitizes a string.
     *
     * @param  string|array  $element
     * @return string
     */
    public static function sanitize($element, $pattern = '/[^a-zA-Z0-9]/')
    {
        if (is_array($element)) {
            $newArray = [];

            foreach ($element as $key => $string) {
                $key = static::sanitize($key, $pattern);
                $string = static::sanitize($string, $pattern);

                $newArray[$key] = $string;
            }

            return $newArray;
        }

        return preg_replace($pattern, '', (string) $element);
    }
}
