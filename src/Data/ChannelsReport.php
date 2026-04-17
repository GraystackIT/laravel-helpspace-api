<?php

declare(strict_types=1);

namespace GraystackIT\HelpSpace\Data;

class ChannelsReport
{
    public function __construct(
        public readonly array $dailyCounts,
        public readonly array $metrics,
        public readonly array $channels,
        public readonly array $tags,
        public readonly array $topCustomers,
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
            channels: (array) ($data['channels'] ?? []),
            tags: (array) ($data['tags'] ?? []),
            topCustomers: (array) ($data['top_customers'] ?? []),
            raw: $data,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'dailyCounts'  => $this->dailyCounts,
            'metrics'      => $this->metrics,
            'channels'     => $this->channels,
            'tags'         => $this->tags,
            'topCustomers' => $this->topCustomers,
        ];
    }
}
