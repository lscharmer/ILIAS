<?php declare(strict_types=1);

/******************************************************************************
 *
 * This file is part of ILIAS, a powerful learning management system.
 *
 * ILIAS is licensed with the GPL-3.0, you should have received a copy
 * of said license along with the source code.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 *      https://www.ilias.de
 *      https://github.com/ILIAS-eLearning
 *
 *****************************************************************************/

use ILIAS\Filesystem\Exception\FileAlreadyExistsException;
use ILIAS\Filesystem\Exception\FileNotFoundException;
use ILIAS\Filesystem\Exception\IOException;

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class ilCertificateSettingsExerciseRepository implements ilCertificateFormRepository
{
    private ilLanguage $language;
    private ilCertificateSettingsFormRepository $settingsFromFactory;
    private ilObject $object;

    public function __construct(
        ilObject $object,
        string $certificatePath,
        bool $hasAdditionalElements,
        ilLanguage $language,
        ilCtrl $ctrl,
        ilAccessHandler $access,
        ilToolbarGUI $toolbar,
        ilCertificatePlaceholderDescription $placeholderDescriptionObject,
        ?ilCertificateSettingsFormRepository $settingsFormFactory = null
    ) {
        $this->object = $object;
        $this->language = $language;

        if (null === $settingsFormFactory) {
            $settingsFormFactory = new ilCertificateSettingsFormRepository(
                $object->getId(),
                $certificatePath,
                $hasAdditionalElements,
                $language,
                $ctrl,
                $access,
                $toolbar,
                $placeholderDescriptionObject
            );
        }

        $this->settingsFromFactory = $settingsFormFactory;
    }

    /**
     * @param ilCertificateGUI $certificateGUI
     * @return ilPropertyFormGUI
     * @throws FileAlreadyExistsException
     * @throws FileNotFoundException
     * @throws IOException
     * @throws ilDatabaseException
     * @throws ilException
     * @throws ilWACException
     */
    public function createForm(ilCertificateGUI $certificateGUI) : ilPropertyFormGUI
    {
        $form = $this->settingsFromFactory->createForm($certificateGUI);

        return $form;
    }

    public function save(array $formFields) : void
    {
    }

    public function fetchFormFieldData(string $content) : array
    {
        $formFields = $this->settingsFromFactory->fetchFormFieldData($content);

        return $formFields;
    }
}
