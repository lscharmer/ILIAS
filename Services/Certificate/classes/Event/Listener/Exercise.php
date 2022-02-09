<?php declare(strict_types=1);

namespace ILIAS\Certificate\Event\Listener;

use ILIAS\Certificate\Event\CreateGUI;
use ILIAS\Certificate\Event\Path;
use ILIAS\Certificate\Event\TypeInFileName;
use ILIAS\Certificate\Event\VerificationType;
use ILIAS\Certificate\Event\SupportsType;
use ILIAS\Certificate\Event\PlaceholderClassByType;
use ILIAS\Certificate\Event\DicAwareness;
use ILIAS\Certificate\Event\PathEvent;
use ILIAS\Certificate\Event\GUIEvent;
use ILIAS\Certificate\Event\Event;

class Exercise implements CreateGUI, Path, TypeInFileName, VerificationType, PlaceholderClassByType
{
    use SupportsType;
    use DicAwareness;

    public function supportsType() : string
    {
        return 'exc';
    }

    public function typeInFileName(Event $event) : string
    {
        return 'exc';
    }

    public function verificationType(Event $event) : string
    {
        return 'excv';
    }

    public function placeholderClassByType(Event $event) : string
    {
        return \ilExercisePlaceholderValues::class;
    }

    public function path(PathEvent $event) : string
    {
        return \ilCertificatePathConstants::EXERCISE_PATH . $event->object->getId() . '/';
    }

    public function gui(GUIEvent $event) : \ilCertificateGUI
    {
        $description = new \ilExercisePlaceholderDescription();
        $values = new \ilExercisePlaceholderValues();

        $formFactory = new \ilCertificateSettingsExerciseRepository(
            $event->object(),
            $event->certificatePath(),
            false,
            $this->dic->language(),
            $this->dic->ctrl(),
            $this->dic->access(),
            $this->dic->toolbar(),
            $description
        );

        return $event->gui($description, $values, $formFactory);
    }
}
