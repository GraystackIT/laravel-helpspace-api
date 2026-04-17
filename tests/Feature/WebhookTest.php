<?php

declare(strict_types=1);

use GraystackIT\HelpSpace\Connectors\HelpSpaceConnector;
use GraystackIT\HelpSpace\Data\WebhookConfig;
use GraystackIT\HelpSpace\Exceptions\HelpSpaceApiException;
use GraystackIT\HelpSpace\HelpSpaceClient;
use GraystackIT\HelpSpace\Requests\Webhook\GetWebhookLogsRequest;
use GraystackIT\HelpSpace\Requests\Webhook\GetWebhookRequest;
use GraystackIT\HelpSpace\Requests\Webhook\UpdateWebhookRequest;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

it('returns WebhookConfig on getWebhook', function (): void {
    $mockClient = new MockClient([
        GetWebhookRequest::class => MockResponse::make([
            'webhooks' => [
                'enabled'      => true,
                'url'          => 'https://myapp.example.com/webhooks/helpspace',
                'secret'       => 'abc123secret',
                'headers'      => [['key' => 'X-Custom', 'value' => 'my-value']],
                'trigger'      => ['ticket' => ['created' => true], 'customer' => []],
                'failed_count' => 0,
            ],
        ], 200),
    ]);

    $connector = app(HelpSpaceConnector::class);
    $connector->withMockClient($mockClient);

    $config = (new HelpSpaceClient($connector))->getWebhook();

    expect($config)->toBeInstanceOf(WebhookConfig::class)
        ->and($config->enabled)->toBeTrue()
        ->and($config->url)->toBe('https://myapp.example.com/webhooks/helpspace')
        ->and($config->secret)->toBe('abc123secret')
        ->and($config->headers)->toHaveCount(1)
        ->and($config->failedCount)->toBe(0);
});

it('returns updated WebhookConfig on updateWebhook', function (): void {
    $mockClient = new MockClient([
        UpdateWebhookRequest::class => MockResponse::make([
            'webhooks' => [
                'enabled'      => false,
                'url'          => 'https://myapp.example.com/webhooks/hs',
                'secret'       => null,
                'headers'      => [],
                'trigger'      => [],
                'failed_count' => 0,
            ],
        ], 200),
    ]);

    $connector = app(HelpSpaceConnector::class);
    $connector->withMockClient($mockClient);

    $config = (new HelpSpaceClient($connector))->updateWebhook([
        'enabled' => false,
        'url'     => 'https://myapp.example.com/webhooks/hs',
    ]);

    expect($config)->toBeInstanceOf(WebhookConfig::class)
        ->and($config->enabled)->toBeFalse()
        ->and($config->url)->toBe('https://myapp.example.com/webhooks/hs');
});

it('wraps config in webhooks key for updateWebhook request', function (): void {
    $mockClient = new MockClient([
        UpdateWebhookRequest::class => MockResponse::make([
            'webhooks' => ['enabled' => true, 'url' => '', 'headers' => [], 'trigger' => [], 'failed_count' => 0],
        ], 200),
    ]);

    $connector = app(HelpSpaceConnector::class);
    $connector->withMockClient($mockClient);

    (new HelpSpaceClient($connector))->updateWebhook(['enabled' => true, 'url' => 'https://example.com']);

    $lastRequest = $mockClient->getLastRequest();
    $reflection  = new ReflectionMethod($lastRequest, 'defaultBody');
    $body        = $reflection->invoke($lastRequest);

    expect($body)->toHaveKey('webhooks')
        ->and($body['webhooks']['enabled'])->toBeTrue();
});

it('returns array on getWebhookLogs', function (): void {
    $mockClient = new MockClient([
        GetWebhookLogsRequest::class => MockResponse::make([
            'data' => [
                ['status' => 500, 'response' => 'Internal Server Error', 'created_at' => '2024-01-01T12:00:00Z'],
            ],
        ], 200),
    ]);

    $connector = app(HelpSpaceConnector::class);
    $connector->withMockClient($mockClient);

    $logs = (new HelpSpaceClient($connector))->getWebhookLogs();

    expect($logs)->toHaveCount(1)
        ->and($logs[0]['status'])->toBe(500);
});

it('returns empty array when no webhook logs', function (): void {
    $mockClient = new MockClient([
        GetWebhookLogsRequest::class => MockResponse::make(['data' => []], 200),
    ]);

    $connector = app(HelpSpaceConnector::class);
    $connector->withMockClient($mockClient);

    $logs = (new HelpSpaceClient($connector))->getWebhookLogs();

    expect($logs)->toBe([]);
});

it('throws HelpSpaceApiException on 401 for getWebhook', function (): void {
    $mockClient = new MockClient([
        GetWebhookRequest::class => MockResponse::make(['error' => 'Unauthorized'], 401),
    ]);

    $connector = app(HelpSpaceConnector::class);
    $connector->withMockClient($mockClient);

    expect(fn () => (new HelpSpaceClient($connector))->getWebhook())
        ->toThrow(HelpSpaceApiException::class);
});
