<?php

declare(strict_types=1);

namespace GraystackIT\HelpSpace\Requests\Tickets;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class DeleteTicketRequest extends Request
{
    protected Method $method = Method::DELETE;

    public function __construct(private readonly int $ticketId) {}

    public function resolveEndpoint(): string
    {
        return "/api/v1/tickets/{$this->ticketId}";
    }
}
