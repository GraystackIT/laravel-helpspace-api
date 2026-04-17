<?php

declare(strict_types=1);

namespace GraystackIT\HelpSpace\Data;

class Message
{
    public function __construct(
        public readonly int $id,
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
    ) {}

    /**
     * @param  array<string, mixed>  $item
     */
    public static function fromArray(array $item): self
    {
        $from = $item['from_contact'] ?? [];

        return new self(
            id: (int) ($item['id'] ?? 0),
            type: (string) ($item['type'] ?? 'external'),
            fromContactEmail: (string) ($from['email'] ?? ''),
            fromContactName: (string) ($from['name'] ?? ''),
            to: (array) ($item['to'] ?? []),
            cc: (array) ($item['cc'] ?? []),
            bcc: (array) ($item['bcc'] ?? []),
            body: (string) ($item['body'] ?? ''),
            attachments: (array) ($item['attachments'] ?? []),
            inlineImages: (array) ($item['inline_images'] ?? []),
            createdAt: (string) ($item['created_at'] ?? ''),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id'               => $this->id,
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
        ];
    }
}
