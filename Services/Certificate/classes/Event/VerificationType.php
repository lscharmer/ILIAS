<?php declare(strict_types=1);

namespace ILIAS\Certificate\Event;

interface VerificationType extends Listener
{
    public function verificationType(Event $event) : string;
}
