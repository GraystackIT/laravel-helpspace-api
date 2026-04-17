<?php

declare(strict_types=1);

namespace GraystackIT\HelpSpace\Data;

class PerformanceReport
{
    public function __construct(
        public readonly array $dailyCounts,
        public readonly array $metrics,
        public readonly array $topAgents,
        public readonly array $raw,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            dailyCounts: (array) ($data['daily_counts'] ?? []),
            metrics: (array) ($data['metrics'] ?? []),
            topAgents: (array) ($data['top_agents'] ?? []),
            raw: $data,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'dailyCounts' => $this->dailyCounts,
            'metrics'     => $this->metrics,
            'topAgents'   => $this->topAgents,
        ];
    }
}
