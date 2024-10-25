<?php

/**
 * @package MiniCollection
 * @description This class is made for accessing associated array's value more elegantly and easily
 * @author techvillage <support@techvill.org>
 * @contributor Muhammad AR Zihad <[zihad.techvill@gmail.com]>
 * @created 21-11-2022
 */

namespace App\Http\Helpers;

use ArrayAccess;
use ArrayIterator;
use Illuminate\Contracts\Support\Arrayable;
use IteratorAggregate;

class MiniCollection implements ArrayAccess, Arrayable, IteratorAggregate
{

    /**
     * Stores data in array
     */
    private $data = [];

    /**
     * Flag for building nested collection
     */
    private $hasNested = false;


    /**
     * Constructor of the class
     * @param array $hayStack Default []
     */
    public function __construct($hayStack = [], $hasNested = false)
    {
        $this->hasNested = $hasNested;
        $this->merge($hayStack);
    }


    /**
     * Merge data with the given array
     * @param array|null $hayStack
     * @return App\Lib\MiniCollection
     */
    public function merge($hayStack = null, $hasNested = false)
    {
        $this->hasNested = $this->hasNested || $hasNested;
        if (is_array($hayStack)) {
            if ($this->hasNested) {
                $hayStack = $this->compileNestedArray($hayStack);
            }
            $this->data = array_merge($this->data, $hayStack);
            return $this;
        }
        throw new \Exception(sprintf("Parameter {%s} array expected and %s given.", '$hayStack', gettype($hayStack)));
    }


    /**
     * Modify the nested array into array of miniCollection
     * @param array $array
     * @return array
     */
    private function compileNestedArray($array = [])
    {
        $data = $this->processData($array);
        if ($data instanceof MiniCollection) {
            return $data->toArray();
        }
        return $array;
    }


    /**
     * Compile array data into miniCollection
     * @param array $array
     * @return self
     */
    private function processData($array = [])
    {
        if (is_array($array)) {
            foreach ($array as $key => $item) {
                if (is_array($item)) {
                    $array[$key] = $this->processData($item);
                }
            }
            return new static($array);
        }
        return $array;
    }


    /**
     * Set element to the data associated with the key
     * @param string $key Array key
     * @return mixed
     */
    public function __set($key, $value)
    {
        if (is_null($key)) {
            $this->data[] = $value;
        } else {
            $this->data[$key] = $value;
        }
    }


    /**
     * Get element from the data by key
     * @param string $key Array key
     * @return mixed
     */
    public function __get($key)
    {
        if (isset($this->data[$key])) {
            return $this->data[$key];
        }
        return null;
    }


    /**
     * Check if a key is available in the data
     * @param string $name key of the element
     * @return bool
     */
    public function __isset($name)
    {
        return isset($this->data[$name]);
    }


    /**
     * Unset element form array
     * @param string $name key of the element
     * @return void
     */
    public function __unset($name)
    {
        if (isset($this->data[$name])) {
            unset($this->data[$name]);
        }
    }


    /**
     * Set element to the data associated with the key
     * @param string $key Array key
     * @param string $value Array value
     * @return void
     */
    public function offsetSet($key, $value): void
    {
        $this->__set($key, $value);
    }


    /**
     * Check if a key is available in the data
     * @param string $name key of the element
     * @return bool
     */
    public function offsetExists($name): bool
    {
        return $this->__isset($name);
    }


    /**
     * Unset element from data array
     * @param string $name key of the element
     * @return void
     */
    public function offsetUnset($name): void
    {
        $this->__unset($name);
    }


    /**
     * Get element from the data by key
     * @param string $key Array key
     * @return mixed
     */
    public function offsetGet($key = null)
    {
        if ($key == null) {
            return null;
        }
        return $this->__get($key);
    }


    /**
     * Make the object iterable
     * @return array
     */
    public function toArray()
    {
        if (!$this->hasNested) {
            return $this->data;
        }
        return $this->decompileArray($this->data);
    }


    protected function decompileArray($array)
    {
        foreach ($array as $index => $arrayItem) {
            if ($arrayItem instanceof self) {
                $array[$index] = $this->decompileArray($arrayItem->toArray());
            }
        }
        return $array;
    }


    /**
     * Let the data iterate through iterator functions
     * @return ArrayIterator
     */
    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->data);
    }


    /**
     * Returns only associative array containing the given key
     *
     * @param array $keys
     *
     * @return array
     */
    public function only(array $keys = [])
    {
        $picked = [];
        foreach ($keys as $key) {
            if (isset($this->data[$key])) {
                $picked[$key] = $this->data[$key];
            }
        }
        if ($this->hasNested) {
            return $this->decompileArray($picked);
        }
        return $picked;
    }
}
