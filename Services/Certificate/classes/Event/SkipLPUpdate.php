<?php declare(strict_types=1);

namespace ILIAS\Certificate\Event;

interface SkipLPUpdate extends Listener
{
    public function skipLPUpdate(Event $event);
}
