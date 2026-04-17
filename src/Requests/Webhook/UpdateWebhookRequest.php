<?php

declare(strict_types=1);

namespace GraystackIT\HelpSpace\Requests\Webhook;

use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Traits\Body\HasJsonBody;

class UpdateWebhookRequest extends Request implements HasBody
{
    use HasJsonBody;

    protected Method $method = Method::POST;

    /**
     * @param  array<string, mixed>  $config  Webhook configuration.
     *                                         Keys: enabled (bool), url (string), secret (string),
     *                                         headers (array of {key, value}), trigger (object with
     *                                         ticket/customer/tag boolean maps)
     */
    public function __construct(private readonly array $webhookConfig) {}

    public function resolveEndpoint(): string
    {
        return '/api/v1/webhook';
    }

    protected function defaultBody(): array
    {
        return ['webhooks' => $this->webhookConfig];
    }
}
