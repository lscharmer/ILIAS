<?php declare(strict_types=1);

namespace ILIAS\Certificate\Event;

interface Listener
{
    public function event(Event $event);
}
