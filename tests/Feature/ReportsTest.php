<?php

declare(strict_types=1);

use GraystackIT\HelpSpace\Connectors\HelpSpaceConnector;
use GraystackIT\HelpSpace\Data\ChannelsReport;
use GraystackIT\HelpSpace\Data\PerformanceReport;
use GraystackIT\HelpSpace\Exceptions\HelpSpaceApiException;
use GraystackIT\HelpSpace\HelpSpaceClient;
use GraystackIT\HelpSpace\Requests\Reports\ChannelsReportRequest;
use GraystackIT\HelpSpace\Requests\Reports\PerformanceReportRequest;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

it('returns ChannelsReport on getChannelsReport', function (): void {
    $mockClient = new MockClient([
        ChannelsReportRequest::class => MockResponse::make([
            'daily_counts' => [
                ['date' => '2024-01-01', 'opened' => 5, 'closed' => 3],
            ],
            'metrics' => [
                'new' => 5, 'closed' => 3, 'open' => 10, 'waiting' => 2, 'escalated' => 1,
            ],
            'channels'      => [['name' => 'Email', 'count' => 5]],
            'tags'          => [['name' => 'billing', 'count' => 2]],
            'top_customers' => [['email' => 'cust@example.com', 'count' => 3]],
        ], 200),
    ]);

    $connector = app(HelpSpaceConnector::class);
    $connector->withMockClient($mockClient);

    $report = (new HelpSpaceClient($connector))->getChannelsReport('2024-01-01', '2024-01-31');

    expect($report)->toBeInstanceOf(ChannelsReport::class)
        ->and($report->dailyCounts)->toHaveCount(1)
        ->and($report->metrics['new'])->toBe(5)
        ->and($report->channels)->toHaveCount(1)
        ->and($report->tags)->toHaveCount(1)
        ->and($report->topCustomers)->toHaveCount(1);
});

it('passes date range to channels report request', function (): void {
    $mockClient = new MockClient([
        ChannelsReportRequest::class => MockResponse::make([
            'daily_counts' => [], 'metrics' => [], 'channels' => [], 'tags' => [], 'top_customers' => [],
        ], 200),
    ]);

    $connector = app(HelpSpaceConnector::class);
    $connector->withMockClient($mockClient);

    (new HelpSpaceClient($connector))->getChannelsReport('2024-03-01', '2024-03-31');

    $lastRequest = $mockClient->getLastRequest();
    $reflection  = new ReflectionMethod($lastRequest, 'defaultBody');
    $body        = $reflection->invoke($lastRequest);

    expect($body['start'])->toBe('2024-03-01')
        ->and($body['end'])->toBe('2024-03-31');
});

it('throws InvalidArgumentException for empty dates on getChannelsReport', function (): void {
    $client = new HelpSpaceClient(app(HelpSpaceConnector::class));

    expect(fn () => $client->getChannelsReport('', '2024-01-31'))
        ->toThrow(\InvalidArgumentException::class);
});

it('returns PerformanceReport on getPerformanceReport', function (): void {
    $mockClient = new MockClient([
        PerformanceReportRequest::class => MockResponse::make([
            'daily_counts' => [
                ['date' => '2024-02-01', 'opened' => 3, 'closed' => 2],
            ],
            'metrics' => [
                'avg_resolution_time' => ['days' => 0, 'hours' => 4, 'minutes' => 30],
            ],
            'top_agents' => [
                ['name' => 'Agent Smith', 'resolved' => 12],
            ],
        ], 200),
    ]);

    $connector = app(HelpSpaceConnector::class);
    $connector->withMockClient($mockClient);

    $report = (new HelpSpaceClient($connector))->getPerformanceReport('2024-02-01', '2024-02-28');

    expect($report)->toBeInstanceOf(PerformanceReport::class)
        ->and($report->dailyCounts)->toHaveCount(1)
        ->and($report->topAgents)->toHaveCount(1)
        ->and($report->topAgents[0]['name'])->toBe('Agent Smith');
});

it('passes date range to performance report request', function (): void {
    $mockClient = new MockClient([
        PerformanceReportRequest::class => MockResponse::make([
            'daily_counts' => [], 'metrics' => [], 'top_agents' => [],
        ], 200),
    ]);

    $connector = app(HelpSpaceConnector::class);
    $connector->withMockClient($mockClient);

    (new HelpSpaceClient($connector))->getPerformanceReport('2024-04-01', '2024-04-30');

    $lastRequest = $mockClient->getLastRequest();
    $reflection  = new ReflectionMethod($lastRequest, 'defaultBody');
    $body        = $reflection->invoke($lastRequest);

    expect($body['start'])->toBe('2024-04-01')
        ->and($body['end'])->toBe('2024-04-30');
});

it('throws HelpSpaceApiException on 401 for getChannelsReport', function (): void {
    $mockClient = new MockClient([
        ChannelsReportRequest::class => MockResponse::make(['error' => 'Unauthorized'], 401),
    ]);

    $connector = app(HelpSpaceConnector::class);
    $connector->withMockClient($mockClient);

    expect(fn () => (new HelpSpaceClient($connector))->getChannelsReport('2024-01-01', '2024-01-31'))
        ->toThrow(HelpSpaceApiException::class);
});

it('throws HelpSpaceApiException on 401 for getPerformanceReport', function (): void {
    $mockClient = new MockClient([
        PerformanceReportRequest::class => MockResponse::make(['error' => 'Unauthorized'], 401),
    ]);

    $connector = app(HelpSpaceConnector::class);
    $connector->withMockClient($mockClient);

    expect(fn () => (new HelpSpaceClient($connector))->getPerformanceReport('2024-01-01', '2024-01-31'))
        ->toThrow(HelpSpaceApiException::class);
});
