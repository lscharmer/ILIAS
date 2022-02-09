<?php declare(strict_types=1);

namespace ILIAS\Certificate\Event\Listener;

use ILIAS\Certificate\Event\CreateGUI;
use ILIAS\Certificate\Event\Path;
use ILIAS\Certificate\Event\VerificationType
use ILIAS\Certificate\Event\PlaceholderClassByType;
use ILIAS\Certificate\Event\SupportsType;
use ILIAS\Certificate\Event\DicAwareness;
use ILIAS\Certificate\Event\PathEvent;
use ILIAS\Certificate\Event\GUIEvent;
use ILIAS\Certificate\Event\Event;

class StudyProgramme implements CreateGUI, Path, VerificationType, PlaceholderClassByType
{
    use SupportsType;
    use DicAwareness;

    public function supportsType() : string
    {
        return 'prg';
    }

    public function placeholderClassByType(Event $event) : string
    {
        return \ilStudyProgrammePlaceholderValues::class;
    }

    public function path(PathEvent $event) : string
    {
        return \ilCertificatePathConstants::STUDY_PROGRAMME_PATH . $event->object->getId() . '/';
    }

    public function gui(GUIEvent $event) : \\ilCertificateGUI
    {
        $description = new \ilStudyProgrammePlaceholderDescription();
        $values = new \ilStudyProgrammePlaceholderValues();
        $formFactory = new \ilCertificateSettingsStudyProgrammeFormRepository(
            $event->object(),
            $event->certificatePath(),
            true,
            $this->dic->language(),
            $this->dic->ctrl(),
            $this->dic->access(),
            $this->dic->toolbar(),
            $description
        );

        return $event->gui($description, $values, $formFactory);
    }
}
