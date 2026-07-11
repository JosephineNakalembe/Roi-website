<?php

namespace App\Http\Controllers\Concerns;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

trait AuthorizesOwnership
{
    /**
     * Abort with 403 unless the given model belongs to the authenticated user.
     */
    protected function authorizeOwnership(Model $model, string $column = 'user_id'): void
    {
        if ($model->{$column} !== Auth::id()) {
            abort(403);
        }
    }
}
