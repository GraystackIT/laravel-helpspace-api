<?php

declare(strict_types=1);

namespace GraystackIT\HelpSpace\Requests\Reports;

use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Traits\Body\HasJsonBody;

class ChannelsReportRequest extends Request implements HasBody
{
    use HasJsonBody;

    protected Method $method = Method::POST;

    /**
     * @param  string  $start  Start date in YYYY-MM-DD format
     * @param  string  $end    End date in YYYY-MM-DD format
     */
    public function __construct(
        private readonly string $start,
        private readonly string $end,
    ) {}

    public function resolveEndpoint(): string
    {
        return '/api/v1/reports/channels';
    }

    protected function defaultBody(): array
    {
        return [
            'start' => $this->start,
            'end'   => $this->end,
        ];
    }
}
