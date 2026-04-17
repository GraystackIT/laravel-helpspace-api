<?php

declare(strict_types=1);

namespace GraystackIT\HelpSpace\Enums;

enum TicketStatus: string
{
    case Unassigned = 'unassigned';
    case Open       = 'open';
    case Escalated  = 'escalated';
    case Spam       = 'spam';
    case Waiting    = 'waiting';
    case Closed     = 'closed';
}
