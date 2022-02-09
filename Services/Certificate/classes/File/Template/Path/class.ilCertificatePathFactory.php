<?php declare(strict_types=1);
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

use ILIAS\Certificate\Event\Dispatch;
use ILIAS\Certificate\Event\PathEvent;

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class ilCertificatePathFactory
{
    public function create(ilObject $object) : string
    {
        $result = (new Dispatch(require 'mymy.php'))->event(new PathEvent($object->getType(), $object));

        return $result->value();
    }
}
