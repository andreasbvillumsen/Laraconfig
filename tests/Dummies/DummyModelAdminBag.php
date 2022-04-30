<?php

namespace Tests\Dummies;

use andreasbvillumsen\Laraconfig\HasConfig;
use Illuminate\Database\Eloquent\Model;

class DummyModelAdminBag extends Model
{
    use HasConfig;

    protected $table = 'users';

    protected function getSettingBags(): string|array {
        return ['admins'];
    }
}
