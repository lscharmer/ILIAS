<?php declare(strict_types=1);

namespace ILIAS\Certificate\Event;

interface PlaceholderClassByType extends Listener
{
    public function placeholderClassByType(Event $event) : string;
}
