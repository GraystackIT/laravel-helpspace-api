<?php

declare(strict_types=1);

namespace GraystackIT\HelpSpace\Data;

class Message
{
    public function __construct(
        public readonly int $id,
        public readonly string $subject,
        public readonly string $type,
        public readonly string $fromContactEmail,
        public readonly string $fromContactName,
        public readonly array $to,
        public readonly array $cc,
        public readonly array $bcc,
        public readonly string $body,
        public readonly array $attachments,
        public readonly array $inlineImages,
        public readonly string $createdAt,
        /** Whether the message is stored as a draft rather than sent. */
        public readonly bool $draft = false,
        /** The sender's contact ID (`from_contact.id`). */
        public readonly ?string $fromContactId = null,
        /** The sender's customer ID (`from_contact.user_id`). */
        public readonly ?string $fromContactUserId = null,
    ) {}

    /**
     * @param  array<string, mixed>  $item  HelpSpace's actual `from_contact` object carries the address
     *                                        under `value` (not `email`); `email` is kept as a fallback
     *                                        only in case an older API version returns it.
     */
    public static function fromArray(array $item): self
    {
        $from = $item['from_contact'] ?? [];

        return new self(
            id: (int) ($item['id'] ?? 0),
            subject: (string) ($item['subject'] ?? ''),
            type: (string) ($item['type'] ?? 'external'),
            fromContactEmail: (string) ($from['value'] ?? $from['email'] ?? ''),
            fromContactName: (string) ($from['name'] ?? ''),
            to: (array) ($item['to'] ?? []),
            cc: (array) ($item['cc'] ?? []),
            bcc: (array) ($item['bcc'] ?? []),
            body: (string) ($item['body'] ?? ''),
            attachments: (array) ($item['attachments'] ?? []),
            inlineImages: (array) ($item['inline_images'] ?? []),
            createdAt: (string) ($item['created_at'] ?? ''),
            draft: (bool) ($item['draft'] ?? false),
            fromContactId: isset($from['id']) ? (string) $from['id'] : null,
            fromContactUserId: isset($from['user_id']) ? (string) $from['user_id'] : null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id'               => $this->id,
            'subject'          => $this->subject,
            'type'             => $this->type,
            'fromContactEmail' => $this->fromContactEmail,
            'fromContactName'  => $this->fromContactName,
            'to'               => $this->to,
            'cc'               => $this->cc,
            'bcc'              => $this->bcc,
            'body'             => $this->body,
            'attachments'      => $this->attachments,
            'inlineImages'     => $this->inlineImages,
            'createdAt'        => $this->createdAt,
            'draft'            => $this->draft,
            'fromContactId'    => $this->fromContactId,
            'fromContactUserId' => $this->fromContactUserId,
        ];
    }
}
