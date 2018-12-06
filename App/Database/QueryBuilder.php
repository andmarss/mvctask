<?php
/**
 * Created by PhpStorm.
 * User: Zver
 * Date: 03.12.2018
 * Time: 0:07
 */

namespace App\Database;


use App\App;

class QueryBuilder
{
    protected $sql = [];

    protected static $instance;

    protected $bindings = [
        'where' => [],
        'set' => []
    ];

    /**
     * @param string $fields
     * @return $this
     *
     * Метод, указывающий, какие поля мы хотим получить при запросе в БД
     * принимает массивы, строки и аргументы, записанные через запятую, например select(id, name, email)
     */

    protected function select($fields = '*')
    {
        $this->reset();

        if(is_string($fields)) {
            $this->sql['select'] = "SELECT {$fields} ";
        } elseif (is_array($fields) && count($fields) > 0) {
            $fields = implode(', ', $fields);

            $this->sql['select'] = "SELECT ${fields} ";
        } elseif (func_num_args() > 1) {
            $fields = implode(', ', func_get_args());

            $this->sql['select'] = "SELECT ${fields} ";
        }

        return $this;
    }

    /**
     * @param $table
     * @return $this
     *
     * Записывает таблицу, из которой будет браться выборка
     */

    protected function from($table)
    {
        $this->sql['from'] = "FROM {$table}";

        return $this;
    }

    /**
     * @param $table
     * @return $this
     *
     * Записывает таблицу, с которой будем работать
     */

    protected function table($table)
    {
        $this->sql['table'] = $table;

        return $this;
    }

    /**
     * @param array $conditions
     * @return $this
     * @throws \Exception
     *
     * Принимает условия, по которому будет выполнен запрос в базу
     */

    protected function where($conditions = [])
    {
        if(!isset($this->sql['select']) && !isset($this->sql['delete']) && !isset($this->sql['update'])) {
            $this->select();
        }

        if(!isset($this->sql['from']) && !isset($this->sql['table']) && !isset($this->sql['update']) && !isset($this->sql['delete'])) {
            throw new \Exception('Необходимо указать имя таблицы, откуда будет происходить выборка данных');
        }

        if(!isset($this->sql['from']) && isset($this->sql['table'])) {
            $this->from($this->sql['table']);
        }

        $this->reset(['table']);

        $operators = ['=', '>', '<', '<=', '>=', '<>'];

        if (func_num_args() === 3) { // where(field,operator,value)

            $field = func_get_arg(0);
            $operator = func_get_arg(1);
            $value = func_get_arg(2);

            if(in_array($operator, $operators)) {
                $this->sql['where'][] = "{$field} {$operator} {$this->escape($value)}";
            }

        } elseif (is_array($conditions) && count($conditions) === 3) { // where([field,operator,value])

            $field = $conditions[0];
            $operator = $conditions[1];
            $value = $conditions[2];

            if(in_array($operator, $operators)) {
                $this->sql['where'][] = "{$field} {$operator} {$this->escape($value)}";
            }

        } elseif (is_array($conditions) && count($conditions) !== 3 && count($conditions) > 0) {

            $where = implode(' AND ', array_map(function ($key, $value){
                return "$key = {$this->escape($value)}";
            }, array_keys($conditions), array_values($conditions)));

            $this->sql['where'][] = $where;

        }

        return $this;
    }

    /**
     * @param $field
     * @param $value
     * @param null $selectedField
     * @return $this
     * @throws \Exception
     *
     * Ищет поле $fieldName во вхождении $value
     *
     * $value может быть функцией, возвращающей выборку, вхождение которой будет проверять whereIn
     *
     * $value и $selectedField могуть быть строками, где $value будет именем таблицы, откуда будет просиходить выборка,
     * тогда выборка будет выглядеть как WHERE $fieldName in (SELECT $selectedField from $value)
     *
     * $value может быть массивом, тогда выборка будет выглядеть, например, как WHERE $fieldName in ([1,2,3,4,5])
     */

    protected function whereIn($field, $value, $selectedField = null)
    {
        if(!isset($this->sql['select']) && !isset($this->sql['delete']) && !isset($this->sql['update'])) {
            $this->select();
        }

        if(!isset($this->sql['from']) && !isset($this->sql['table']) && !isset($this->sql['update']) && !isset($this->sql['delete'])) {
            throw new \Exception('Необходимо указать имя таблицы, откуда будет происходить выборка данных');
        }

        if(!isset($this->sql['from']) && isset($this->sql['table'])) {
            $this->from($this->sql['table']);
        }

        if(is_callable($value)) {

            $this->sql['where'][] = "{$field} IN ({$value((new self()))})";

        } elseif (is_string($value) && is_string($selectedField)) {

            $this->sql['where'][] = "{$field} IN (SELECT {$selectedField} FROM {$value})";

        } elseif (is_array($value)) {

            $values = implode(', ', array_values($value));
            $this->sql['where'][] = "{$field} IN ({$values})";

        }

        return $this;
    }

    /**
     * @param array $condition
     * @return $this
     * @throws \Exception
     *
     * Уточнящий метод
     * Дополняет существующий sql запрос, добавляя OR и условия, который переданы в массиве $condition
     */

    protected function orWhere($condition = [])
    {
        if(!isset($this->sql['select']) && !isset($this->sql['delete']) && !isset($this->sql['update'])) {
            $this->select();
        }

        if(!isset($this->sql['from']) && !isset($this->sql['table']) && !isset($this->sql['update']) && !isset($this->sql['delete'])) {
            throw new \Exception('Необходимо указать имя таблицы, откуда будет происходить выборка данных');
        }

        if(!isset($this->sql['from']) && isset($this->sql['table'])) {
            $this->from($this->sql['table']);
        }

        if (count($this->sql['where']) > 0) {

            $condition = implode(' AND ', array_map(function ($key, $value){
                return "$key = {$this->escape($value)}";
            }, array_keys($condition), array_values($condition)));

            $this->sql['where'][] = ' OR ' . $condition;

        } else {
            throw new \Exception('Сперва должен быть выбран метод "where"');
        }

        return $this;
    }

    /**
     * @param array $conditions
     * @return $this
     * @throws \Exception
     *
     * Устанавливает соответствие символьной строки с шаблоном
     * Шаблон функция создает сама
     * Достаточно просто написать whereLike([name => 'Вася'])
     */

    protected function whereLike($conditions = [])
    {
        if(!isset($this->sql['select']) && !isset($this->sql['delete']) && !isset($this->sql['update'])) {
            $this->select();
        }

        if(!isset($this->sql['from']) && !isset($this->sql['table']) && !isset($this->sql['update']) && !isset($this->sql['delete'])) {
            throw new \Exception('Необходимо указать имя таблицы, откуда будет происходить выборка данных');
        }

        if(!isset($this->sql['from']) && isset($this->sql['table'])) {
            $this->from($this->sql['table']);
        }

        if (is_array($conditions) && count($conditions) !== 3 && count($conditions) > 0) {

            $where = implode(' AND ', array_map(function ($key, $value){
                return "$key LIKE {$this->escape('%' . $value . '%')}";
            }, array_keys($conditions), array_values($conditions)));

            $this->sql['where'][] = $where;

        }

        return $this;
    }

    /**
     * @param array $conditions
     * @return $this
     * @throws \Exception
     *
     * Уточняющий метод, устанавливающий соответствие символьной строки с шаблоном, и добавляющий ключевое слово OR перед шаблоном
     * Шаблон функция создает сама
     * Достаточно просто написать orWhereLike([name => 'Вася'])
     */

    protected function orWhereLike($conditions = [])
    {
        if(!isset($this->sql['select']) && !isset($this->sql['delete']) && !isset($this->sql['update'])) {
            $this->select();
        }

        if(!isset($this->sql['from']) && !isset($this->sql['table']) && !isset($this->sql['update']) && !isset($this->sql['delete'])) {
            throw new \Exception('Необходимо указать имя таблицы, откуда будет происходить выборка данных');
        }

        if(!isset($this->sql['from']) && isset($this->sql['table'])) {
            $this->from($this->sql['table']);
        }

        if (count($this->sql['where']) > 0) {

            $condition = implode(' AND ', array_map(function ($key, $value){
                return "$key LIKE {$this->escape('%' . $value . '%')}";
            }, array_keys($conditions), array_values($conditions)));

            $this->sql['where'][] = ' OR ' . $condition;

        } else {
            throw new \Exception('Сперва должен быть выбран метод "where"');
        }

        return $this;
    }

    /**
     * @param string $field
     * @return $this
     *
     * Устанавливает, по какому полю будет происходить сортировка
     */

    protected function orderBy($field = 'id')
    {
        $this->sql['order_by'] = " ORDER BY {$field}";

        return $this;
    }

    /**
     * Устанавливает способом сортировки - сортировку по возрастанию
     *
     * @return $this
     */

    protected function asc()
    {
        $this->sql['asc'] = ' ASC';

        return $this;
    }

    /**
     * Устанавливает способом сортировки - сортировку по убыванию
     *
     * @return $this
     */

    protected function desc()
    {
        $this->sql['desc'] = ' DESC';

        return $this;
    }

    /**
     * @param $firstRow
     * @param null $lastRow
     * @return $this
     *
     * Возвращает лимитированные данные
     * Если указан только $fristRow - выводит то количество записей, которое указано в параметре $firstRow
     * Иначе - в диапазоне $firstRow - $lastRow
     */

    protected function limit($firstRow, $lastRow = null)
    {
        if (!is_null($lastRow)) {
            $this->sql['limit'] = " LIMIT {$firstRow}, {$lastRow}";
        } elseif (is_null($lastRow)) {
            $this->sql['limit'] = " LIMIT {$firstRow}";
        }

        return $this;
    }

    /**
     * @param $table
     * @return $this
     *
     * Устанавливает имя таблицы, которую требуется обновить
     */

    protected function update($table)
    {
        $this->reset();

        $this->sql['update'] = "UPDATE {$table} ";

        return $this;
    }

    /**
     * @param array $values
     * @return $this
     *
     * Устанавливает поля, которые будут обновлены в выбранной таблице
     */

    protected function set($values = [])
    {
        $this->sql['set'] .= "SET ";
        $value = '';

        if(count($values) > 0) {
            $value = implode(', ', array_map(function($field, $value) {
                return $field . ' = ' . $this->escape($value);
            }, array_keys($values), array_values($values)));
        }

        $this->sql['set'] .= $value;

        return $this;
    }

    /**
     * @param $data
     * @return $this
     * @throws \Exception
     *
     * Устанавливает, какие поля и какие значения будут записаны в таблицу
     */

    protected function insert($data)
    {
        if(!isset($this->sql['from']) && !$this->sql['table']) {
            throw new \Exception('Сперва должна быть объявлена таблица, куда будет происходить загрузка данных');
        }

        $table = '';

        if(isset($this->sql['from']) && !isset($this->sql['table'])) {
            $table = $this->sql['from'];
        }

        if(isset($this->sql['table'])) {
            $table = $this->sql['table'];
        }

        $this->reset();

        $this->sql['insert'] = "INSERT INTO " . $table . " (" . implode(', ', array_keys($data)) . ") VALUES (" . implode(', ', array_values($data)) . ')';

        return $this;
    }

    /**
     * @param null $table
     * @return $this
     * @throws \Exception
     *
     * Устанавливает имя таблицы, из которой необходимо что либо удалить
     */

    protected function delete($table = null)
    {
        if(is_null($table)) {
            if(isset($this->sql['from'])) {
                $table = $this->sql['from'];
            }

            if(isset($this->sql['table'])) {
                $table = $this->sql['table'];
            }

            if(!isset($this->sql['table']) && !isset($this->sql['from'])) {
                throw new \Exception('Необходимо указать имя таблицы');
            }
        }

        $this->reset();

        $this->sql['delete'] = 'DELETE FROM ' . $table;

        return $this;
    }

    /**
     * @param array $what
     * @return $this
     *
     * Сбрасывает все значения объекта по умолчанию
     */

    protected function reset($what = [])
    {

        if(count($what) > 0) {
            foreach ($what as $name) {
                unset($this->sql[$name]);
            }
        } else {
            $this->sql = [];
            $this->bindings = [
                'where' => [],
                'set' => []
            ];
        }

        return $this;
    }

    /**
     * @return string
     *
     * Возвращает sql строку
     */

    protected function get()
    {
        $sql = '';

        if(!empty($this->sql)) {
            foreach ($this->sql as $key => $value) {
                if ($key === 'where') {
                    $sql .= ' WHERE ';
                    foreach ($value as $where) {
                        $sql .= $where;
                        if (count($value) > 1) {
                            $next = next($value);

                            if($next && !preg_match('/or/i', $next)) {
                                $sql .= ' AND ';
                            }
                        }
                    }
                } else {
                    $sql .= $value;
                }
            }
        }

        return $sql;
    }

    protected function like($data)
    {
        return " LIKE %$data%";
    }

    protected function setBindings($type, $data)
    {
        if(array_key_exists($type, $this->bindings)) {
            $this->bindings[$type] = array_values(array_merge($data, $this->getBindings($type)));
        }

        return $this;
    }

    protected function getBindings($type)
    {
        return $this->bindings[$type];
    }

    /**
     * @param $string
     * @return string
     *
     * Экранирует строку
     */

    protected function escape($string)
    {
        return \App\Database\DB::escape( $string );
    }

    protected function has($name)
    {
        if(isset($this->sql[$name]) && is_string($this->sql[$name])) {
            return mb_strlen($this->sql[$name]) > 0;
        } elseif ((is_array($this->sql[$name]))) {
            return (count($this->sql[$name]) > 0);
        }
    }

    protected function getSql($name)
    {
        return isset($this->sql[$name]) ? $this->sql[$name] : null;
    }

    public static function __callStatic($method, $args)
    {
        static::$instance = new static();

        return static::$instance->$method(...$args);
    }

    public function __call($method, $args)
    {
        if (method_exists($this, $method)) {
            return $this->{$method}(...$args);
        }
    }
}