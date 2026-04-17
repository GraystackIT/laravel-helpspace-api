<?php

declare(strict_types=1);

use GraystackIT\HelpSpace\Connectors\HelpSpaceConnector;
use GraystackIT\HelpSpace\Data\Ticket;
use GraystackIT\HelpSpace\Exceptions\HelpSpaceApiException;
use GraystackIT\HelpSpace\HelpSpaceClient;
use GraystackIT\HelpSpace\Requests\Tickets\CreateTicketRequest;
use GraystackIT\HelpSpace\Requests\Tickets\DeleteTicketRequest;
use GraystackIT\HelpSpace\Requests\Tickets\GetTicketRequest;
use GraystackIT\HelpSpace\Requests\Tickets\ListTicketsRequest;
use GraystackIT\HelpSpace\Requests\Tickets\UpdateTicketRequest;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

it('is resolved from the container', function (): void {
    expect(app(HelpSpaceClient::class))->toBeInstanceOf(HelpSpaceClient::class);
});

it('returns paginated Ticket array on listTickets', function (): void {
    $mockClient = new MockClient([
        ListTicketsRequest::class => MockResponse::make([
            'data' => [
                [
                    'id'           => 101,
                    'subject'      => 'Cannot login',
                    'status'       => 'open',
                    'channel'      => ['id' => 'ch1', 'email' => 'support@example.com'],
                    'from_contact' => ['email' => 'user@example.com', 'name' => 'John Doe'],
                    'assignee'     => ['id' => 'a1', 'name' => 'Agent Smith'],
                    'team'         => ['id' => 't1', 'name' => 'Tier 1'],
                    'tags'         => ['billing'],
                    'custom_fields' => [],
                    'created_at'   => '2024-01-15T10:00:00Z',
                    'updated_at'   => '2024-01-15T11:00:00Z',
                ],
            ],
            'meta'  => ['current_page' => 1, 'total' => 1],
            'links' => ['next' => null],
        ], 200),
    ]);

    $connector = app(HelpSpaceConnector::class);
    $connector->withMockClient($mockClient);

    $result = (new HelpSpaceClient($connector))->listTickets();

    expect($result['data'])->toHaveCount(1)
        ->and($result['data'][0])->toBeInstanceOf(Ticket::class)
        ->and($result['data'][0]->id)->toBe(101)
        ->and($result['data'][0]->subject)->toBe('Cannot login')
        ->and($result['data'][0]->status)->toBe('open')
        ->and($result['data'][0]->channelEmail)->toBe('support@example.com')
        ->and($result['data'][0]->contactEmail)->toBe('user@example.com')
        ->and($result['data'][0]->contactName)->toBe('John Doe')
        ->and($result['data'][0]->assigneeName)->toBe('Agent Smith')
        ->and($result['data'][0]->teamName)->toBe('Tier 1')
        ->and($result['data'][0]->tags)->toBe(['billing'])
        ->and($result['meta'])->toBe(['current_page' => 1, 'total' => 1]);
});

it('returns empty data array when no tickets found', function (): void {
    $mockClient = new MockClient([
        ListTicketsRequest::class => MockResponse::make([
            'data'  => [],
            'meta'  => [],
            'links' => [],
        ], 200),
    ]);

    $connector = app(HelpSpaceConnector::class);
    $connector->withMockClient($mockClient);

    $result = (new HelpSpaceClient($connector))->listTickets();

    expect($result['data'])->toBe([]);
});

it('caps per-page at 50 on listTickets', function (): void {
    $mockClient = new MockClient([
        ListTicketsRequest::class => MockResponse::make(['data' => [], 'meta' => [], 'links' => []], 200),
    ]);

    $connector = app(HelpSpaceConnector::class);
    $connector->withMockClient($mockClient);

    (new HelpSpaceClient($connector))->listTickets(perPage: 999);

    $lastRequest = $mockClient->getLastRequest();
    expect($lastRequest)->toBeInstanceOf(ListTicketsRequest::class);

    $reflection = new ReflectionMethod($lastRequest, 'defaultQuery');
    $query      = $reflection->invoke($lastRequest);

    expect((int) $query['per-page'])->toBe(50);
});

it('passes status filter to listTickets request', function (): void {
    $mockClient = new MockClient([
        ListTicketsRequest::class => MockResponse::make(['data' => [], 'meta' => [], 'links' => []], 200),
    ]);

    $connector = app(HelpSpaceConnector::class);
    $connector->withMockClient($mockClient);

    (new HelpSpaceClient($connector))->listTickets(statuses: ['open', 'escalated']);

    $lastRequest = $mockClient->getLastRequest();
    $reflection  = new ReflectionMethod($lastRequest, 'defaultQuery');
    $query       = $reflection->invoke($lastRequest);

    expect($query['status'])->toBe(['open', 'escalated']);
});

it('returns a Ticket on getTicket', function (): void {
    $mockClient = new MockClient([
        GetTicketRequest::class => MockResponse::make([
            'data' => [
                'id'           => 42,
                'subject'      => 'Billing issue',
                'status'       => 'closed',
                'channel'      => ['id' => 'ch2', 'email' => 'billing@example.com'],
                'from_contact' => ['email' => 'jane@example.com', 'name' => 'Jane Doe'],
                'created_at'   => '2024-02-01T08:00:00Z',
            ],
        ], 200),
    ]);

    $connector = app(HelpSpaceConnector::class);
    $connector->withMockClient($mockClient);

    $ticket = (new HelpSpaceClient($connector))->getTicket(42);

    expect($ticket)->toBeInstanceOf(Ticket::class)
        ->and($ticket->id)->toBe(42)
        ->and($ticket->subject)->toBe('Billing issue')
        ->and($ticket->status)->toBe('closed');
});

it('returns a Ticket on createTicket', function (): void {
    $mockClient = new MockClient([
        CreateTicketRequest::class => MockResponse::make([
            'data' => [
                'id'           => 99,
                'subject'      => 'New request',
                'status'       => 'unassigned',
                'channel'      => ['id' => 'ch1', 'email' => 'support@example.com'],
                'from_contact' => ['email' => 'new@example.com', 'name' => 'New User'],
                'created_at'   => '2024-03-01T09:00:00Z',
            ],
        ], 201),
    ]);

    $connector = app(HelpSpaceConnector::class);
    $connector->withMockClient($mockClient);

    $ticket = (new HelpSpaceClient($connector))->createTicket([
        'subject'      => 'New request',
        'channel'      => ['id' => 'ch1'],
        'from_contact' => ['email' => 'new@example.com', 'name' => 'New User'],
        'message'      => ['body' => '<p>Hello</p>'],
    ]);

    expect($ticket)->toBeInstanceOf(Ticket::class)
        ->and($ticket->id)->toBe(99)
        ->and($ticket->subject)->toBe('New request');
});

it('throws InvalidArgumentException for empty subject on createTicket', function (): void {
    $client = new HelpSpaceClient(app(HelpSpaceConnector::class));

    expect(fn () => $client->createTicket(['subject' => '']))
        ->toThrow(\InvalidArgumentException::class);
});

it('returns updated Ticket on updateTicket', function (): void {
    $mockClient = new MockClient([
        UpdateTicketRequest::class => MockResponse::make([
            'data' => [
                'id'           => 55,
                'subject'      => 'Updated subject',
                'status'       => 'waiting',
                'from_contact' => ['email' => 'u@example.com', 'name' => 'User'],
                'created_at'   => '2024-01-01T00:00:00Z',
            ],
        ], 200),
    ]);

    $connector = app(HelpSpaceConnector::class);
    $connector->withMockClient($mockClient);

    $ticket = (new HelpSpaceClient($connector))->updateTicket(55, ['status' => 'waiting']);

    expect($ticket)->toBeInstanceOf(Ticket::class)
        ->and($ticket->status)->toBe('waiting');
});

it('returns true on deleteTicket', function (): void {
    $mockClient = new MockClient([
        DeleteTicketRequest::class => MockResponse::make([], 204),
    ]);

    $connector = app(HelpSpaceConnector::class);
    $connector->withMockClient($mockClient);

    $result = (new HelpSpaceClient($connector))->deleteTicket(10);

    expect($result)->toBeTrue();
});

it('throws HelpSpaceApiException on 401 for listTickets', function (): void {
    $mockClient = new MockClient([
        ListTicketsRequest::class => MockResponse::make(['error' => 'Unauthorized'], 401),
    ]);

    $connector = app(HelpSpaceConnector::class);
    $connector->withMockClient($mockClient);

    expect(fn () => (new HelpSpaceClient($connector))->listTickets())
        ->toThrow(HelpSpaceApiException::class);
});

it('throws HelpSpaceApiException on 401 for getTicket', function (): void {
    $mockClient = new MockClient([
        GetTicketRequest::class => MockResponse::make(['error' => 'Unauthorized'], 401),
    ]);

    $connector = app(HelpSpaceConnector::class);
    $connector->withMockClient($mockClient);

    expect(fn () => (new HelpSpaceClient($connector))->getTicket(1))
        ->toThrow(HelpSpaceApiException::class);
});
