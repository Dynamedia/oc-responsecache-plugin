<?php namespace Dynamedia\ResponseCache\Classes\Console;

use Exception;
use Illuminate\Console\Command;
use Dynamedia\ResponseCache\Classes\Contracts\Cache;

class ClearCache extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'response-cache:clear {slug? : URL slug of page to delete} {--recursive}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear the response cache.';

    /**
     * Execute the console command.
     *
     * @return void
     * @throws Exception
     */
    public function handle(Cache $cache): void
    {
        $slug = $this->argument('slug');
        $recursive = $this->option('recursive');

        if (!$slug) {
            $this->clear($cache);
        } else if ($recursive) {
            $this->clear($cache, $slug);
        } else {
            $this->forget($cache, $slug);
        }
    }

    /**
     * Remove the cached file for the given slug.
     *
     * @param Cache $cache
     * @param string|null $slug
     * @return void
     * @throws Exception
     */
    protected function forget(Cache $cache, ?string $slug): void
    {
        if ($cache->forget($slug)) {
            $this->info('Response cache cleared for "'. $slug .'"');
        } else {
            $this->info('Response cache found for "' .$slug .'"');
        }
    }

    /**
     * Clear the full page cache.
     *
     * @param Cache $cache
     * @param string|null $path
     * @return void
     * @throws \Dynamedia\ResponseCache\Classes\Exceptions\CacheDirectoryPathNotSetException
     */
    protected function clear(Cache $cache, ?string $path = null): void
    {
        if ($cache->clear($path)) {
            $this->info('Response cache cleared at '. $cache->getCachePath($path));
        } else {
            $this->warn('Response cache not cleared at '. $cache->getCachePath($path));
        }
    }
}
