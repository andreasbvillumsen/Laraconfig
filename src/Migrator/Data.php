<?php

namespace andreasbvillumsen\Laraconfig\Migrator;

use andreasbvillumsen\Laraconfig\Registrar\Declaration;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

/**
 * @internal
 */
class Data
{
    /**
     * Database Metadata.
     *
     * @var \andreasbvillumsen\Laraconfig\Eloquent\Metadata[]|EloquentCollection
     */
    public EloquentCollection|array $metadata;

    /**
     * Declarations.
     *
     * @var Collection|Declaration[]
     */
    public Collection|array $declarations;

    /**
     * Models to check for bags.
     *
     * @var Collection|Model[]
     */
    public Collection|array $models;

    /**
     * If the cache should be invalidated on settings changes.
     *
     * @var bool
     */
    public bool $invalidateCache = false;

    /**
     * Invalidate the cache through the models instead of the settings.
     *
     * @var bool
     */
    public bool $useModels = false;

    /**
     * Data constructor.
     *
     * @return void
     */
    public function __construct()
    {
        $this->models = new Collection();
    }
}
