<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Model;

final class SafeSeed
{
    /**
     * Insert a row only when the unique match does not already exist.
     * Existing production records are left untouched.
     *
     * @param  class-string<Model>  $class
     * @param  array<string, mixed>  $unique
     * @param  array<string, mixed>  $values
     */
    public static function missing(string $class, array $unique, array $values): Model
    {
        $row = $class::query()->firstOrNew($unique);
        if (! $row->exists) {
            $row->fill($values);
            $row->save();
        }

        return $row;
    }
}
