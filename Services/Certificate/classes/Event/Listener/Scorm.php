<?php declare(strict_types=1);

namespace ILIAS\Certificate\Event\Listener;

use ILIAS\Certificate\Event\CreateGUI;
use ILIAS\Certificate\Event\Path;
use ILIAS\Certificate\Event\TypeInFileName;
use ILIAS\Certificate\Event\VerificationType;
use ILIAS\Certificate\Event\PlaceholderClassByType;
use ILIAS\Certificate\Event\SupportsType;
use ILIAS\Certificate\Event\FetchCertificate;
use ILIAS\Certificate\Event\FetchCertificateEvent;
use ILIAS\Certificate\Event\DicAwareness;
use ILIAS\Certificate\Event\PathEvent;
use ILIAS\Certificate\Event\GUIEvent;
use ILIAS\Certificate\Event\Event;

class Scorm implements CreateGUI, Path, TypeInFileName, VerificationType, FetchCertificate, PlaceholderClassByType
{
    use SupportsType;
    use DicAwareness;

    public function supportsType() : string
    {
        return 'sahs';
    }

    public function typeInFileName(Event $event) : string
    {
        return 'scorm';
    }

    public function verificationType(Event $event) : string
    {
        return 'scov';
    }

    public function fetchCertificate(FetchCertificateEvent $event) : \ilCertificateFilename
    {
        return new \ilCertificateScormPdfFilename($event->generator(), $this->dic->language(), new \ilSetting('scorm'))
    }

    public function placeholderClassByType(Event $event) : string
    {
        return \ilScormPlaceholderValues::class;
    }

    public function path(PathEvent $event) : string
    {
        return \ilCertificatePathConstants::SCORM_PATH . $event->object->getId() . '/';
    }

    public function gui(GUIEvent $event) : \ilCertificateGUI
    {
        $description = new \ilScormPlaceholderDescription($object);
        $values = new \ilScormPlaceholderValues();

        $formFactory = new \ilCertificateSettingsScormFormRepository(
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
