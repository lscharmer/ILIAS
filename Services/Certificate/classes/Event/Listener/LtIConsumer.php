<?php declare(strict_types=1);

namespace ILIAS\Certificate\Event\Listener;

use ILIAS\Certificate\Event\CreateGUI;
use ILIAS\Certificate\Event\Path;
use ILIAS\Certificate\Event\VerificationType;
use ILIAS\Certificate\Event\PlaceholderClassByType;
use ILIAS\Certificate\Event\SupportsType;
use ILIAS\Certificate\Event\DicAwareness;
use ILIAS\Certificate\Event\PathEvent;
use ILIAS\Certificate\Event\GUIEvent;
use ILIAS\Certificate\Event\Event;

class LtIConsumer implements CreateGUI, Path, VerificationType, PlaceholderClassByType
{
    use SupportsType;
    use DicAwareness;

    public function supportsType() : string
    {
        return 'lti';
    }

    public function verificationType(Event $event) : string
    {
        return 'ltiv';
    }

    public function placeholderClassByType(Event $event) : string
    {
        return \ilLTIConsumerPlaceholderValues::class;
    }

    public function path(PathEvent $event) : string
    {
        return \ilCertificatePathConstants::LTICON_PATH . $event->object->getId() . '/';
    }

    public function gui(GUIEvent $event) : \ilCertificateGUI
    {
        $description = new \ilLTIConsumerPlaceholderDescription();
        $values = new \ilLTIConsumerPlaceholderValues();

        $formFactory = new \ilCertificateSettingsLTIConsumerFormRepository(
            $object,
            $certificatePath,
            true,
            $DIC->language(),
            $DIC->ctrl(),
            $DIC->access(),
            $DIC->toolbar(),
            $description
        );

        return $event->gui($description, $values, $formFactory);
    }
}
