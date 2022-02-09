<?php declare(strict_types=1);

namespace ILIAS\Certificate\Event\Listener;

use ILIAS\Certificate\Event\CreateGUI;
use ILIAS\Certificate\Event\Path;
use ILIAS\Certificate\Event\TypeInFileName;
use ILIAS\Certificate\Event\VerificationType;
use ILIAS\Certificate\Event\PlaceholderClassByType;
use ILIAS\Certificate\Event\SupportsType;
use ILIAS\Certificate\Event\DicAwareness;
use ILIAS\Certificate\Event\PathEvent;
use ILIAS\Certificate\Event\GUIEvent;
use ILIAS\Certificate\Event\Event;

class Test implements CreateGUI, Path, TypeInFileName, VerificationType, PlaceholderClassByType
{
    use SupportsType;
    use DicAwareness;

    public function supportsType() : string
    {
        return 'tst';
    }

    public function typeInFileName(Event $event) : string
    {
        return 'test';
    }

    public function verificationType(Event $event) : string
    {
        return 'tstv';
    }

    public function placeholderClassByType(Event $event) : string
    {
        return \ilTestPlaceholderValues::class;
    }

    public function path(PathEvent $event) : string
    {
        return \ilCertificatePathConstants::TEST_PATH . $event->object->getId() . '/';
    }

    public function gui(GUIEvent $event) : \\ilCertificateGUI
    {
        $description = new \ilTestPlaceholderDescription();
        $values = new \ilTestPlaceholderValues();

        $formFactory = new \ilCertificateSettingsTestFormRepository(
            $event->object()->getId(),
            $event->certificatePath(),
            false,
            $event->object(),
            $this->dic->language(),
            $this->dic->ctrl(),
            $this->dic->access(),
            $this->dic->toolbar(),
            $description
        );

        $deleteAction = new \ilCertificateTestTemplateDeleteAction(
            $event->deleteAction(),
            new \ilCertificateObjectHelper()
        );

        $event = $event->withDeleteAction($deleteAction);

        return $event->gui($description, $values, $formFactory);
    }
}
