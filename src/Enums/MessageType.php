<?php

declare(strict_types=1);

namespace GraystackIT\HelpSpace\Enums;

enum MessageType: string
{
    case External  = 'external';
    case Internal  = 'internal';
    case Forward   = 'forward';
    case Widget    = 'widget';
    case Error     = 'error';
    case Bounce    = 'bounce';
    case Event     = 'event';
    case AiSummary = 'ai-summary';
}
