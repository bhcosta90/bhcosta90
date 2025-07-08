<?php

declare(strict_types = 1);

namespace Costa\Services;

use Illuminate\Database\Eloquent\Model;

abstract class Service
{
    abstract protected function model(): Model;
}
