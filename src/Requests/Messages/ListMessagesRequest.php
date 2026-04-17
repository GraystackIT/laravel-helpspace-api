<?php

declare(strict_types=1);

namespace GraystackIT\HelpSpace\Requests\Messages;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class ListMessagesRequest extends Request
{
    protected Method $method = Method::GET;

    /**
     * @param  string[]  $additionalTypes  Supplement default types (external, widget, forward).
     *                                     Valid values: internal, error, bounce, event, ai-summary
     * @param  string[]  $types            Override returned types entirely.
     */
    public function __construct(
        private readonly int $ticketId,
        private readonly array $additionalTypes = [],
        private readonly array $types = [],
    ) {}

    public function resolveEndpoint(): string
    {
        return "/api/v1/tickets/{$this->ticketId}/messages";
    }

    protected function defaultQuery(): array
    {
        $query = [];

        if (! empty($this->additionalTypes)) {
            $query['additional-types'] = $this->additionalTypes;
        }

        if (! empty($this->types)) {
            $query['types'] = $this->types;
        }

        return $query;
    }
}
