<?php declare(strict_types=1);

namespace ILIAS\Certificate\Event\Listener;

use ILIAS\Certificate\Event\CreateGUI;
use ILIAS\Certificate\Event\Path;
use ILIAS\Certificate\Event\SkipLPUpdate;
use ILIAS\Certificate\Event\TypeInFileName;
use ILIAS\Certificate\Event\VerificationType;
use ILIAS\Certificate\Event\PlaceholderClassByType;
use ILIAS\Certificate\Event\SupportsType;
use ILIAS\Certificate\Event\DicAwareness;
use ILIAS\Certificate\Event\PathEvent;
use ILIAS\Certificate\Event\GUIEvent;
use ILIAS\Certificate\Event\Event;

class Course implements CreateGUI, Path, SkipLPUpdate, TypeInFileName, VerificationType, PlaceholderClassByType
{
    use SupportsType;
    use DicAwareness;

    public function supportsType() : string
    {
        return 'crs';
    }

    public function typeInFileName(Event $event) : string
    {
        return 'course';
    }

    public function verificationType(Event $event) : string
    {
        return 'crsv';
    }

    public function skipLPUpdate(Event $event) : bool
    {
        $this->dic->logger()->root()->info(
            'Skipping handling for course, because courses cannot be certificate trigger ' .
            '(with globally disabled learning progress) for other certificate enabled objects'
        );

        return true;
    }

    public function placeholderClassByType(Event $event) : string
    {
        return \ilCoursePlaceholderValues::class;
    }

    public function path(PathEvent $event) : string
    {
        return \ilCertificatePathConstants::COURSE_PATH . $event->object->getId() . '/';
    }

    public function gui(GUIEvent $event) : \ilCertificateGUI
    {
        $description = new \ilCoursePlaceholderDescription($event->object()->getid());
        $values = new \ilCoursePlaceholderValues();

        $formFactory = new \ilCertificateSettingsCourseFormRepository(
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
