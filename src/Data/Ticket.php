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
        public readonly ?string $creatorId,
        public readonly ?string $creatorName,
        public readonly array $tags,
        public readonly array $customFields,
        public readonly string $createdAt,
        public readonly ?string $updatedAt,
        public readonly ?string $lastContact,
        public readonly ?string $deletedAt,
        /** The channel's display name (`channel.name`). */
        public readonly ?string $channelName = null,
        /** The channel's kind, e.g. `email` (`channel.type`). */
        public readonly ?string $channelType = null,
        /** The contact's ID (`from_contact.id`). */
        public readonly ?string $contactId = null,
        /** The contact's customer ID (`from_contact.user_id`). */
        public readonly ?string $contactUserId = null,
        /** The assigned agent's email (`assignee.email`). */
        public readonly ?string $assigneeEmail = null,
        /** The creating agent's email (`creator.email`). */
        public readonly ?string $creatorEmail = null,
    ) {}

    /**
     * @param  array<string, mixed>  $item  HelpSpace's actual `channel`/`from_contact` objects carry the
     *                                        address under `value` (not `email`); `email` is kept as a
     *                                        fallback only in case an older API version returns it.
     */
    public static function fromArray(array $item): self
    {
        $channel  = $item['channel']      ?? [];
        $contact  = $item['from_contact'] ?? [];
        $assignee = $item['assignee']     ?? [];
        $team     = $item['team']         ?? [];
        $creator  = $item['creator']      ?? [];

        return new self(
            id: (int) ($item['id'] ?? 0),
            subject: (string) ($item['subject'] ?? ''),
            status: (string) ($item['status'] ?? ''),
            channelId: isset($channel['id']) ? (string) $channel['id'] : null,
            channelEmail: isset($channel['value']) ? (string) $channel['value'] : (isset($channel['email']) ? (string) $channel['email'] : null),
            contactEmail: isset($contact['value']) ? (string) $contact['value'] : (isset($contact['email']) ? (string) $contact['email'] : null),
            contactName: isset($contact['name']) ? (string) $contact['name'] : null,
            assigneeId: isset($assignee['id']) ? (string) $assignee['id'] : null,
            assigneeName: isset($assignee['name']) ? (string) $assignee['name'] : null,
            teamId: isset($team['id']) ? (string) $team['id'] : null,
            teamName: isset($team['name']) ? (string) $team['name'] : null,
            creatorId: isset($creator['id']) ? (string) $creator['id'] : null,
            creatorName: isset($creator['name']) ? (string) $creator['name'] : null,
            tags: (array) ($item['tags'] ?? []),
            customFields: (array) ($item['custom_fields'] ?? []),
            createdAt: (string) ($item['created_at'] ?? ''),
            updatedAt: isset($item['updated_at']) ? (string) $item['updated_at'] : null,
            lastContact: isset($item['last_contact']) ? (string) $item['last_contact'] : null,
            deletedAt: isset($item['deleted_at']) ? (string) $item['deleted_at'] : null,
            channelName: isset($channel['name']) ? (string) $channel['name'] : null,
            channelType: isset($channel['type']) ? (string) $channel['type'] : null,
            contactId: isset($contact['id']) ? (string) $contact['id'] : null,
            contactUserId: isset($contact['user_id']) ? (string) $contact['user_id'] : null,
            assigneeEmail: isset($assignee['email']) ? (string) $assignee['email'] : null,
            creatorEmail: isset($creator['email']) ? (string) $creator['email'] : null,
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
            'creatorId'    => $this->creatorId,
            'creatorName'  => $this->creatorName,
            'tags'         => $this->tags,
            'customFields' => $this->customFields,
            'createdAt'    => $this->createdAt,
            'updatedAt'    => $this->updatedAt,
            'lastContact'  => $this->lastContact,
            'deletedAt'    => $this->deletedAt,
            'channelName'  => $this->channelName,
            'channelType'  => $this->channelType,
            'contactId'    => $this->contactId,
            'contactUserId' => $this->contactUserId,
            'assigneeEmail' => $this->assigneeEmail,
            'creatorEmail' => $this->creatorEmail,
        ];
    }
}
