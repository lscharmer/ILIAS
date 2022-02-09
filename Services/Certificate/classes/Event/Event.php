<?php declare(strict_types=1);

namespace ILIAS\Certificate\Event;

class Event
{
    private string $type;
    private string $name;

    public function __construct(string $type, string $name)
    {
        $this->type = $type;
        $this->name = $name;
    }

    public function type() : string
    {
        return $this->type;
    }

    public function name() : string
    {
        return $this->name;
    }
}
