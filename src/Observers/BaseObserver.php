<?php

namespace Elocache\Observers;

class BaseObserver extends AbstractObserver
{
    public function saved($model)
    {
        $this->clearCacheTags($model->getTable());
    }

    public function deleted($model)
    {
        $this->clearCacheTags($model->getTable());
    }
}
