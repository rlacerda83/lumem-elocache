<?php

namespace Elocache\Cache;

use Cache;
use Illuminate\Support\Facades\Log;

class EloquentCache
{
    /**
     * Cache duration in minutes; 0 is forever.
     *
     * @var int
     */
    protected $cacheForMinutes = 0;

    /**
     * Enable caching.
     *
     * @var bool
     */
    protected $enableCaching = true;

    /**
     * Enable logging.
     *
     * @var bool
     */
    protected $enableLogging = true;

    protected $model;

    public function setModel($model)
    {
        $this->model = $model;
    }

    public function getModel()
    {
        return $this->model;
    }

    /**
     * @param $key
     * @param $query
     * @param string $verb
     * @param int    $itemsPage
     *
     * @return mixed
     */
    public function cacheQueryBuilder($key, $query, $verb = 'get', $itemsPage = 30)
    {
        $tag = $this->getModel()->getTable();
        $key = md5($key);

        $referenceClass = $this;
        if ($this->enableCaching) {
            if ($this->cacheForMinutes > 0) {
                return Cache::tags($tag)->remember($key, $this->cacheForMinutes, function () use ($query, $verb, $key, $referenceClass, $itemsPage) {
                    $referenceClass->log($key);
                    if ($verb == 'paginate') {
                        $paginator = $query->paginate($itemsPage);
                        $paginator->appends(app('request')->except('page'));

                        return $paginator;
                    }

                    return $query->$verb();
                });
            }

            return Cache::tags($tag)->rememberForever($key, function () use ($query, $verb, $key, $referenceClass, $itemsPage) {
                $referenceClass->log($key);
                if ($verb == 'paginate') {
                    $paginator = $query->paginate($itemsPage);
                    $paginator->appends(app('request')->except('page'));

                    return $paginator;
                }

                return $query->$verb();
            });
        }

        return $query->$verb();
    }

    /**
     * @param $key
     * @param $data
     *
     * @return mixed
     */
    public function cacheGenericData($key, $data, $tag)
    {
        if(!$tag) {
            $tag = $this->getModel()->getTable();
        }

        $key = md5($key);

        $referenceClass = $this;
        if ($this->enableCaching) {
            if ($this->cacheForMinutes > 0) {
                return Cache::tags($tag)->remember($key, $this->cacheForMinutes, function () use ($data, $key, $referenceClass) {
                    $referenceClass->log($key);

                    return $data;
                });
            }

            return Cache::tags($tag)->rememberForever($key, function () use ($data, $key, $referenceClass) {
                $referenceClass->log($key);

                return $data;
            });
        }

        return $data;
    }

    /**
     * @param $key
     */
    protected function log($key)
    {
        if ($this->enableLogging) {
            Log::info('Refreshing cache for '.get_class($this->getModel()).' ('.$key.')');
        }
    }

    /**
     * Flush the cache by tags.
     *
     * @return void
     */
    public function flushCacheByTags($tags, $key = null)
    {
        Cache::tags($tags)->flush();
    }
}
