<?php

namespace andreasbvillumsen\Laraconfig\Migrator\Pipes;

use Closure;
use andreasbvillumsen\Laraconfig\Laraconfig;
use andreasbvillumsen\Laraconfig\Migrator\Data;
use andreasbvillumsen\Laraconfig\MorphManySettings;
use Illuminate\Console\OutputStyle;
use Illuminate\Contracts\Cache\Factory;
use Illuminate\Contracts\Config\Repository;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\LazyCollection;
use Symfony\Component\Console\Input\InputInterface;

class InvalidateCache
{
    /**
     * RemoveOldMetadata constructor.
     *
     * @param Repository $config
     * @param OutputStyle $output
     * @param Factory $cache
     * @param InputInterface $input
     */
    public function __construct(
        protected Repository $config,
        protected OutputStyle $output,
        protected Factory $cache,
        protected InputInterface $input
    ) {
    }

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
        // If the cache was flushed, there are no cache keys to forget.
        if ($this->shouldInvalidateCacheKeys($data)) {
            $store = $this->config->get('cache.default');

            $this->output->info(
                "Forgot {$this->forgetModelCacheKeys($data)} config cache keys/users from the cache store $store."
            );
        }

        return $next($data);
    }


    /**
     * Check if we should cycle through models to invalidate their keys.
     *
     * @param Data $data
     *
     * @return bool
     */
    protected function shouldInvalidateCacheKeys(Data $data): bool
    {
        return $data->invalidateCache
            && ! $this->input->getOption('flush-cache')
            && $this->config->get('laraconfig.cache.enable');
    }

    /**
     * Forget model cache keys.
     *
     * @param Data $data
     * @return int
     */
    protected function forgetModelCacheKeys(Data $data): int
    {
        $store = $this->cache->store($this->config->get('laraconfig.cache.store'));

        $prefix = $this->config->get('laraconfig.cache.prefix');

        $count = 0;

        foreach ($data->models as $settable) {
            $morph = $settable->getMorphClass();
            $keyName = $settable->getKeyName();

            foreach ($this->querySettable($settable) as $model) {
                $key = MorphManySettings::generateKeyForModel($prefix, $morph, $model->{$keyName});

                $store->forget($key);
                $store->forget("$key:time");

                $count++;
            }
        }

        return $count;
    }

    /**
     * Returns a query to retrieve all settings distinct by type and id.
     *
     * @param Model $settable
     * @return LazyCollection
     */
    protected function querySettable(Model $settable): LazyCollection
    {
        // We can simply query the settings table through its index of morphs.
        return $settable->newQuery()
            ->getQuery()
            ->select($name = $settable->getKeyName())
            ->lazyById(column: $name);
    }
}
