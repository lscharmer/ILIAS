<?php declare(strict_types=1);

namespace ILIAS\Certificate\Event;

interface CreateGUI extends Listener
{
    public function gui(GUIEvent $event) : \ilCertificateGUI;
}
