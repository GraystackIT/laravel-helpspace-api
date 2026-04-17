<?php

declare(strict_types=1);

namespace GraystackIT\HelpSpace\Requests\Tickets;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class ListTicketsRequest extends Request
{
    protected Method $method = Method::GET;

    public function __construct(
        private readonly int $perPage = 20,
        private readonly array $subjects = [],
        private readonly array $bodies = [],
        private readonly array $subjectOrBody = [],
        private readonly array $contacts = [],
        private readonly array $assignees = [],
        private readonly array $organizations = [],
        private readonly array $teams = [],
        private readonly array $tags = [],
        private readonly array $statuses = [],
        private readonly ?string $createdBetween = null,
        private readonly ?string $lastContactBetween = null,
    ) {}

    public function resolveEndpoint(): string
    {
        return '/api/v1/tickets';
    }

    protected function defaultQuery(): array
    {
        $query = [
            'per-page' => min($this->perPage, 50),
        ];

        if (! empty($this->subjects)) {
            $query['subject'] = $this->subjects;
        }

        if (! empty($this->bodies)) {
            $query['body'] = $this->bodies;
        }

        if (! empty($this->subjectOrBody)) {
            $query['subject-or-body'] = $this->subjectOrBody;
        }

        if (! empty($this->contacts)) {
            $query['contacts'] = $this->contacts;
        }

        if (! empty($this->assignees)) {
            $query['assignees'] = $this->assignees;
        }

        if (! empty($this->organizations)) {
            $query['organizations'] = $this->organizations;
        }

        if (! empty($this->teams)) {
            $query['teams'] = $this->teams;
        }

        if (! empty($this->tags)) {
            $query['tags'] = $this->tags;
        }

        if (! empty($this->statuses)) {
            $query['status'] = $this->statuses;
        }

        if ($this->createdBetween !== null) {
            $query['created-between'] = $this->createdBetween;
        }

        if ($this->lastContactBetween !== null) {
            $query['last-contact-between'] = $this->lastContactBetween;
        }

        return $query;
    }
}
