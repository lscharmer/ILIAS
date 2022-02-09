<?php declare(strict_types=1);

/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

use ILIAS\DI\Container;
use ILIAS\Certificate\Event\GUIEvent;
use ILIAS\Certificate\Event\Dispatch;
use ILIAS\Data\Result;
use ILIAS\Data\Result\Error;

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class ilCertificateGUIFactory
{
    private Container $dic;

    public function __construct(?Container $dic = null)
    {
        if (null === $dic) {
            global $DIC;
            $dic = $DIC;
        }
        $this->dic = $dic;
    }

    /**
     * @param ilObject $object
     * @return ilCertificateGUI
     * @throws ilException
     */
    public function create(ilObject $object) : ilCertificateGUI
    {
        global $DIC;

        $type = $object->getType();

        $logger = $DIC->logger()->cert();

        $templateRepository = new ilCertificateTemplateDatabaseRepository($this->dic->database(), $logger);
        $deleteAction = new ilCertificateTemplateDeleteAction($templateRepository);
        $pathFactory = new ilCertificatePathFactory();

        $certificatePath = $pathFactory->create($object);

        $event = new GUIEvent($type, $object, $certificatePath, $deleteAction);

        $result = $DIC->certificate()->event($event);
        $result = (new Dispatch(require 'mymy.php'))->event($event);

        return $result->then($this->isCertificateGUI())->value();
    }

    private function isCertificateGUI() : callable
    {
        return static function ($value) : Result {
            if ($value instanceof ilCertificateGUI) {
                return new Error(sprintf(
                    'Invalid return value given: expected %s, got %s.',
                    \ilCertificateGUI::class,
                    \get_class($value)
                ));
            }

            return $result;
        };
    }
}
