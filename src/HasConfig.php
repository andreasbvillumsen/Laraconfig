<?php

namespace andreasbvillumsen\Laraconfig;

use andreasbvillumsen\Laraconfig\Eloquent\Setting;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

use function method_exists;

/**
 * @property-read SettingsCollection|Setting[] $settings
 *
 * @method Builder|static whereConfig(string|array $name, string $operator = null, $value = null, string $boolean = 'and')
 * @method Builder|static orWhereConfig(string|array $name, string $operator = null, $value = null)
 */
trait HasConfig
{
    /**
     * Returns the settings relationship.
     *
     * @return \andreasbvillumsen\Laraconfig\MorphManySettings
     */
    public function settings(): MorphManySettings
    {
        $instance = $this->newRelatedInstance(Eloquent\Setting::class);

        [$type, $id] = $this->getMorphs('settable', null, null);

        $table = $instance->getTable();

        return new MorphManySettings(
            $instance->newQuery(), $this, $table.'.'.$type, $table.'.'.$id, $this->getKeyName()
        );
    }

    /**
     * Boot the current trait.
     *
     * @return void
     */
    protected static function bootHasConfig(): void
    {
        static::addGlobalScope(new Eloquent\Scopes\WhereConfig());

        static::created(
            static function (Model $model): void {
                // If there is no method, or there is and returns true, we will initialize.
                if (!method_exists($model, 'shouldInitializeConfig') || $model->shouldInitializeConfig()) {
                    $model->settings()->initialize();
                }
            }
        );

        static::deleting(
            static function (Model $model): void {
                // Bye settings on delete, or force-delete.
                if (!method_exists($model, 'isForceDeleting') || $model->isForceDeleting()) {
                    $model->settings()->withoutGlobalScopes()->delete();
                }
            }
        );
    }
}
