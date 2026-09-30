# Changelog

All notable changes to `graystackit/laravel-helpspace-api` will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

Audited the Tickets endpoints against HelpSpace's own `api-tickets` documentation page
(previously blocked by site-wide bot protection on `documentation.helpspace.com`; unblocked
by the user providing the page content directly).

### Fixed
- `listTickets()`'s created-date filter sent the query parameter as `created-between`; the
  real API parameter is `created-at-between`. The filter was silently ignored (unknown query
  params aren't rejected, just dropped), so date-range filtering by creation date never
  actually worked. `last-contact-between` was already correct.
- `Ticket::$channelEmail` and `Ticket::$contactEmail` read the `email` key off the `channel`/
  `from_contact` response objects, but HelpSpace's real response shape carries the address
  under `value` (confirmed via the API's own documented example response) — both properties
  were always `null` in production. `email` is kept as a fallback in case an older API
  version still returns it.

- `Message::$fromContactEmail` had the same bug as the Ticket fields above: read the `email`
  key off `from_contact`, but the real response carries the address under `value`. Always
  `null`/empty in production. `email` kept as a fallback.
- README's `createMessage()` attachment example used `file_name` as the attachment key;
  HelpSpace's documented attachment object uses `name` (matching the Create Ticket message
  attachment shape). Documentation-only fix — `CreateMessageRequest` always passed the raw
  payload through unvalidated, so this only affected copy-pasted example code, not the
  package's own behaviour.

### Verified correct — no change needed
- **Reports** (`/api/v1/reports/channels`, `/api/v1/reports/performance`): audited against
  the user-provided doc export. Endpoint paths, request body shape (`{start, end}`), and every
  response field mapping (`labels`/`opened`/`closed`/`metrics`/`channel_usage`/`tag_usage`/
  `top_customers`/`top_agents`) are already correct — including the fact that, unlike every
  other endpoint group, Reports responses are **not** wrapped in a `data` key, which the
  package already handles correctly. Resolves the "worth a look" note from the original
  code-only review (2026-09-28) about reports skipping the `data`-unwrap — that was correct
  behaviour, not a gap.
- **Webhook** (`GET/POST /api/v1/webhook`, `GET /api/v1/webhook/logs`): audited against the
  user-provided doc export. Endpoint paths, the `{"webhooks": {...}}` request envelope on
  update, the `{"data": {...}}` response envelope on get/update, and every `WebhookConfig`
  field all match the documented shape exactly. No changes needed.
- **Messages list/get query parameters and endpoint paths**: `additional-types[]`/`types[]`
  query keys and both endpoint paths already matched the doc exactly.

### Flagged — unverified, deliberately left unchanged pending confirmation
- **Ticket status values:** HelpSpace's own docs disagree with themselves. The Ticket
  Object/Create/Update field tables all list `open escaleted waiting closed spam` (no
  `unassigned`, spelled "escaleted"), but the filter query-parameter table lists
  `unassigned open escalated spam waiting closed` (correct spelling, includes `unassigned`).
  The package's `TicketStatus` enum matches the filter table's version. `Ticket::$status` is
  currently a plain `string` (the enum is unused/dead code), so there's no crash risk today —
  but confirm the real wire value on a live ticket before ever parsing `$status` into
  `TicketStatus`.
- **Message `subject` field may not exist at all.** HelpSpace's Create/Get Message doc
  examples (both complete, non-truncated) show no `subject` field anywhere in the message
  request or response schema — apparently only tickets carry one. But the package's earlier
  `[1.0.1]` release explicitly added subject-mapping based on a confirmed API observation at
  the time, and `createMessage()` still requires a non-empty `subject`. Left unchanged — this
  needs a live API call to resolve either way, since guessing wrong in either direction is a
  breaking change.

## [1.0.1] - 2026-06-19

### Fixed
- `Message` DTO now maps the `subject` field returned by the HelpSpace API; previously it was silently dropped from all `listMessages()` and `getMessage()` responses
- `createMessage()` now validates that `subject` is non-empty before sending the request, consistent with the API requirement and the existing `body` guard

## [1.0.0] - 2026-04-17

### Added
- Initial release
- `HelpSpaceClient` with 14 methods across Tickets, Messages, Reports, and Webhooks
- **Tickets:** `listTickets()`, `getTicket()`, `createTicket()`, `updateTicket()`, `deleteTicket()`
- **Messages:** `listMessages()`, `getMessage()`, `createMessage()`
- **Reports:** `getChannelsReport()`, `getPerformanceReport()`
- **Webhooks:** `getWebhook()`, `updateWebhook()`, `getWebhookLogs()`
- `Ticket` DTO with typed readonly properties and `fromArray()` / `toArray()`
- `Message` DTO with typed readonly properties and `fromArray()` / `toArray()`
- `ChannelsReport` DTO with daily counts, metrics, channels, tags, and top customers
- `PerformanceReport` DTO with daily counts, metrics, and top agents
- `WebhookConfig` DTO with enabled, url, secret, headers, trigger, and failedCount
- `TicketStatus` enum: `Unassigned`, `Open`, `Escalated`, `Spam`, `Waiting`, `Closed`
- `MessageType` enum: `External`, `Internal`, `Forward`, `Widget`, `Error`, `Bounce`, `Event`, `AiSummary`
- `HelpSpaceApiException` for all API and network errors
- `HelpSpaceConnector` with `Authorization: Bearer` and `Hs-Client-Id` headers
- Laravel service provider with auto-discovery and config validation
- Config file publishable via `vendor:publish --tag=helpspace-config`
- 41 tests (Feature + Unit) with Saloon `MockClient`
