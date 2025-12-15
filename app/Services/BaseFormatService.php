<?php

namespace App\Services;

use Carbon\Carbon;
use Error;

class BaseFormatService
{
    public function dateToTimestamp($data): int
    {
        try {
            $date = new Carbon($data);
            $timestamp =  $date->getTimestamp();
            return $timestamp;
        } catch (Error $error) {
            $now = Carbon::now();
            return $now->getTimestamp();
        }
    }
}
