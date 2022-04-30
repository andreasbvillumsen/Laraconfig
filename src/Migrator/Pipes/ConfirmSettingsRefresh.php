<?php

namespace andreasbvillumsen\Laraconfig\Migrator\Pipes;

use Closure;
use andreasbvillumsen\Laraconfig\Eloquent\Metadata;
use andreasbvillumsen\Laraconfig\Eloquent\Setting;
use andreasbvillumsen\Laraconfig\Migrator\Data;
use Illuminate\Console\OutputStyle;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Database\Eloquent\Collection;
use RuntimeException;
use Symfony\Component\Console\Input\InputInterface;

/**
 * @internal
 */
class ConfirmSettingsRefresh
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
     * @param  \Closure  $next
     *
     * @return mixed
     */
    public function handle(Data $data, Closure $next): mixed
    {
        if ($this->input->getOption('refresh')) {
            if ($this->rejectedRefreshOnProduction()) {
                throw new RuntimeException('Settings refresh has been rejected by the user.');
            }

            // Truncate both tables.
            Setting::query()->truncate();
            Metadata::query()->truncate();

            // Reset the metadata collection since there is nothing left.
            $data->metadata = new Collection();
        }

        return $next($data);
    }

    /**
     * Returns if there the settings data will be refreshed and the developer has rejected that.
     *
     * @return bool
     */
    protected function rejectedRefreshOnProduction(): bool
    {
        return $this->shouldPrompt()
            && !$this->output->confirm('ALL settings will be deleted completely. Proceed?');
    }

    /**
     * Check if the developer should be prompted for refreshing the settings tables.
     *
     * @return bool
     */
    protected function shouldPrompt(): bool
    {
        return Metadata::query()->exists()
            && $this->app->environment('production')
            && ! $this->input->getOption('force');
    }
}
