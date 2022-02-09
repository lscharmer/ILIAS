<?php declare(strict_types=1);

namespace ILIAS\Certificate\Event;

class PathEvent extends Event
{
    private \ilObject $object;

    public function __construct(string $type, \ilObject $object)
    {
        parent::__construct($type, 'path');
        $this->object = $object;
    }

    public function object() : \ilObject
    {
        return $this->object;
    }
}
