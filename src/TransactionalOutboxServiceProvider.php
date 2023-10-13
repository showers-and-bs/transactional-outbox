<?php

namespace ShowersAndBs\TransactionalOutbox;

use Illuminate\Support\ServiceProvider;
use ShowersAndBs\ThirstyEvents\Contracts\ShouldBePublished;
use ShowersAndBs\TransactionalOutbox\Events\PublishingComplete;
use ShowersAndBs\TransactionalOutbox\Events\PublishingFailed;
use ShowersAndBs\TransactionalOutbox\Events\PublishingRun;
use ShowersAndBs\TransactionalOutbox\Listeners\PublishingCompleteListener;
use ShowersAndBs\TransactionalOutbox\Listeners\PublishingFailedListener;
use ShowersAndBs\TransactionalOutbox\Listeners\PublishingRunListener;
use ShowersAndBs\TransactionalOutbox\Listeners\ShouldBePublishedListener;

class TransactionalOutboxServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        PublishingRun::class => [
            PublishingRunListener::class,
        ],
        PublishingFailed::class => [
            PublishingFailedListener::class,
        ],
        PublishingComplete::class => [
            PublishingCompleteListener::class,
        ],
        ShouldBePublished::class => [
            ShouldBePublishedListener::class,
        ],
    ];

    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->registerCommands();
            $this->registerMigrations();
        }

        $this->registerEventListeners();
    }

    /**
     * Register the package's console commands.
     *
     * @return void
     */
    protected function registerCommands(): void
    {
        $this->commands([
            \ShowersAndBs\TransactionalOutbox\Console\Commands\MessageRelay::class,
            \ShowersAndBs\TransactionalOutbox\Console\Commands\MessageOutbox::class,
        ]);
    }

    /**
     * Register the package's event listeners.
     *
     * @return void
     */
    protected function registerEventListeners()
    {
        foreach ($this->listen as $event => $listeners) {
            foreach (array_unique($listeners, SORT_REGULAR) as $listener) {
                \Illuminate\Support\Facades\Event::listen($event, $listener);
            }
        }
    }

    /**
     * Register the package's database migrations.
     *
     * @return void
     */
    protected function registerMigrations()
    {
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
    }
}
