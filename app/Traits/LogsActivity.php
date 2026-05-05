<?php

namespace App\Traits;

use App\Models\ActivityLog;

trait LogsActivity
{
    protected static function bootLogsActivity()
    {
        static::created(function ($model) {
            static::logAction($model, 'created', "Created " . class_basename($model));
        });

        static::updated(function ($model) {
            static::logAction($model, 'updated', "Updated " . class_basename($model));
        });

        static::deleted(function ($model) {
            static::logAction($model, 'deleted', "Deleted " . class_basename($model));
        });
    }

    protected static function logAction($model, $action, $description)
    {
        if (auth()->check()) {
            ActivityLog::create([
                'user_id'      => auth()->id(),
                'subject_type' => get_class($model),
                'subject_id'   => $model->id,
                'action'       => $action,
                'description'  => $description,
                'ip_address'   => request()->ip(),
            ]);
        }
    }
}
