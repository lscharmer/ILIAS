<?php declare(strict_types=1);

namespace ILIAS\Certificate\Event;

interface TypeInFileName extends Listener
{
    public function typeInFileName(Event $event) : string;
}
