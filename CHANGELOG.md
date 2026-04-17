# Changelog

All notable changes to `graystackit/laravel-helpspace-api` will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

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
