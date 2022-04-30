<?php

namespace andreasbvillumsen\Laraconfig\Facades;

use andreasbvillumsen\Laraconfig\Registrar\Declaration;
use andreasbvillumsen\Laraconfig\Registrar\SettingRegistrar;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Facade;

/**
 * @method static Collection|\andreasbvillumsen\Laraconfig\Eloquent\Setting[] getSettings()
 * @method static Declaration name(string $name)
 */
class Setting extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor(): string
    {
        return SettingRegistrar::class;
    }
}
