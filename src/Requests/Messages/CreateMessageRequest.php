<?php

declare(strict_types=1);

namespace GraystackIT\HelpSpace\Requests\Messages;

use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Traits\Body\HasJsonBody;

class CreateMessageRequest extends Request implements HasBody
{
    use HasJsonBody;

    protected Method $method = Method::POST;

    /**
     * @param  array<string, mixed>  $data  Message payload.
     *                                       Required keys: from_contact (email/name), body, subject.
     *                                       Optional: type, to, cc, bcc, attachments, inline_images,
     *                                       skip_notifications, send_mail_to_recipients, created_at
     */
    public function __construct(
        private readonly int $ticketId,
        private readonly array $data,
    ) {}

    public function resolveEndpoint(): string
    {
        return "/api/v1/tickets/{$this->ticketId}/messages";
    }

    protected function defaultBody(): array
    {
        return $this->data;
    }
}
