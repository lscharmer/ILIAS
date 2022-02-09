<?php declare(strict_types=1);

namespace ILIAS\Certificate\Event;

interface FetchCertificate extends Listener
{
    public function fetchCertificate(FetchCertificateEvent $event) : \ilCertificateFilename;
}
