<?php declare(strict_types=1);
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

use ILIAS\Certificate\Event\Dispatch;
use ILIAS\Certificate\Event\Event;
use ILIAS\Data\Result\Error;
use ILIAS\Data\Result;

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class ilCertificateTypeClassMap
{
    /**
     * @param string $type
     * @return string
     * @throws ilException
     */
    public function getPlaceHolderClassNameByType(string $type) : string
    {
        $changeErrorMessage = static function () use ($type) : Error {
            return new Error(new ilException('The given type ' . $type . 'is not mapped as a class on the class map');
        };

        return $this->get($type)->except($changeErrorMessage)->value();
    }

    public function typeExistsInMap(string $type) : bool
    {
        return $this->get($type)->isOk();
    }

    private function get(string $type) : Result
    {
        return (new Dispatch(require 'mymy.php'))->event(new Event($type, 'placeholderClassByType'));
    }
}
