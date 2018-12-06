<?php
/**
 * Created by PhpStorm.
 * User: Zver
 * Date: 24.11.2018
 * Time: 1:19
 */

namespace App\Database;

use App\App;
use App\Collect;
use App\Database\QueryBuilder;


class ORM extends Collect
{
    protected $pdo;

    public static $table;

    protected static $fillable = null;

    protected static $instance;

    protected $query;

    protected $sql;
    protected $condition;

    /**
     * @return mixed
     */
    public function __construct()
    {
        $this->pdo = App::get('database')->get_dbh();

        parent::__construct([]);

        $this->query = new QueryBuilder();

        return $this;
    }

    /**
     * @param string $field
     * @return $this
     *
     * Метод, указывающий, какие поля мы хотим получить при запросе в БД
     */

    protected function select($field = '*')
    {
        $table = static::$table;

        $this->query->select($field)->from($table);

        return $this;
    }

    /**
     * @param array $condition
     * @return $this
     *
     * Принимает условия, по которому будет выполнен запрос в базу
     */

    protected function where($condition = [])
    {
        if(!$this->query->has('select') && (!$this->query->has('from') || !$this->query->has('table'))){
            $table = static::$table;

            $this->query->select('*')->from($table)->where($condition);
        } else {
            $this->query->where($condition);
        }

        return $this;
    }

    /**
     * @param array $condition
     * @return $this
     * @throws \Error
     *
     * Уточнящий метод
     * Дополняет существующий sql запрос, добавляя OR и условия, который переданы в массиве $condition
     */

    protected function orWhere($condition = [])
    {
        if (!$this->query->has('where')) {
            throw new \Error('Сперва должна быть вызвана функция where.');
        }

        $this->query->orWhere($condition);

        return $this;
    }

    /**
     * @param $fieldName
     * @param $value
     * @param null $selectedField
     * @return $this
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

    protected function whereIn($fieldName, $value, $selectedField = null)
    {
        if(!$this->query->has('select') && (!$this->query->has('from') || !$this->query->has('table'))){
            $table = static::$table;
            $this->query->select('*')->from($table);
        }

        $this->query->whereIn($fieldName, $value, $selectedField);

        return $this;
    }

    /**
     * @param array $condition
     * @return $this
     *
     * Устанавливает соответствие символьной строки с шаблоном
     * Шаблон функция создает сама
     * Достаточно просто написать whereLike([name => 'Вася'])
     */

    protected function whereLike($condition = [])
    {
        if(!$this->query->has('select') && (!$this->query->has('from') || !$this->query->has('table'))){
            $table = static::$table;

            $this->query->select('*')->from($table)->whereLike($condition);
        } else {
            $this->query->whereLike($condition);
        }

        return $this;
    }

    /**
     * @param array $condition
     * @return $this
     *
     * Уточняющий метод, устанавливающий соответствие символьной строки с шаблоном, и добавляющий ключевое слово OR перед шаблоном
     * Шаблон функция создает сама
     * Достаточно просто написать orWhereLike([name => 'Вася'])
     */

    protected function orWhereLike($condition = [])
    {
        if(!$this->query->has('select') && (!$this->query->has('from') || !$this->query->has('table'))){
            $table = static::$table;

            $this->query->select('*')->from($table)->orWhereLike($condition);
        } else {
            $this->query->orWhereLike($condition);
        }

        return $this;
    }

    /**
     * @param array $parameters
     * @return $this
     * @throws \Error
     *
     * Добавляет в базу данных запись
     * Возвращает экземпляр созданного объекта, в контексте класса которого метод был вызван
     */

    protected function insert(array $parameters = []) {

        if(count($parameters) > 0) {

            if(is_null(static::$fillable) || !is_array(static::$fillable) || (is_array(static::$fillable) && count(static::$fillable) === 0)) {
                throw new \Error('Параметр "fillable" должен быть не пустым массиваом.');
            }

            if(is_array(static::$fillable)) {
                if(is_array($parameters) && count($parameters) > 0) {
                    foreach ($parameters as $parameter => $value) {
                        if(!in_array($parameter, static::$fillable)) {
                            throw new \Error('Нельзя добавлять поля, не внесенные в "fillable"');
                        }
                    }
                } else {
                    throw new \Error('Аргумент "parameters" должен быть НЕ пустым массивом');
                }
            }

            $fillable = [];

            foreach ($parameters as $column => $value) {
                if($column === 'id' || $column === 'sql' || $column === 'condition') continue;
                if (is_object($value) || is_array($value) || is_resource($value)) continue;

                $columns[] = $column;
                $data[':' . $column] = $this->escape($value);
                $fillable[$column] = $this->escape($value);
            }

            $table = static::$table;

            $sql = $this->query->table($table)->insert($fillable)->get();

            try {

                $statement = $this->pdo->prepare($sql);

                $result = $statement->execute();

                if($result) {
                    $statement = $this->pdo->prepare('SELECT LAST_INSERT_ID()');

                    $statement->execute();

                    return $this->find($statement->fetchColumn());
                }

            } catch (\PDOException $e) {
                die(var_dump($e->getMessage()));
            }
        } elseif ($this->isNew()) {
            $columns = [];
            $data = [];
            $fillable = [];

            foreach ($this as $column => $value) {
                if($column === 'id' || $column === 'sql' || $column === 'condition') continue;
                if (is_object($value) || is_array($value) || is_resource($value)) continue;

                $columns[] = $column;
                $data[':' . $column] = $this->escape($value);
                $fillable[$column] = $this->escape($value);
            }

            if(is_null(static::$fillable) || !is_array(static::$fillable) || (is_array(static::$fillable) && count(static::$fillable) === 0)) {
                throw new \Error('Параметр "fillable" должен быть не пустым массиваом.');
            }

            if(is_array(static::$fillable)) {
                if(is_array($fillable) && count($fillable) > 0) {
                    foreach ($fillable as $parameter => $value) {
                        if(!in_array($parameter, static::$fillable)) {
                            throw new \Error('Нельзя добавлять поля, не внесенные в "fillable"');
                        }
                    }
                } else {
                    throw new \Error('Аргумент "parameters" должен быть НЕ пустым массивом');
                }
            }

            $table = static::$table;

            $sql = $this->query->table($table)->insert($fillable)->get();

            $statement = $this->pdo->prepare($sql);

            $result = $statement->execute();

            if($result) {
                $statement = $this->pdo->prepare('SELECT LAST_INSERT_ID()');

                $statement->execute();

                return $this->find($statement->fetchColumn());
            }
        }

        return $this;
    }

    /**
     * @param array $parameters
     * @param array $conditions
     * @return $this
     *
     * Обнавляет текущий объект, выполняя запрос по переданным параметрам и условиям
     */

    protected function update(array $parameters = [], array $conditions = []){
        if(count($parameters) > 0 && count($conditions) > 0) {

            $table = static::$table;

            $sql = $this->query->update($table)->set($parameters)->where($conditions)->get();

            try {

                $statement = $this->pdo->prepare($sql);

                $statement->execute();

                return $this;

            } catch (\PDOException $e) {
                die(var_dump($e->getMessage()));
            }
        } elseif (!$this->isNew()) {
            $data = [];
            $columns = [];

            foreach ($this as $column => $value) {
                if ($column === 'id' || $column === 'sql' || $column === 'condition') continue;
                if (is_object($value) || is_array($value) || is_resource($value)) continue;

                $data[] = $column . ' = ' . $this->escape($value);
                $columns[$column] = $value;
            }

            $table = static::$table;

            $sql = $this->query->update($table)->set($columns)->where(['id' => $this->id])->get();

            try {

                $statement = $this->pdo->prepare($sql);

                $statement->execute();

                return $this;

            } catch (\PDOException $e) {
                die(var_dump($e->getMessage()));
            }
        }
    }

    /**
     * @param array $conditions
     * @return $this
     *
     * Удаляет запись из БД
     */

    protected function delete(array $conditions = []){
        if(count($conditions) > 0) {

            $table = static::$table;

            $sql = $this->query->table($table)->delete()->where($conditions)->get();

            try {

                $statement = $this->pdo->prepare($sql);

                $statement->execute();

                return $this;

            } catch (\PDOException $e) {
                die(var_dump($e->getMessage()));
            }
        } elseif(!$this->isNew()) {
            $table = static::$table;

            $sql = $this->query->table($table)->delete()->where(['id' => $this->id])->get();

            try {

                $statement = $this->pdo->prepare($sql);

                $statement->execute();

                return $this;

            } catch (\PDOException $e) {
                die(var_dump($e->getMessage()));
            }
        }

        return $this;
    }

    /**
     * @return array
     *
     * Выполняет запрос в базу данных
     * Возвращает массив объектов-экземпляров класса, в контексте которого метод был вызван
     */

    public function get()
    {
        try {

            $sql = $this->query->get();

            $statement = $this->pdo->prepare($sql);

            $statement->setFetchMode(\PDO::FETCH_CLASS, get_class($this));

            $statement->execute();

            $this->collection = $statement->fetchAll();

            return $this->collection;

        } catch (\PDOException $e) {
            die(var_dump($e->getMessage()));
        }
    }

    /**
     * @return mixed
     *
     * Выполняет запрос в базу данных
     * Возвращает первый совпавший с запросом объект-экземпляр класса, в контексте которого метод был вызван
     */

    public function first()
    {
        try {

            $sql = $this->query->get();

            $statement = $this->pdo->prepare($sql);

            $statement->setFetchMode(\PDO::FETCH_CLASS, get_class($this));

            $statement->execute();

            return $statement->fetch();

        } catch (\PDOException $e) {
            die(var_dump($e->getMessage()));
        }
    }

    /**
     * @param string $field
     * @return $this
     *
     * Устанавливает, по какому полю будет происходить сортировка
     */

    protected function orderBy($field = 'id')
    {
        $this->query->orderBy($field);

        return $this;
    }

    /**
     * Устанавливает способом сортировки - сортировку по убыванию
     *
     * @return $this
     */

    protected function desc()
    {
        $this->query->desc();

        return $this;
    }

    /**
     * Устанавливает способом сортировки - сортировку по возрастанию
     *
     * @return $this
     */

    protected function asc()
    {
        $this->query->asc();

        return $this;
    }

    /**
     * @param $data
     * @return mixed
     *
     * Экранирует переданную строку
     */

    protected function escape($data)
    {
        return \App\Database\DB::escape($data);
    }

    /**
     * @param $from
     * @param $to
     * @param string $order
     * @return $this
     *
     * Возвращает лимитированные данные
     * Возвращает массив объектов-экземпляров класса, в контексте которого был вызван метод
     */

    protected function limit($from, $to = null, $order = 'ASC')
    {
        if($order === 'ASC') {
            $table = static::$table;

            if(!$this->query->has('select') && (!$this->query->has('table') && !$this->query->has('from'))) {
                $this->query->select('*')->from($table);
            }

            $this->query->orderBy('id')->asc()->limit($from, $to);

        } else {
            $table = static::$table;

            if(!$this->query->has('select') && (!$this->query->has('table') || !$this->query->has('from'))) {
                $this->query->select('*')->from($table);
            }

            $this->query->orderBy('id')->desc()->limit($from, $to);
        }


        return $this;
    }

    /**
     * @return int
     *
     * Возвращает количество записей в таблице
     */

    protected function countRows()
    {
        $sql = 'SELECT COUNT(*) FROM ' . static::$table;

        try {
            $statement = $this->pdo->prepare($sql);

            $statement->execute();

            return (int) ($statement->fetch())[0];

        } catch (\PDOException $e) {
            die(var_dump($e->getMessage()));
        }
    }

    /**
     * @return bool
     *
     * Проверяет, является ли объект новым (имеет ли id)
     */

    public function isNew()
    {
        return empty($this->id);
    }

    /**
     * Если объект - новый, записывает его в БД
     * Иначе - обновляет его
     */

    public function save()
    {
        if($this->isNew()) $this->insert();
        else $this->update();
    }

    /**
     * @return bool|string
     *
     * Возвращает имя класса по имени таблицы
     */

    protected function getClassName()
    {
        return substr(ucfirst(static::$table), 0, mb_strlen(static::$table)-1);
    }

    /**
     * @param $id
     * @return mixed
     *
     * Ищет запись в БД по переданному id
     * Возвращает объект-экземпляр класса, в контексте которого метод был вызван
     */

    protected function find($id)
    {
        $table = static::$table;

        $sql = $this->query->select('*')->from($table)->where(['id' => $id])->get();

        try {

            $statement = $this->pdo->prepare($sql);

            $statement->setFetchMode(\PDO::FETCH_CLASS, get_class($this));

            $statement->execute();

            return $statement->fetch();

        } catch (\PDOException $e) {
            die(var_dump($e->getMessage()));
        }
    }

    protected function sql()
    {
        return $this->query->get();
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