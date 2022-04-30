<?php

namespace Tests\Dummies;

use andreasbvillumsen\Laraconfig\HasConfig;
use Illuminate\Database\Eloquent\Model;

class DummyModelWithoutSettings extends Model
{
    protected $table = 'users';
}
