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
 * @version    6.1.0
 * @author     Cartalyst LLC
 * @license    Cartalyst PSL
 * @copyright  (c) 2011-2020, Cartalyst LLC
 * @link       https://cartalyst.com
 */

namespace Cartalyst\Workshop\Generators;

use LogicException;
use Illuminate\Support\Str;
use Cartalyst\Workshop\Extension;
use Illuminate\Filesystem\Filesystem;

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
     * The base path where the the generated Extension will be stored.
     *
     * @var string
     */
    protected $basePath;

    /**
     * The stubs directory.
     *
     * @var string
     */
    protected static $stubsDir;

    /**
     * Constructor.
     *
     * @param \Cartalyst\Workshop\Extension|string $extension
     * @param \Illuminate\Filesystem\Filesystem    $files
     *
     * @return void
     */
    public function __construct($extension, Filesystem $files)
    {
        if (is_string($extension)) {
            $this->extension = new Extension($extension);
        } else {
            $this->extension = $extension;
        }

        $this->files = $files;

        $this->basePath = __DIR__.str_repeat('/..', 5);
    }

    /**
     * Sets the stubs directory.
     *
     * @param string $dir
     *
     * @return void
     */
    public static function setStubsDir(string $dir): void
    {
        static::$stubsDir = $dir;
    }

    /**
     * Returns the stubs directory.
     *
     * @return string
     */
    public static function getStubsDir(): string
    {
        return static::$stubsDir;
    }

    /**
     * Returns the stub file path.
     *
     * @param string $path
     *
     * @return string
     */
    public function getStub(string $path): string
    {
        $overriddenPath = static::$stubsDir.DIRECTORY_SEPARATOR.$path;

        if ($this->files->exists($overriddenPath)) {
            return $overriddenPath;
        }

        return __DIR__.'/../stubs/'.$path;
    }

    public function getFullPath(string $file = null): string
    {
        return $this->basePath.'/extensions/'.$this->extension->getFullName().'/'.($file ?? '');
    }

    public function setBasePath(string $basePath): void
    {
        $this->basePath = $basePath;
    }

    /**
     * Prepares the file contents.
     *
     * @param string $path
     * @param array  $args
     *
     * @return string
     */
    public function prepare(string $path, array $args = []): string
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
     * @param string $path
     *
     * @return void
     */
    protected function ensureDirectory(string $path): void
    {
        $path = str_replace('/', DIRECTORY_SEPARATOR, $path);

        if (! $this->files->isDirectory($path)) {
            $this->files->makeDirectory($path, 0777, true);
        }
    }

    /**
     * Wraps an array for text output.
     *
     * @param array       $array
     * @param string|null $indentation
     *
     * @return string
     */
    protected function wrapArray(array $array, string $indentation = null): string
    {
        $text = '';

        $indentation = $indentation."\t";

        array_walk($array, function ($value, $key) use ($indentation, &$text) {
            if (is_array($value)) {
                if (! is_numeric($key)) {
                    $text .= $indentation."'".$key."' => [\n\t";
                } else {
                    $text .= $indentation."[\n\t";
                }

                $text .= $indentation.$this->wrapArray($value, $indentation)."\n";

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
     * @throws \LogicException
     *
     * @return string
     */
    protected function getExtensionPhpPath(): string
    {
        $path = $this->getFullPath('extension.php');

        if (! $this->files->exists($path)) {
            throw new LogicException('extension.php could not be found.');
        }

        return $path;
    }

    /**
     * Sanitizes a string.
     *
     * @param array|string $element
     * @param string       $pattern
     *
     * @return array|string
     */
    public static function sanitize($element, string $pattern = '/[^a-zA-Z0-9]/')
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
