<?php

declare(strict_types=1);

namespace GraystackIT\HelpSpace\Requests\Messages;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class GetMessageRequest extends Request
{
    protected Method $method = Method::GET;

    public function __construct(
        private readonly int $ticketId,
        private readonly int $messageId,
    ) {}

    public function resolveEndpoint(): string
    {
        return "/api/v1/tickets/{$this->ticketId}/messages/{$this->messageId}";
    }
}
