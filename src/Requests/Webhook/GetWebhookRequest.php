<?php

declare(strict_types=1);

namespace GraystackIT\HelpSpace\Requests\Webhook;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class GetWebhookRequest extends Request
{
    protected Method $method = Method::GET;

    public function resolveEndpoint(): string
    {
        return '/api/v1/webhook';
    }
}
