<?php

namespace App\Models\Traits;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

trait CreatedBy
{
    /**
     * Boot the trait.
     */
    protected static function bootCreatedBy(): void
    {
        static::creating(function ($model) {
            // Check if the table has a user_id column
            if (Schema::hasColumn($model->getTable(), 'user_id')) {
                // Only set user_id if it's not already set and user is authenticated
                if (empty($model->user_id) && Auth::check()) {
                    $model->user_id = Auth::id();
                }
            }
        });
    }
}

