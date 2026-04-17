<?php

declare(strict_types=1);

namespace GraystackIT\HelpSpace\Requests\Tickets;

use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Traits\Body\HasJsonBody;

class CreateTicketRequest extends Request implements HasBody
{
    use HasJsonBody;

    protected Method $method = Method::POST;

    /**
     * @param  array<string, mixed>  $data  Ticket payload matching HelpSpace API schema.
     *                                       Required keys: subject, channel (with id/email),
     *                                       from_contact (with email/name), message (with body).
     */
    public function __construct(private readonly array $data) {}

    public function resolveEndpoint(): string
    {
        return '/api/v1/tickets';
    }

    protected function defaultBody(): array
    {
        return $this->data;
    }
}
