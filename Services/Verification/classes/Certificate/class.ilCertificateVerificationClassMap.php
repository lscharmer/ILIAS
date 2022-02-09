<?php

use ILIAS\Certificate\Event\Dispatch;
use ILIAS\Certificate\Event\Event;
use ILIAS\Data\Result\Error;
use ILIAS\Data\Result;

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class ilCertificateVerificationClassMap
{
    /**
     * @throws ilException
     */
    public function getVerificationTypeByType(string $type) : string {

        $changeErrorMessage = static function () use ($type) : Error {
            return new Error(new ilException('The given type ' . $type . ' is not mapped as a verification type on the class map'));
        };

        return $this->get($type)->except($changeErrorMessage)->value();
    }

    private function typeExistsInMap(string $type) : bool
    {
        return $this->get($type)->isOk();
    }

    private function get(string $type) : Result
    {
        return (new Dispatch(require 'mymy.php'))->event(new Event($type, 'verificationType'));
    }
}
