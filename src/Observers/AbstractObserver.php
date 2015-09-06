<?php

namespace Elocache\Observers;

use Elocache\Cache\EloquentCache;

abstract class AbstractObserver extends EloquentCache
{
    protected function clearCacheTags($tags)
    {
        $this->flushCacheByTags($tags);
    }

    abstract public function saved($model);

    abstract public function deleted($model);
}
