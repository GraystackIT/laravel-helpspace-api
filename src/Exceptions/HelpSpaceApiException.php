<?php

declare(strict_types=1);

namespace GraystackIT\HelpSpace\Exceptions;

use RuntimeException;
use Saloon\Exceptions\Request\RequestException;

/**
 * A HelpSpace API call that failed.
 *
 * For an HTTP error the code is the status. A `422` carries the API's
 * per-field `errors`, and every response carries the account's rate-limit
 * state: 30 requests per minute, with each write counting as two.
 */
class HelpSpaceApiException extends RuntimeException
{
    /**
     * @param  string  $message  The exception message.
     * @param  int  $code  The HTTP status, or 0 when the API was not reached.
     * @param  \Throwable|null  $previous  The underlying failure.
     * @param  string|null  $apiMessage  The `message` HelpSpace answered with.
     * @param  array<string, string[]>  $validationErrors  A `422`'s `errors`, keyed by field.
     * @param  int|null  $rateLimitLimit  `X-RateLimit-Limit`: requests allowed per minute.
     * @param  int|null  $rateLimitRemaining  `X-RateLimit-Remaining`: requests left this minute.
     */
    public function __construct(
        string $message = '',
        int $code = 0,
        ?\Throwable $previous = null,
        public readonly ?string $apiMessage = null,
        public readonly array $validationErrors = [],
        public readonly ?int $rateLimitLimit = null,
        public readonly ?int $rateLimitRemaining = null,
    ) {
        parent::__construct($message, $code, $previous);
    }

    /**
     * Build the exception for an HTTP error the API answered with.
     *
     * @param  RequestException  $e  The failed request.
     * @return self
     */
    public static function fromRequestException(RequestException $e): self
    {
        $response = $e->getResponse();
        $status   = $response->status();
        $body     = json_decode($response->body(), true);
        $body     = is_array($body) ? $body : [];

        $errors = [];

        foreach ((array) ($body['errors'] ?? []) as $field => $messages) {
            $errors[(string) $field] = array_values(array_map('strval', array_filter((array) $messages, 'is_scalar')));
        }

        return new self(
            "HelpSpace API returned HTTP {$status}",
            $status,
            $e,
            apiMessage: is_string($body['message'] ?? null) ? $body['message'] : null,
            validationErrors: $errors,
            rateLimitLimit: self::headerInteger($response->header('X-RateLimit-Limit')),
            rateLimitRemaining: self::headerInteger($response->header('X-RateLimit-Remaining')),
        );
    }

    /**
     * Whether the API refused the call because the account's rate limit was reached.
     *
     * @return bool
     */
    public function isRateLimited(): bool
    {
        return $this->getCode() === 429;
    }

    /**
     * Whether the API rejected the request's data (`422`).
     *
     * @return bool
     */
    public function isValidationError(): bool
    {
        return $this->getCode() === 422;
    }

    /**
     * Parse a numeric header.
     *
     * @param  string|array<int, string>|null  $value  The header value.
     * @return int|null
     */
    private static function headerInteger(string|array|null $value): ?int
    {
        if (is_array($value)) {
            $value = $value[0] ?? null;
        }

        return is_string($value) && is_numeric(trim($value)) ? (int) trim($value) : null;
    }
}
