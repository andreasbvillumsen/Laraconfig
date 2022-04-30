<?php

namespace andreasbvillumsen\Laraconfig\Migrator\Pipes;

use Closure;
use andreasbvillumsen\Laraconfig\Eloquent\Metadata;
use andreasbvillumsen\Laraconfig\Migrator\Data;

/**
 * @internal
 */
class LoadMetadata
{
    /**
     * Handles the Settings migration.
     *
     * @param Data $data
     * @param Closure $next
     *
     * @return mixed
     */
    public function handle(Data $data, Closure $next): mixed
    {
        $data->metadata = Metadata::all()->keyBy(static fn(Metadata $metadata): string => $metadata->name);

        return $next($data);
    }
}
