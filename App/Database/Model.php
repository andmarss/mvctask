<?php

/**
 * Class Model
 * @package App\Database
 */

namespace App\Database;

use App\Database\DB;
use App\App;
use App\Database\ORM;
use App\Database\QueryBuilder;

class Model extends ORM
{
    public static $table;

    public $id;

    /**
     * Возвращает коллекцию объектов из выбранной таблицы
     *
     * @return \App\Collect
     */

    protected function all()
    {
        $sql = $this->query->select('*')->from(static::$table)->get();

        $statement = $this->pdo->prepare($sql);

        $statement->execute();

        return collect($statement->fetchAll(\PDO::FETCH_CLASS, static::class));
    }

    /**
     * @param array $parameters
     * @return mixed
     *
     * Создает экземпляр класса, в контексте которого он был вызван
     */

    public static function create(array $parameters)
    {
        return (new static())->insert($parameters);
    }

    /**
     * @param $className
     * @param $foreignKey
     * @param $ownerKey
     * @return mixed
     *
     * Определяет отношение один к одному или один ко многим
     */

    public function belongsTo($className, $foreignKey, $ownerKey)
    {
        $class = "App\\Database\\$className";

        $sql = "SELECT * FROM " . (new $class)->getTable() . " WHERE " . $foreignKey . " = " . $this->{$ownerKey};

        try {

            $statement = $this->pdo->prepare($sql);

            $statement->setFetchMode(\PDO::FETCH_CLASS, $class);

            $statement->execute();

            return $statement->fetch();

        } catch (\PDOException $e) {
            die(var_dump($e->getMessage()));
        }
    }

    /**
     * @param $className
     * @param $foreignKey
     * @param $ownerKey
     * @return mixed
     *
     * Определяет отношение один к одному
     */

    public function hasOne($className, $foreignKey, $ownerKey)
    {
        $class = "App\\Database\\$className";

        $sql = "SELECT * FROM " . (new $class)->getTable() . " WHERE " . $foreignKey . " = " . $this->{$ownerKey};

        try {

            $statement = $this->pdo->prepare($sql);

            $statement->setFetchMode(\PDO::FETCH_CLASS, $class);

            $statement->execute();

            return $statement->fetch();

        } catch (\PDOException $e) {
            die(var_dump($e->getMessage()));
        }
    }

    /**
     * @param $className
     * @param $foreignKey
     * @param $ownerKey
     * @return array
     *
     * Определяет отношение один ко многим
     */

    public function hasMany($className, $foreignKey, $ownerKey)
    {
        $class = "App\\Database\\$className";

        $sql = "SELECT * FROM " . (new $class)->getTable() . " WHERE " . $foreignKey . " = " . $this->{$ownerKey};

        try {

            $statement = $this->pdo->prepare($sql);

            $statement->setFetchMode(\PDO::FETCH_CLASS, $class);

            $statement->execute();

            $this->collection = $statement->fetchAll();

            return $this->collection;

        } catch (\PDOException $e) {
            die(var_dump($e->getMessage()));
        }
    }

    public function getTable()
    {
        return static::$table;
    }
}
