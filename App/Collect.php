<?php

namespace App;

use App\Interfaces\ArrayAccess;

/**
 * Class Collect
 * @package App
 *
 * Класс для работы с массивами, как с коллекцией
 * предоставляет удобный интерфейс для работы с ними
 */

class Collect implements \Countable, ArrayAccess
{
    protected $collection;

    public function __construct($countable = [])
    {
        $this->collection = $countable;
    }

    /**
     * @param \Closure $func
     * @return $this
     *
     * Аналог foreach
     */

    public function each(\Closure $func)
    {
        foreach ($this->collection as $key => $item) {
            $func($item, $key, $this->collection);
        }

        return $this;
    }

    /**
     * @param \Closure $func
     * @return $this
     *
     * Аналог array_map
     */

    public function map(\Closure $func)
    {
        $result = [];

        foreach ($this->collection as $key => $item) {
            $result[] = $func($item, $key, $this->collection);
        }

        $this->collection = $result;

        return $this;
    }

    /**
     * @param \Closure $func
     * @return $this
     *
     * Аналог array_filter
     */

    public function filter(\Closure $func)
    {
        $result = [];

        foreach ($this->collection as $key => $item) {
            if($func($item, $key, $this->collection)) {
                $result[] = $item;
            }
        }

        $this->collection = $result;

        return $this;
    }

    /**
     * @param $condition
     * @param bool $strict
     * @return int
     *
     * Возвращает количество совпавших в колекции по условию
     * Если condition - функция - вызывает ей в контексте функции filter
     */

    public function search($condition, $strict = false)
    {
        $find = 0;

        if(!is_callable($condition)) {
            if($strict) {
                foreach ($this->collection as $key => $item) {
                    if($condition === $item) {
                        $find++;
                    }
                }
            } else {
                foreach ($this->collection as $key => $item) {
                    if($condition == $item) {
                        $find++;
                    }
                }
            }

            return $find;
        } elseif (is_callable($condition)) {
            return $this->filter($condition)->count();
        }
    }

    /**
     * @param \Closure $func
     * @return $this
     *
     * Обратно действию функции filter
     */

    public function reject(\Closure $func)
    {
        $result = [];

        foreach ($this->collection as $key => $item) {
            if(!$func($item, $key, $this->collection)) {
                $result[] = $item;
            }
        }

        $this->collection = $result;

        return $this;
    }

    /**
     * @param \Closure $func
     * @param $initial
     * @return $this|mixed
     *
     * аналог array_reduce
     */

    public function reduce(\Closure $func, $initial)
    {
        $accumulator = $initial;

        foreach ($this->collection as $key => $item) {
            $accumulator = $func($accumulator, $item);
        }

        if(is_array($accumulator)) {
            $this->collection = $accumulator;

            return $this;
        }

        return $accumulator;
    }

    /**
     * @param \Closure $func
     * @return Collect|mixed
     *
     * Суммирует значения колекции, и возвращает это значение
     */

    public function sum(\Closure $func)
    {
        return $this->reduce(function ($total, $item) use ($func){
            return $total + $func($item);
        }, 0);
    }

    /**
     * @return int
     *
     * Возвращает количество элементов в колекции
     */

    public function count()
    {
        return count($this->collection);
    }

    /**
     * @param $items
     * @return static
     *
     * Превращает массив в объект-экземпляр класса Collect
     */

    public static function make($items)
    {
        return (new static($items));
    }

    public function offsetExists($offset)
    {
        return array_key_exists($offset, $this->collection);
    }

    public function offsetGet($offset)
    {
        return $this->collection[$offset];
    }

    public function offsetSet($offset, $value)
    {
        if(!is_null($offset)) {
            $this->collection[] = $offset;
        } else {
            $this->collection[$offset] = $value;
        }
    }

    public function offsetUnset($offset)
    {
        unset($this->collection[$offset]);
    }

    /**
     * @param \Closure $func
     * @return bool
     *
     * Принимает функцию
     *
     * Перебирает коллекцию, возвращает true, если все условия, выполненые в функции func - верны
     * Иначе - false
     */

    public function every(\Closure $func)
    {
        foreach ($this->collection as $key => $item) {
            if(!$func($item, $key, $this->collection)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param \Closure $func
     * @return bool
     *
     * Перебирает коллекцию, возвращает true, если все хотя бы одно условие, выполненое в функции func - верно
     * Иначе - false
     */

    public function some(\Closure $func)
    {
        $i = 0;

        foreach ($this->collection as $key => $item) {
            if($func($item, $key, $this->collection)) {
                $i++;
            }
        }

        return $i !== 0;
    }

    /**
     * @return array
     *
     * Вернуть массив коллекции
     */

    public function get()
    {
        return $this->collection;
    }

    /**
     * @return $this
     *
     * Аналог array_keys
     */

    public function values()
    {
        $this->collection = array_values($this->collection);

        return $this;
    }

    /**
     * @return $this
     *
     * Аналог array_values
     */

    public function keys()
    {
        $this->collection = array_keys($this->collection);

        return $this;
    }

    /**
     * @param int $num
     * @return $this
     *
     * Аналог array_chunk
     */

    public function chunk(int $num)
    {
        $this->collection = array_chunk((array) $this->collection, $num);

        return $this;
    }

    /**
     * @return mixed|null
     *
     * Возвращает первый элемент коллекции, или null
     */

    public function first()
    {
        return isset($this->collection[0]) ? $this->collection[0] : null;
    }

    /**
     * @return array|null
     *
     * Возвращает последний элемент коллекции, или null
     */

    public function last()
    {
        return count((array) $this->collection) > 0 ? array_slice((array) $this->collection, -1) : null;
    }

    /**
     * @param int $from
     * @param int $to
     *
     * Аналог array_slice
     */

    public function slice($from = 0, $to = 1)
    {
        $this->collection = array_slice((array) $this->collection, $from, $to);
    }
}