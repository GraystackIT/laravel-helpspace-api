<?php

declare(strict_types=1);

namespace GraystackIT\HelpSpace\Data;

class WebhookConfig
{
    public function __construct(
        public readonly bool $enabled,
        public readonly string $url,
        public readonly ?string $secret,
        public readonly array $headers,
        public readonly array $trigger,
        public readonly int $failedCount,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        $webhook = $data['webhooks'] ?? $data;

        return new self(
            enabled: (bool) ($webhook['enabled'] ?? false),
            url: (string) ($webhook['url'] ?? ''),
            secret: isset($webhook['secret']) ? (string) $webhook['secret'] : null,
            headers: (array) ($webhook['headers'] ?? []),
            trigger: (array) ($webhook['trigger'] ?? []),
            failedCount: (int) ($webhook['failed_count'] ?? 0),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'enabled'     => $this->enabled,
            'url'         => $this->url,
            'secret'      => $this->secret,
            'headers'     => $this->headers,
            'trigger'     => $this->trigger,
            'failedCount' => $this->failedCount,
        ];
    }
}
