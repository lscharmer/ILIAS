<?php declare(strict_types=1);

namespace ILIAS\Certificate\Event;

class GUIEvent extends Event
{
    private \ilObject $object;
    private string $certificatePath;
    private \ilCertificateTemplateDeleteAction $deleteAction;

    public function __construct(string $type, \ilObject $object, string $certificatePath, \ilCertificateTemplateDeleteAction $deleteAction)
    {
        parent::__construct($type, 'gui');
        $this->object = $object;
        $this->certificatePath = $certificatePath;
        $this->deleteAction = $deleteAction;
    }

    public function gui(
        \ilCertificatePlaceholderDescription $description,
        \ilCertificatePlaceholderValues $values,
        ?ilCertificateFormRepository $formFactory
    ) : \ilCertificateGUI
    {
        return new \ilCertificateGUI(
            $description,
            $values,
            $this->object->getId(),
            $this->certificatePath,
            $formFactory,
            $this->deleteAction
        );
    }

    public function object() : \ilObject
    {
        return $this->object;
    }

    public function certificatePath() : string
    {
        return $this->certificatePath;
    }

    public function deleteAction() : \ilCertificateTemplateDeleteAction
    {
        return $this->deleteAction;
    }

    public function withObject(\ilObject $object) : self
    {
        return new static($this->type, $object, $this->certificatePath, $this->deleteAction);
    }

    public function withCertificatePath(string $certificatePath) : self
    {
        return new static($this->type, $this->object, $certificatePath, $this->deleteAction);
    }

    public function withDeleteAction(\ilCertificateTemplateDeleteAction $deleteAction) : self
    {
        return new static($this->type, $this->object, $this->certificatePath, $deleteAction);
    }
}
