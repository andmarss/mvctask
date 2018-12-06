<?php

/**
 * Class DB
 */

namespace App\Database;

use App\App;

/**
 * Класс, предоставляющий доступ к БД
 *
 * Class DB
 * @package App\Database
 */

class DB
{
    protected $dbh;

    private static $instance;

    protected $action;
    protected $tableName;
    protected $field;
    protected $operator;
    protected $collection;
    protected $sql;
    protected $executeData;
    protected $query;

    /**
     * DB constructor.
     */

    public function __construct($config = [])
    {
        if(!$config) {
            $config = App::get('config/database');
        }

        try {
            $this->dbh = new \PDO(
                $config['connection'].';dbname='.$config['name'],
                $config['username'],
                $config['password'],
                $config['options']
            );

            $this->dbh->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

            $this->query = new \App\Database\QueryBuilder( $this->dbh );

        } catch (\PDOException $e) {
            var_dump($e->getMessage()); die();
        }
    }

    /**
     * @param $sql
     * @param array $data
     * @return bool
     */

    public function execute($sql, array $data = [])
    {
        $config = App::get('config/database');

        $dbh = new \PDO(
            $config['connection'].';dbname='.$config['name'],
            $config['username'],
            $config['password'],
            $config['options']
        );

        $sth = $dbh->prepare($sql);

        $result = $sth->execute($data);

        if(!$result) var_dump($sth->errorInfo() ); die();

        return true;
    }

    /**
     * @return string
     */

    public function lastInsertId()
    {
        return $this->dbh->lastInsertId();
    }

    /**
     * @return \PDO
     */

    public function connect($config)
    {
        try {
            $this->dbh = new \PDO(
                $config['connection'].';dbname='.$config['name'],
                $config['username'],
                $config['password'],
                $config['options']
            );
        } catch (\PDOException $e) {
            var_dump($e->errorInfo()); die();
        }

        return $this;
    }

    /**
     * Возвращает экземпляр PDO
     *
     * @return \PDO
     */

    public function get_dbh()
    {
        return $this->dbh;
    }

    /**
     * устанавливает имя таблицы, к которой потом будет происходить обращение
     *
     * @param $name
     * @return $this
     */

    protected function table($name)
    {
        $this->query->table($name);

        $this->tableName = $name;

        return $this;
    }

    protected function select($select = '*')
    {
        $this->query->select($select);

        return $this;
    }

    /**
     * @param array $condition
     * @return $this
     * @throws \Exception
     *
     * Принимает условия, по которому будет выполнен запрос в базу
     */

    protected function where($condition = [])
    {
        if(!$this->query->has('select')) {
            $this->query->select('*');
        }

        if(!$this->query->has('table') && !$this->query->has('from') && $this->tableName) {
            $this->query->from($this->tableName);
        }

        if(!$this->query->has('from') && !$this->query->has('table')){
            throw new \Exception('Сперва должен быть вызван метод table, после чего один из методов: select, update, insert или delete');
        } else {
            $this->query->where($condition);
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
        if (!$this->query->has('where')) {
            throw new \Exception('Сперва должна быть вызван метод table, после чего один из методов: select, update, insert или delete и метод where');
        }

        $this->query->orWhere($condition);

        return $this;
    }

    /**
     * @param $fieldName
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

    protected function whereIn($fieldName, $value, $selectedField = null)
    {
        if(!$this->query->has('select') && (!$this->query->has('from') && !$this->query->has('table'))){
            throw new \Exception('Сперва должен быть вызван метод table, после чего один из методов: select, update, insert или delete');
        }

        $this->query->whereIn($fieldName, $value, $selectedField);

        return $this;
    }

    /**
     * @param array $condition
     * @return $this
     * @throws \Exception
     *
     * Устанавливает соответствие символьной строки с шаблоном
     * Шаблон функция создает сама
     * Достаточно просто написать whereLike([name => 'Вася'])
     */

    protected function whereLike($condition = [])
    {
        if(!$this->query->has('select') && (!$this->query->has('from') && !$this->query->has('table'))){
            throw new \Exception('Сперва должен быть вызван метод table, после чего один из методов: select, update, insert или delete');
        }

        $this->query->whereLike($condition);

        return $this;
    }

    /**
     * @param array $condition
     * @return $this
     * @throws \Exception
     *
     * Уточняющий метод, устанавливающий соответствие символьной строки с шаблоном, и добавляющий ключевое слово OR перед шаблоном
     * Шаблон функция создает сама
     * Достаточно просто написать orWhereLike([name => 'Вася'])
     */

    protected function orWhereLike($condition = [])
    {
        if(!$this->query->has('select') && (!$this->query->has('from') && !$this->query->has('table'))){
            throw new \Exception('Сперва должен быть вызван метод table, после чего один из методов: select, update, insert или delete');
        }

        $this->query->orWhereLike($condition);

        return $this;
    }

    /**
     * Возвращает массив объектов-экземпляров указанного класса
     *
     * @param string $class
     * @return array
     */

    protected function get($class = '')
    {
        $class = $class ? 'App\\Database\\' . ucfirst($class) : get_class($this);

        $sql = $this->query->get();

        $statement = $this->dbh->prepare($sql);

        if(!is_null($this->executeData)) {
            $statement->execute($this->executeData);
        } else {
            $statement->execute();
        }

        return $statement->fetchAll(\PDO::FETCH_CLASS, $class);
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
     * Возвращает первый совпавший экземпляр
     *
     * @param string $class
     * @return mixed
     */

    protected function first($class = '')
    {
        if($this->query->has('table')) {
            $class = $this->getClassName( $this->query->getSql('table') );
        }

        if ($this->tableName) {
            $class = $this->getClassName( $this->tableName );
        }

        $class = $class !== '' ? 'App\\Database\\' . ucfirst($class) : null;

        $sql = $this->query->get();

        try {

            $statement = $this->dbh->prepare($sql);

            $statement->setFetchMode(\PDO::FETCH_CLASS, get_class((new $class)));

            $statement->execute();

            return $statement->fetch();

        } catch (\PDOException $e) {
            die(var_dump($e->getMessage(), $e->getFile(), $e->getLine(), $e->getTraceAsString()));
        }
    }

    protected function getClassName($table)
    {
        return substr(ucfirst($table), 0, mb_strlen($table)-1);
    }

    protected function escape($string)
    {
        return $this->dbh->quote($string);
    }

    public static function __callStatic($method, $args)
    {
        if(!is_object(static::$instance)) {
            static::$instance = App::get('database');
        }

        return static::$instance->$method(...$args);
    }

    public function __call($method, $args)
    {
        if (method_exists($this, $method)) {
            return $this->{$method}(...$args);
        }
    }
}
