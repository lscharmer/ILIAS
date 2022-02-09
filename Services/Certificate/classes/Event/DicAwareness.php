<?php declare(strict_types=1);

namespace ILIAS\Certificate\Event;

use ILIAS\DI\Container;

trait DicAwareness
{
    private Container $dic;

    public function __construct(Container $dic = null)
    {
        $this->dic = $dic;
        if (null === $this->dic) {
            global $DIC;
            $this->dic = $DIC;
        }
    }
}
