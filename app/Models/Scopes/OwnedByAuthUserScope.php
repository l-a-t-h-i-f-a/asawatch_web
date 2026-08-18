<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

/**
 * Defense-in-depth: automatically scopes queries to the authenticated user
 * during HTTP requests, on top of explicit policy checks in controllers.
 * Skipped in console/queue context, where there is no request-bound user.
 */
class OwnedByAuthUserScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        if (app()->runningInConsole()) {
            return;
        }

        if ($user = auth()->user()) {
            $builder->where($model->getTable().'.user_id', $user->id);
        }
    }
}
