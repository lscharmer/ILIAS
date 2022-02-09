<?php declare(strict_types=1);

namespace ILIAS\Certificate\Event;

use ILIAS\Data\Result;
use ILIAS\Data\Result\Error;

class Dispatch
{
    private $collection;

    public function __construct(array $collection)
    {
        $create = static function (string $class) : bool {
            return new $class();
        };

        $this->collection = \array_map($create, $collection);
    }

    public function event(Event $event) : Result
    {
        return $this->next($this->collection, $event);
    }

    private function next(array $next, Event $event) : Result
    {
        if (empty($next)) {
            return new Error(sprintf('The type "%s" is currently not defined for certificates', $event->type()));
        }

        return $next[0]->event($event)->except(function () use ($next, $event) : Result {
            return $this->next(\array_slice($next, 1), $arguments);
        });
    }
}
