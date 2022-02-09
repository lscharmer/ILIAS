<?php declare(strict_types=1);

namespace ILIAS\Certificate\Event;

interface Path extends Listener
{
    public function path(PathEvent $event) : string;
}
