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
     * @param  int       $page             The page of the paginated collection (1-based).
     */
    public function __construct(
        private readonly int $ticketId,
        private readonly array $additionalTypes = [],
        private readonly array $types = [],
        private readonly int $page = 1,
    ) {}

    public function resolveEndpoint(): string
    {
        return "/api/v1/tickets/{$this->ticketId}/messages";
    }

    /**
     * The type filters and page of the list; `page` is sent from the second page on.
     *
     * @return array<string, mixed>
     */
    protected function defaultQuery(): array
    {
        $query = [];

        if ($this->page > 1) {
            $query['page'] = $this->page;
        }

        if (! empty($this->additionalTypes)) {
            $query['additional-types'] = $this->additionalTypes;
        }

        if (! empty($this->types)) {
            $query['types'] = $this->types;
        }

        return $query;
    }
}
