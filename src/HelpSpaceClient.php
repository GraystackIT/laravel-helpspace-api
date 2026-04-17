<?php

declare(strict_types=1);

namespace GraystackIT\HelpSpace;

use GraystackIT\HelpSpace\Connectors\HelpSpaceConnector;
use GraystackIT\HelpSpace\Data\ChannelsReport;
use GraystackIT\HelpSpace\Data\Message;
use GraystackIT\HelpSpace\Data\PerformanceReport;
use GraystackIT\HelpSpace\Data\Ticket;
use GraystackIT\HelpSpace\Data\WebhookConfig;
use GraystackIT\HelpSpace\Exceptions\HelpSpaceApiException;
use GraystackIT\HelpSpace\Requests\Messages\CreateMessageRequest;
use GraystackIT\HelpSpace\Requests\Messages\GetMessageRequest;
use GraystackIT\HelpSpace\Requests\Messages\ListMessagesRequest;
use GraystackIT\HelpSpace\Requests\Reports\ChannelsReportRequest;
use GraystackIT\HelpSpace\Requests\Reports\PerformanceReportRequest;
use GraystackIT\HelpSpace\Requests\Tickets\CreateTicketRequest;
use GraystackIT\HelpSpace\Requests\Tickets\DeleteTicketRequest;
use GraystackIT\HelpSpace\Requests\Tickets\GetTicketRequest;
use GraystackIT\HelpSpace\Requests\Tickets\ListTicketsRequest;
use GraystackIT\HelpSpace\Requests\Tickets\UpdateTicketRequest;
use GraystackIT\HelpSpace\Requests\Webhook\GetWebhookLogsRequest;
use GraystackIT\HelpSpace\Requests\Webhook\GetWebhookRequest;
use GraystackIT\HelpSpace\Requests\Webhook\UpdateWebhookRequest;
use Illuminate\Support\Facades\Log;
use Saloon\Exceptions\Request\RequestException;

class HelpSpaceClient
{
    public function __construct(private readonly HelpSpaceConnector $connector) {}

    // -------------------------------------------------------------------------
    // Tickets
    // -------------------------------------------------------------------------

    /**
     * Retrieve a paginated list of tickets with optional filters.
     *
     * @param  int      $perPage             Results per page (max 50)
     * @param  string[] $statuses            Filter by status values (unassigned|open|escalated|spam|waiting|closed)
     * @param  string[] $contacts            Filter by contact email
     * @param  string[] $assignees           Filter by assignee name or ID
     * @param  string[] $tags                Filter by tag names
     * @param  string[] $subjects            Filter by subject text
     * @param  string[] $bodies              Filter by body text
     * @param  string[] $subjectOrBody       Filter by subject or body text
     * @param  string[] $organizations       Filter by organization
     * @param  string[] $teams               Filter by team
     * @param  string|null $createdBetween   Date range YYYY-MM-DD/YYYY-MM-DD
     * @param  string|null $lastContactBetween  Date range YYYY-MM-DD/YYYY-MM-DD
     * @return array{data: Ticket[], meta: array<string,mixed>, links: array<string,mixed>}
     *
     * @throws HelpSpaceApiException
     */
    public function listTickets(
        int $perPage = 20,
        array $statuses = [],
        array $contacts = [],
        array $assignees = [],
        array $tags = [],
        array $subjects = [],
        array $bodies = [],
        array $subjectOrBody = [],
        array $organizations = [],
        array $teams = [],
        ?string $createdBetween = null,
        ?string $lastContactBetween = null,
    ): array {
        Log::info('HelpSpace: listing tickets', ['perPage' => $perPage, 'statuses' => $statuses]);

        $data = $this->send(new ListTicketsRequest(
            perPage: $perPage,
            subjects: $subjects,
            bodies: $bodies,
            subjectOrBody: $subjectOrBody,
            contacts: $contacts,
            assignees: $assignees,
            organizations: $organizations,
            teams: $teams,
            tags: $tags,
            statuses: $statuses,
            createdBetween: $createdBetween,
            lastContactBetween: $lastContactBetween,
        ));

        $tickets = array_map(
            static fn (array $item) => Ticket::fromArray($item),
            $data['data'] ?? []
        );

        Log::info('HelpSpace: tickets listed', ['count' => count($tickets)]);

        return [
            'data'  => $tickets,
            'meta'  => $data['meta']  ?? [],
            'links' => $data['links'] ?? [],
        ];
    }

    /**
     * Retrieve a single ticket by ID.
     *
     * @throws HelpSpaceApiException
     */
    public function getTicket(int $id): Ticket
    {
        Log::info('HelpSpace: getting ticket', ['id' => $id]);

        $data = $this->send(new GetTicketRequest($id));

        return Ticket::fromArray($data['data'] ?? $data);
    }

    /**
     * Create a new ticket.
     *
     * @param  array<string, mixed>  $payload  Required: subject, channel (id/email), from_contact (email/name),
     *                                          message (body). Optional: status, assignee, team, tags,
     *                                          custom_fields, skip_rules, skip_autoreply, skip_notifications
     * @throws \InvalidArgumentException
     * @throws HelpSpaceApiException
     */
    public function createTicket(array $payload): Ticket
    {
        if (empty($payload['subject'])) {
            throw new \InvalidArgumentException('Ticket subject must not be empty.');
        }

        Log::info('HelpSpace: creating ticket', ['subject' => $payload['subject']]);

        $data = $this->send(new CreateTicketRequest($payload));

        return Ticket::fromArray($data['data'] ?? $data);
    }

    /**
     * Update an existing ticket.
     *
     * @param  array<string, mixed>  $payload  Fields to update: status, assignee, team, tags, custom_fields, etc.
     * @throws HelpSpaceApiException
     */
    public function updateTicket(int $id, array $payload): Ticket
    {
        Log::info('HelpSpace: updating ticket', ['id' => $id]);

        $data = $this->send(new UpdateTicketRequest($id, $payload));

        return Ticket::fromArray($data['data'] ?? $data);
    }

    /**
     * Soft-delete a ticket. Permanently removed after 30 days.
     *
     * @throws HelpSpaceApiException
     */
    public function deleteTicket(int $id): bool
    {
        Log::info('HelpSpace: deleting ticket', ['id' => $id]);

        $this->send(new DeleteTicketRequest($id));

        return true;
    }

    // -------------------------------------------------------------------------
    // Messages
    // -------------------------------------------------------------------------

    /**
     * Retrieve all messages for a ticket.
     *
     * Default types returned: external, widget, forward.
     *
     * @param  string[]  $additionalTypes  Supplement defaults: internal, error, bounce, event, ai-summary
     * @param  string[]  $types            Override returned types entirely
     * @return Message[]
     *
     * @throws HelpSpaceApiException
     */
    public function listMessages(
        int $ticketId,
        array $additionalTypes = [],
        array $types = [],
    ): array {
        Log::info('HelpSpace: listing messages', ['ticketId' => $ticketId]);

        $data = $this->send(new ListMessagesRequest($ticketId, $additionalTypes, $types));

        $messages = array_map(
            static fn (array $item) => Message::fromArray($item),
            $data['data'] ?? []
        );

        Log::info('HelpSpace: messages listed', ['ticketId' => $ticketId, 'count' => count($messages)]);

        return $messages;
    }

    /**
     * Retrieve a single message by ticket and message ID.
     *
     * @throws HelpSpaceApiException
     */
    public function getMessage(int $ticketId, int $messageId): Message
    {
        Log::info('HelpSpace: getting message', ['ticketId' => $ticketId, 'messageId' => $messageId]);

        $data = $this->send(new GetMessageRequest($ticketId, $messageId));

        return Message::fromArray($data['data'] ?? $data);
    }

    /**
     * Create a new message on a ticket.
     *
     * @param  array<string, mixed>  $payload  Required: from_contact (email/name), body, subject.
     *                                          Optional: type, to, cc, bcc, attachments (base64),
     *                                          inline_images, skip_notifications, send_mail_to_recipients
     * @throws \InvalidArgumentException
     * @throws HelpSpaceApiException
     */
    public function createMessage(int $ticketId, array $payload): Message
    {
        if (empty($payload['body'])) {
            throw new \InvalidArgumentException('Message body must not be empty.');
        }

        Log::info('HelpSpace: creating message', ['ticketId' => $ticketId]);

        $data = $this->send(new CreateMessageRequest($ticketId, $payload));

        return Message::fromArray($data['data'] ?? $data);
    }

    // -------------------------------------------------------------------------
    // Reports
    // -------------------------------------------------------------------------

    /**
     * Get the all-channels report for a date range.
     *
     * @param  string  $start  YYYY-MM-DD
     * @param  string  $end    YYYY-MM-DD
     * @throws \InvalidArgumentException
     * @throws HelpSpaceApiException
     */
    public function getChannelsReport(string $start, string $end): ChannelsReport
    {
        $this->validateDateRange($start, $end);

        Log::info('HelpSpace: channels report', ['start' => $start, 'end' => $end]);

        $data = $this->send(new ChannelsReportRequest($start, $end));

        return ChannelsReport::fromArray($data);
    }

    /**
     * Get the performance report for a date range.
     *
     * @param  string  $start  YYYY-MM-DD
     * @param  string  $end    YYYY-MM-DD
     * @throws \InvalidArgumentException
     * @throws HelpSpaceApiException
     */
    public function getPerformanceReport(string $start, string $end): PerformanceReport
    {
        $this->validateDateRange($start, $end);

        Log::info('HelpSpace: performance report', ['start' => $start, 'end' => $end]);

        $data = $this->send(new PerformanceReportRequest($start, $end));

        return PerformanceReport::fromArray($data);
    }

    // -------------------------------------------------------------------------
    // Webhooks
    // -------------------------------------------------------------------------

    /**
     * Retrieve the current webhook configuration.
     *
     * @throws HelpSpaceApiException
     */
    public function getWebhook(): WebhookConfig
    {
        Log::info('HelpSpace: getting webhook config');

        $data = $this->send(new GetWebhookRequest());

        return WebhookConfig::fromArray($data);
    }

    /**
     * Update the webhook configuration.
     *
     * @param  array<string, mixed>  $config  Keys: enabled (bool), url (string), secret (string),
     *                                         headers (array of {key, value}), trigger (object with
     *                                         ticket/customer/tag boolean maps)
     * @throws HelpSpaceApiException
     */
    public function updateWebhook(array $config): WebhookConfig
    {
        Log::info('HelpSpace: updating webhook config');

        $data = $this->send(new UpdateWebhookRequest($config));

        return WebhookConfig::fromArray($data);
    }

    /**
     * Retrieve recent webhook error logs.
     *
     * @return array<int, array<string, mixed>>
     * @throws HelpSpaceApiException
     */
    public function getWebhookLogs(): array
    {
        Log::info('HelpSpace: getting webhook logs');

        $data = $this->send(new GetWebhookLogsRequest());

        return $data['data'] ?? $data;
    }

    // -------------------------------------------------------------------------
    // Internal
    // -------------------------------------------------------------------------

    /**
     * Send a request, normalise errors into HelpSpaceApiException.
     *
     * @return array<string, mixed>
     * @throws HelpSpaceApiException
     */
    private function send(\Saloon\Http\Request $request): array
    {
        try {
            $response = $this->connector->send($request);
        } catch (RequestException $e) {
            Log::error('HelpSpace: API request failed', [
                'status' => $e->getResponse()->status(),
                'body'   => substr($e->getResponse()->body(), 0, 500),
            ]);

            throw new HelpSpaceApiException(
                "HelpSpace API returned HTTP {$e->getResponse()->status()}",
                $e->getResponse()->status(),
                $e
            );
        } catch (\Throwable $e) {
            Log::error('HelpSpace: unexpected error', ['message' => $e->getMessage()]);

            throw new HelpSpaceApiException("HelpSpace request failed: {$e->getMessage()}", 0, $e);
        }

        $data = $response->json();

        if (! is_array($data)) {
            throw new HelpSpaceApiException('HelpSpace API returned a non-JSON response.');
        }

        return $data;
    }

    private function validateDateRange(string $start, string $end): void
    {
        if (trim($start) === '' || trim($end) === '') {
            throw new \InvalidArgumentException('Report start and end dates must not be empty.');
        }
    }
}
