<?php

declare(strict_types=1);

use GraystackIT\HelpSpace\Data\Ticket;

it('builds Ticket from full array', function (): void {
    $ticket = Ticket::fromArray([
        'id'           => 1,
        'subject'      => 'Login error',
        'status'       => 'open',
        'channel'      => ['id' => 'ch1', 'email' => 'support@acme.com'],
        'from_contact' => ['email' => 'alice@example.com', 'name' => 'Alice'],
        'assignee'     => ['id' => 'ag1', 'name' => 'Bob'],
        'team'         => ['id' => 'tm1', 'name' => 'Support'],
        'creator'      => ['id' => 'cr1', 'name' => 'Admin'],
        'tags'         => ['billing', 'urgent'],
        'custom_fields' => ['priority_level' => 'high'],
        'created_at'   => '2024-01-01T00:00:00Z',
        'updated_at'   => '2024-01-02T00:00:00Z',
        'last_contact' => '2024-01-03T00:00:00Z',
        'deleted_at'   => null,
    ]);

    expect($ticket->id)->toBe(1)
        ->and($ticket->subject)->toBe('Login error')
        ->and($ticket->status)->toBe('open')
        ->and($ticket->channelId)->toBe('ch1')
        ->and($ticket->channelEmail)->toBe('support@acme.com')
        ->and($ticket->contactEmail)->toBe('alice@example.com')
        ->and($ticket->contactName)->toBe('Alice')
        ->and($ticket->assigneeId)->toBe('ag1')
        ->and($ticket->assigneeName)->toBe('Bob')
        ->and($ticket->teamId)->toBe('tm1')
        ->and($ticket->teamName)->toBe('Support')
        ->and($ticket->creatorId)->toBe('cr1')
        ->and($ticket->creatorName)->toBe('Admin')
        ->and($ticket->tags)->toBe(['billing', 'urgent'])
        ->and($ticket->customFields)->toBe(['priority_level' => 'high'])
        ->and($ticket->createdAt)->toBe('2024-01-01T00:00:00Z')
        ->and($ticket->updatedAt)->toBe('2024-01-02T00:00:00Z')
        ->and($ticket->lastContact)->toBe('2024-01-03T00:00:00Z')
        ->and($ticket->deletedAt)->toBeNull();
});

it('handles missing optional fields gracefully', function (): void {
    $ticket = Ticket::fromArray([
        'id'         => 2,
        'subject'    => 'Minimal ticket',
        'status'     => 'unassigned',
        'created_at' => '2024-05-01T00:00:00Z',
    ]);

    expect($ticket->channelId)->toBeNull()
        ->and($ticket->channelEmail)->toBeNull()
        ->and($ticket->contactEmail)->toBeNull()
        ->and($ticket->assigneeId)->toBeNull()
        ->and($ticket->teamId)->toBeNull()
        ->and($ticket->creatorId)->toBeNull()
        ->and($ticket->creatorName)->toBeNull()
        ->and($ticket->tags)->toBe([])
        ->and($ticket->customFields)->toBe([])
        ->and($ticket->updatedAt)->toBeNull()
        ->and($ticket->lastContact)->toBeNull()
        ->and($ticket->deletedAt)->toBeNull();
});

it('serializes to array via toArray', function (): void {
    $ticket = Ticket::fromArray([
        'id'           => 3,
        'subject'      => 'Test',
        'status'       => 'closed',
        'from_contact' => ['email' => 'x@y.com', 'name' => 'X'],
        'created_at'   => '2024-06-01T00:00:00Z',
    ]);

    $arr = $ticket->toArray();

    expect($arr)->toHaveKey('id')
        ->and($arr)->toHaveKey('subject')
        ->and($arr)->toHaveKey('status')
        ->and($arr)->toHaveKey('creatorId')
        ->and($arr)->toHaveKey('creatorName')
        ->and($arr)->toHaveKey('lastContact')
        ->and($arr)->toHaveKey('deletedAt')
        ->and($arr['id'])->toBe(3)
        ->and($arr['subject'])->toBe('Test');
});

it('casts id to int', function (): void {
    $ticket = Ticket::fromArray([
        'id'         => '42',
        'subject'    => 'String ID',
        'status'     => 'open',
        'created_at' => '2024-01-01T00:00:00Z',
    ]);

    expect($ticket->id)->toBe(42);
});
