<?php namespace Orchestra\Memory;

use Illuminate\Support\ServiceProvider;

class MemoryServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bindShared('orchestra.memory', function ($app) {
            return new MemoryManager($app);
        });
    }

    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot()
    {
        $path = realpath(__DIR__.'/../../');

        $this->package('orchestra/memory', 'orchestra/memory', $path);

        $this->registerMemoryEvent();
    }

    /**
     * Register memory events during booting.
     *
     * @return void
     */
    protected function registerMemoryEvent()
    {
        $app = $this->app;

        $app->after(function () use ($app) {
            $app['orchestra.memory']->finish();
        });
    }
}
