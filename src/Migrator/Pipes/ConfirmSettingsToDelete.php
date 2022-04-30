<?php

namespace andreasbvillumsen\Laraconfig\Migrator\Pipes;

use Closure;
use andreasbvillumsen\Laraconfig\Eloquent\Metadata;
use andreasbvillumsen\Laraconfig\Migrator\Data;
use Illuminate\Console\OutputStyle;
use Illuminate\Contracts\Foundation\Application;
use RuntimeException;
use Symfony\Component\Console\Input\InputInterface;

/**
 * @internal
 */
class ConfirmSettingsToDelete
{
    /**
     * ConfirmSettingsToDelete constructor.
     *
     * @param Application $app
     * @param OutputStyle $output
     * @param InputInterface $input
     */
    public function __construct(
        protected Application $app,
        protected OutputStyle $output,
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
        if ($this->rejectedDeleteOnProduction($data)) {
            throw new RuntimeException('Settings migration has been rejected by the user.');
        }

        return $next($data);
    }

    /**
     * Returns if there is metadata to delete and the developer has rejected their deletion.
     *
     * @param Data $data
     *
     * @return bool
     */
    protected function rejectedDeleteOnProduction(Data $data): bool
    {
        if ($this->shouldPrompt() && $count = $this->deletableMetadata($data)) {
            return !$this->output->confirm(
                "There are $count old settings that will be deleted on sync. Proceed?"
            );
        }

        return false;
    }

    /**
     * Counts metadata no longer listed in the manifest declarations.
     *
     * @param Data $data
     *
     * @return int
     */
    protected function deletableMetadata(Data $data): int
    {
        return $data->metadata->reject(static function (Metadata $metadata) use ($data): bool {
            return $data->declarations->has($metadata->name);
        })->count();
    }

    /**
     * Check if the developer should be prompted for deleting metadata.
     *
     * @return bool
     */
    protected function shouldPrompt(): bool
    {
        return !$this->input->getOption('refresh')
            && $this->app->environment('production')
            && !$this->input->getOption('force');
    }
}
