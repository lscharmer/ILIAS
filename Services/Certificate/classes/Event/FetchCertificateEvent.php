<?php declare(strict_types=1);

namespace ILIAS\Certificate\Event;

class FetchCertificateEvent extends Event
{
    private \ilCertificateFilename $generator;

    public function __construct(string $type, \ilCertificateFilename $generator)
    {
        parent::__construct($type, 'fetchCertificate');
        $this->generator = $generator;
    }

    public function generator() : \ilCertificateFilename
    {
        return $this->generator;
    }
}
