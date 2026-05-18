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
        // API returns parallel arrays; zip them into per-day objects.
        $labels = (array) ($data['labels'] ?? []);
        $opened = (array) ($data['opened'] ?? []);
        $closed = (array) ($data['closed'] ?? []);
        $dailyCounts = [];
        foreach ($labels as $i => $label) {
            $dailyCounts[] = [
                'date'   => $label,
                'opened' => $opened[$i] ?? 0,
                'closed' => $closed[$i] ?? 0,
            ];
        }

        return new self(
            dailyCounts: $dailyCounts,
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
