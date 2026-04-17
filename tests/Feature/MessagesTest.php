<?php

declare(strict_types=1);

use GraystackIT\HelpSpace\Connectors\HelpSpaceConnector;
use GraystackIT\HelpSpace\Data\Message;
use GraystackIT\HelpSpace\Exceptions\HelpSpaceApiException;
use GraystackIT\HelpSpace\HelpSpaceClient;
use GraystackIT\HelpSpace\Requests\Messages\CreateMessageRequest;
use GraystackIT\HelpSpace\Requests\Messages\GetMessageRequest;
use GraystackIT\HelpSpace\Requests\Messages\ListMessagesRequest;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

it('returns Message array on listMessages', function (): void {
    $mockClient = new MockClient([
        ListMessagesRequest::class => MockResponse::make([
            'data' => [
                [
                    'id'           => 1,
                    'type'         => 'external',
                    'from_contact' => ['email' => 'user@example.com', 'name' => 'John'],
                    'to'           => ['support@example.com'],
                    'cc'           => [],
                    'bcc'          => [],
                    'body'         => '<p>Hello, I need help.</p>',
                    'attachments'  => [],
                    'inline_images' => [],
                    'created_at'   => '2024-01-10T12:00:00Z',
                ],
            ],
        ], 200),
    ]);

    $connector = app(HelpSpaceConnector::class);
    $connector->withMockClient($mockClient);

    $messages = (new HelpSpaceClient($connector))->listMessages(101);

    expect($messages)->toHaveCount(1)
        ->and($messages[0])->toBeInstanceOf(Message::class)
        ->and($messages[0]->id)->toBe(1)
        ->and($messages[0]->type)->toBe('external')
        ->and($messages[0]->fromContactEmail)->toBe('user@example.com')
        ->and($messages[0]->fromContactName)->toBe('John')
        ->and($messages[0]->body)->toBe('<p>Hello, I need help.</p>')
        ->and($messages[0]->createdAt)->toBe('2024-01-10T12:00:00Z');
});

it('returns empty array when no messages', function (): void {
    $mockClient = new MockClient([
        ListMessagesRequest::class => MockResponse::make(['data' => []], 200),
    ]);

    $connector = app(HelpSpaceConnector::class);
    $connector->withMockClient($mockClient);

    $messages = (new HelpSpaceClient($connector))->listMessages(101);

    expect($messages)->toBe([]);
});

it('passes additional-types to listMessages request', function (): void {
    $mockClient = new MockClient([
        ListMessagesRequest::class => MockResponse::make(['data' => []], 200),
    ]);

    $connector = app(HelpSpaceConnector::class);
    $connector->withMockClient($mockClient);

    (new HelpSpaceClient($connector))->listMessages(101, additionalTypes: ['internal']);

    $lastRequest = $mockClient->getLastRequest();
    $reflection  = new ReflectionMethod($lastRequest, 'defaultQuery');
    $query       = $reflection->invoke($lastRequest);

    expect($query['additional-types'])->toBe(['internal']);
});

it('passes types override to listMessages request', function (): void {
    $mockClient = new MockClient([
        ListMessagesRequest::class => MockResponse::make(['data' => []], 200),
    ]);

    $connector = app(HelpSpaceConnector::class);
    $connector->withMockClient($mockClient);

    (new HelpSpaceClient($connector))->listMessages(101, types: ['internal', 'event']);

    $lastRequest = $mockClient->getLastRequest();
    $reflection  = new ReflectionMethod($lastRequest, 'defaultQuery');
    $query       = $reflection->invoke($lastRequest);

    expect($query['types'])->toBe(['internal', 'event']);
});

it('returns Message on getMessage', function (): void {
    $mockClient = new MockClient([
        GetMessageRequest::class => MockResponse::make([
            'data' => [
                'id'           => 77,
                'type'         => 'internal',
                'from_contact' => ['email' => 'agent@example.com', 'name' => 'Agent'],
                'body'         => '<p>Internal note.</p>',
                'created_at'   => '2024-01-11T09:00:00Z',
            ],
        ], 200),
    ]);

    $connector = app(HelpSpaceConnector::class);
    $connector->withMockClient($mockClient);

    $message = (new HelpSpaceClient($connector))->getMessage(101, 77);

    expect($message)->toBeInstanceOf(Message::class)
        ->and($message->id)->toBe(77)
        ->and($message->type)->toBe('internal');
});

it('returns Message on createMessage', function (): void {
    $mockClient = new MockClient([
        CreateMessageRequest::class => MockResponse::make([
            'data' => [
                'id'           => 88,
                'type'         => 'external',
                'from_contact' => ['email' => 'reply@example.com', 'name' => 'Agent'],
                'body'         => '<p>We are looking into this.</p>',
                'created_at'   => '2024-01-12T14:00:00Z',
            ],
        ], 201),
    ]);

    $connector = app(HelpSpaceConnector::class);
    $connector->withMockClient($mockClient);

    $message = (new HelpSpaceClient($connector))->createMessage(101, [
        'from_contact' => ['email' => 'reply@example.com', 'name' => 'Agent'],
        'subject'      => 'Re: your ticket',
        'body'         => '<p>We are looking into this.</p>',
    ]);

    expect($message)->toBeInstanceOf(Message::class)
        ->and($message->id)->toBe(88)
        ->and($message->body)->toBe('<p>We are looking into this.</p>');
});

it('throws InvalidArgumentException for empty body on createMessage', function (): void {
    $client = new HelpSpaceClient(app(HelpSpaceConnector::class));

    expect(fn () => $client->createMessage(101, ['body' => '']))
        ->toThrow(\InvalidArgumentException::class);
});

it('throws HelpSpaceApiException on 401 for listMessages', function (): void {
    $mockClient = new MockClient([
        ListMessagesRequest::class => MockResponse::make(['error' => 'Unauthorized'], 401),
    ]);

    $connector = app(HelpSpaceConnector::class);
    $connector->withMockClient($mockClient);

    expect(fn () => (new HelpSpaceClient($connector))->listMessages(1))
        ->toThrow(HelpSpaceApiException::class);
});
