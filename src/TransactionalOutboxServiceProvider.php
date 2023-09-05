<?php

namespace ShowersAndBs\TransactionalOutbox;

use Illuminate\Support\ServiceProvider;

class TransactionalOutboxServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        \ShowersAndBs\TransactionalOutbox\Events\MessagePublishingRun::class => [
            \ShowersAndBs\TransactionalOutbox\Listeners\MessagePublishingRun::class,
        ],
        \ShowersAndBs\TransactionalOutbox\Events\MessagePublishingFailed::class => [
            \ShowersAndBs\TransactionalOutbox\Listeners\MessagePublishingFailed::class,
        ],
        \ShowersAndBs\TransactionalOutbox\Events\MessagePublishingComplete::class => [
            \ShowersAndBs\TransactionalOutbox\Listeners\MessagePublishingComplete::class,
        ],
    ];

    /**
     * The subscriber classes to register.
     *
     * @var array
     */
    protected $subscribe = [
        \ShowersAndBs\TransactionalOutbox\Listeners\PublishableEventSubscriber::class,
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

        foreach ($this->subscribe as $subscriber) {
            \Illuminate\Support\Facades\Event::subscribe($subscriber);
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