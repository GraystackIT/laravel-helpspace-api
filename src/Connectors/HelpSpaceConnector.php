<?php

declare(strict_types=1);

namespace GraystackIT\HelpSpace\Connectors;

use Saloon\Http\Connector;
use Saloon\Traits\Plugins\AcceptsJson;
use Saloon\Traits\Plugins\AlwaysThrowOnErrors;

class HelpSpaceConnector extends Connector
{
    use AcceptsJson;
    use AlwaysThrowOnErrors;

    public function __construct(
        private readonly string $apiKey,
        private readonly string $clientId,
        private readonly string $baseUrl = 'https://api.helpspace.com',
    ) {}

    public function resolveBaseUrl(): string
    {
        return rtrim($this->baseUrl, '/');
    }

    protected function defaultHeaders(): array
    {
        return [
            'Authorization' => "Bearer {$this->apiKey}",
            'Hs-Client-Id'  => $this->clientId,
        ];
    }
}
