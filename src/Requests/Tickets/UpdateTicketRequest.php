<?php

declare(strict_types=1);

namespace GraystackIT\HelpSpace\Requests\Tickets;

use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Traits\Body\HasJsonBody;

class UpdateTicketRequest extends Request implements HasBody
{
    use HasJsonBody;

    protected Method $method = Method::PATCH;

    /**
     * @param  array<string, mixed>  $data  Fields to update (status, assignee, team, tags, custom_fields, etc.)
     */
    public function __construct(
        private readonly int $ticketId,
        private readonly array $data,
    ) {}

    public function resolveEndpoint(): string
    {
        return "/api/v1/tickets/{$this->ticketId}";
    }

    protected function defaultBody(): array
    {
        return $this->data;
    }
}
