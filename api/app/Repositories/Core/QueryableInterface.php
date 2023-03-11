<?php

namespace App\Repositories\Core;

interface QueryableInterface
{
    public function run($parameters = []);
}
