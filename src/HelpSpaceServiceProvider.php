<?php

declare(strict_types=1);

namespace GraystackIT\HelpSpace;

use GraystackIT\HelpSpace\Connectors\HelpSpaceConnector;
use Illuminate\Support\ServiceProvider;

class HelpSpaceServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/helpspace.php', 'helpspace');

        $this->app->singleton(HelpSpaceConnector::class, function () {
            $apiKey   = (string) config('helpspace.api_key', '');
            $clientId = (string) config('helpspace.client_id', '');

            if (empty($apiKey)) {
                throw new \RuntimeException(
                    'HelpSpace API key is not configured. Set HELPSPACE_API_KEY in your .env file.'
                );
            }

            if (empty($clientId)) {
                throw new \RuntimeException(
                    'HelpSpace Client ID is not configured. Set HELPSPACE_CLIENT_ID in your .env file.'
                );
            }

            return new HelpSpaceConnector(
                apiKey: $apiKey,
                clientId: $clientId,
                baseUrl: (string) config('helpspace.base_url', 'https://api.helpspace.com'),
            );
        });

        $this->app->singleton(HelpSpaceClient::class, fn ($app) => new HelpSpaceClient(
            connector: $app->make(HelpSpaceConnector::class),
        ));
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/helpspace.php' => config_path('helpspace.php'),
            ], 'helpspace-config');
        }
    }
}
