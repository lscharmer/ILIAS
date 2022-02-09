<?php declare(strict_types=1);

namespace ILIAS\Certificate\Event;

use ILIAS\Data\Result;
use ILIAS\Data\Result\Ok;
use ILIAS\Data\Result\Error;

trait SupportsType
{
    abstract public function supportsType() : string;

    public function event(Event $event) : Result
    {
        if ($this->supportsType() === $event->type() && \method_exists($this, $event->name())) {
            $name = $event->name();
            return new Ok($this->$name($event));
        }

        return new Error('404 not found.');
    }
}
