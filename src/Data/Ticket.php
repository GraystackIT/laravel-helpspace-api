<?php

declare(strict_types=1);

namespace GraystackIT\HelpSpace\Data;

class Ticket
{
    public function __construct(
        public readonly int $id,
        public readonly string $subject,
        public readonly string $status,
        public readonly ?string $channelId,
        public readonly ?string $channelEmail,
        public readonly ?string $contactEmail,
        public readonly ?string $contactName,
        public readonly ?string $assigneeId,
        public readonly ?string $assigneeName,
        public readonly ?string $teamId,
        public readonly ?string $teamName,
        public readonly array $tags,
        public readonly array $customFields,
        public readonly string $createdAt,
        public readonly ?string $updatedAt,
    ) {}

    /**
     * @param  array<string, mixed>  $item
     */
    public static function fromArray(array $item): self
    {
        $channel  = $item['channel']  ?? [];
        $contact  = $item['from_contact'] ?? [];
        $assignee = $item['assignee'] ?? [];
        $team     = $item['team']     ?? [];

        return new self(
            id: (int) ($item['id'] ?? 0),
            subject: (string) ($item['subject'] ?? ''),
            status: (string) ($item['status'] ?? ''),
            channelId: isset($channel['id']) ? (string) $channel['id'] : null,
            channelEmail: isset($channel['email']) ? (string) $channel['email'] : null,
            contactEmail: isset($contact['email']) ? (string) $contact['email'] : null,
            contactName: isset($contact['name']) ? (string) $contact['name'] : null,
            assigneeId: isset($assignee['id']) ? (string) $assignee['id'] : null,
            assigneeName: isset($assignee['name']) ? (string) $assignee['name'] : null,
            teamId: isset($team['id']) ? (string) $team['id'] : null,
            teamName: isset($team['name']) ? (string) $team['name'] : null,
            tags: (array) ($item['tags'] ?? []),
            customFields: (array) ($item['custom_fields'] ?? []),
            createdAt: (string) ($item['created_at'] ?? ''),
            updatedAt: isset($item['updated_at']) ? (string) $item['updated_at'] : null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id'           => $this->id,
            'subject'      => $this->subject,
            'status'       => $this->status,
            'channelId'    => $this->channelId,
            'channelEmail' => $this->channelEmail,
            'contactEmail' => $this->contactEmail,
            'contactName'  => $this->contactName,
            'assigneeId'   => $this->assigneeId,
            'assigneeName' => $this->assigneeName,
            'teamId'       => $this->teamId,
            'teamName'     => $this->teamName,
            'tags'         => $this->tags,
            'customFields' => $this->customFields,
            'createdAt'    => $this->createdAt,
            'updatedAt'    => $this->updatedAt,
        ];
    }
}
