<?php declare(strict_types=1);
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

use ILIAS\Certificate\Event\Dispatch;
use ILIAS\Certificate\Event\FetchCertificateEvent;
use ILIAS\Data\Result\Ok;

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class ilCertificatePdfFileNameFactory
{
    private ilLanguage $lng;

    public function __construct(ilLanguage $lng)
    {
        $this->lng = $lng;
    }

    public function create(ilUserCertificatePresentation $presentation) : string
    {
        $objectType = $presentation->getObjType();
        $pdfFileGenerator = $this->fetchCertificateGenerator($objectType);

        return $pdfFileGenerator->createFileName($presentation);
    }

    private function fetchCertificateGenerator(string $objectType) : ilCertificateFilename
    {
        $generator = new ilCertificatePdfFilename($this->lng);
        $result = (new Dispatch(require 'mymy.php'))->event(new FetchCertificateEvent($objectType, $generator));
        $result = $result->except(static function () use ($generator) : Ok {
            return new Ok($generator);
        });

        return $result->value();
    }
}
