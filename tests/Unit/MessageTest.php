<?php

declare(strict_types=1);

use GraystackIT\HelpSpace\Data\Message;

it('builds Message from full array', function (): void {
    $message = Message::fromArray([
        'id'           => 10,
        'subject'      => 'Re: your request',
        'type'         => 'external',
        'from_contact' => ['value' => 'user@example.com', 'name' => 'User'],
        'to'           => ['agent@example.com'],
        'cc'           => ['cc@example.com'],
        'bcc'          => [],
        'body'         => '<p>Hello!</p>',
        'attachments'  => [['file_name' => 'doc.pdf', 'size' => 1024]],
        'inline_images' => [],
        'created_at'   => '2024-02-01T08:00:00Z',
    ]);

    expect($message->id)->toBe(10)
        ->and($message->subject)->toBe('Re: your request')
        ->and($message->type)->toBe('external')
        ->and($message->fromContactEmail)->toBe('user@example.com')
        ->and($message->fromContactName)->toBe('User')
        ->and($message->to)->toBe(['agent@example.com'])
        ->and($message->cc)->toBe(['cc@example.com'])
        ->and($message->bcc)->toBe([])
        ->and($message->body)->toBe('<p>Hello!</p>')
        ->and($message->attachments)->toHaveCount(1)
        ->and($message->createdAt)->toBe('2024-02-01T08:00:00Z');
});

it('handles missing optional fields gracefully', function (): void {
    $message = Message::fromArray([
        'id'         => 20,
        'type'       => 'internal',
        'body'       => '<p>Note.</p>',
        'created_at' => '2024-03-01T00:00:00Z',
    ]);

    expect($message->subject)->toBe('')
        ->and($message->fromContactEmail)->toBe('')
        ->and($message->fromContactName)->toBe('')
        ->and($message->to)->toBe([])
        ->and($message->cc)->toBe([])
        ->and($message->bcc)->toBe([])
        ->and($message->attachments)->toBe([])
        ->and($message->inlineImages)->toBe([]);
});

it('reads from_contact address from value (the real API field), falling back to email', function (): void {
    $message = Message::fromArray([
        'id'           => 25,
        'type'         => 'external',
        'from_contact' => ['email' => 'legacy@example.com', 'name' => 'Legacy'],
        'body'         => '<p>Legacy field.</p>',
        'created_at'   => '2024-03-15T00:00:00Z',
    ]);

    expect($message->fromContactEmail)->toBe('legacy@example.com');
});

it('defaults type to external when absent', function (): void {
    $message = Message::fromArray([
        'id'         => 30,
        'body'       => '<p>Hi</p>',
        'created_at' => '2024-04-01T00:00:00Z',
    ]);

    expect($message->type)->toBe('external');
});

it('serializes to array via toArray', function (): void {
    $message = Message::fromArray([
        'id'           => 40,
        'type'         => 'forward',
        'from_contact' => ['email' => 'a@b.com', 'name' => 'A'],
        'body'         => '<p>Fwd</p>',
        'created_at'   => '2024-05-01T00:00:00Z',
    ]);

    $arr = $message->toArray();

    expect($arr)->toHaveKey('id')
        ->and($arr)->toHaveKey('subject')
        ->and($arr)->toHaveKey('type')
        ->and($arr)->toHaveKey('body')
        ->and($arr['id'])->toBe(40)
        ->and($arr['type'])->toBe('forward');
});

it('maps the draft flag and the sender ids', function (): void {
    $message = Message::fromArray([
        'id'           => 17916,
        'from_contact' => ['id' => 1013, 'user_id' => 1018, 'name' => 'My Custom Name', 'value' => 'someone@example.com', 'type' => 'email'],
        'type'         => 'external',
        'body'         => '<p>Hi</p>',
        'draft'        => true,
        'created_at'   => '2022-04-21T10:24:40+00:00',
    ]);

    expect($message->draft)->toBeTrue()
        ->and($message->fromContactId)->toBe('1013')
        ->and($message->fromContactUserId)->toBe('1018')
        ->and($message->toArray())->toHaveKeys(['draft', 'fromContactId', 'fromContactUserId']);
});

it('treats a message without a draft flag as sent', function (): void {
    expect(Message::fromArray(['id' => 1])->draft)->toBeFalse();
});
