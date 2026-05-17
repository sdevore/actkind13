<?php

namespace App\Traits;

/**
 * Trait ProvidesLabelsById
 * add the baseline ability to return the name of the enum as a label
 * so  UPDATE_USER_PROFILE becomes Update User Profile
 *
 * @see https://masteringlaravel.io/daily/2024-06-28-automatically-generate-label-from-enum
 */
trait ProvidesLabelsById
{
    public function label(): string
    {
        return __(ucwords(strtolower($this->name)));
    }
}
