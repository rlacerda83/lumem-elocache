<?php

namespace Elocache\Repositories\Eloquent;

use Elocache\Cache\EloquentCache;
use Illuminate\Container\Container as App;
use Illuminate\Database\Eloquent\Model;

abstract class AbstractRepository extends EloquentCache
{
    /**
     * @var App
     */
    private $app;

    /**
     * @var
     */
    protected $queryBuilder;

    public function __construct(App $app)
    {
        $this->app = $app;
        $this->makeModel();
    }

    /**
     * Specify Model class name.
     *
     * @return mixed
     */
    abstract public function model();

    /**
     * @param array $columns
     *
     * @return mixed
     */
    public function all($columns = ['*'])
    {
        $query = $this->queryBuilder->select($columns);
        return $this->cacheQueryBuilder('all', $query);
    }

    /**
     * @param array $data
     *
     * @return mixed
     */
    public function create(array $data)
    {
        return $this->queryBuilder->create($data);
    }

    /**
     * @param array $data
     * @param $id
     * @param string $attribute
     *
     * @return mixed
     */
    public function update(array $data, $id, $attribute = 'id')
    {
        return $this->queryBuilder->where($attribute, '=', $id)->update($data);
    }

    /**
     * @param $id
     *
     * @return mixed
     */
    public function delete($id)
    {
        return $this->queryBuilder->destroy($id);
    }

    /**
     * @param $id
     * @param array $columns
     *
     * @return mixed
     */
    public function find($id, $columns = ['*'])
    {
        $query = $this->queryBuilder->select($columns)->where('id', $id);

        return $this->cacheQueryBuilder($id, $query, 'first');
    }

    /**
     * @param $attribute
     * @param $value
     * @param array $columns
     *
     * @return mixed
     */
    public function findBy($attribute, $value, $columns = array('*'))
    {
        $query = $this->queryBuilder->select($columns)->where($attribute, '=', $value);
        return $this->cacheQueryBuilder($attribute.$value, $query, 'first');
    }

    public function makeModel()
    {
        $model = $this->app->make($this->model());

        if (!$model instanceof Model) {
            throw new \Exception("Class {$this->model()} must be an instance of Illuminate\\Database\\Eloquent\\Model");
        }

        $this->queryBuilder = $model->newQuery();
        $this->model = $model;
        $this->setModel($this->model);
    }
}
