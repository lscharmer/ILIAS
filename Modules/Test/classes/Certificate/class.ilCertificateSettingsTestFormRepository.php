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
use ILIAS\Filesystem\Exception\IOException;
use ILIAS\Filesystem\Exception\FileNotFoundException;

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class ilCertificateSettingsTestFormRepository implements ilCertificateFormRepository
{
    private ilCertificateSettingsFormRepository $settingsFromFactory;
    private ilLanguage $language;
    private ilObjTest $testObject;

    public function __construct(
        int $objectId,
        string $certificatePath,
        bool $hasAdditionalElements,
        ilObjTest $testObject,
        ilLanguage $language,
        ilCtrl $ctrl,
        ilAccess $access,
        ilToolbarGUI $toolbar,
        ilCertificatePlaceholderDescription $placeholderDescriptionObject,
        ?ilCertificateSettingsFormRepository $settingsFormRepository = null
    ) {
        $this->testObject = $testObject;
        $this->language = $language;

        if (null === $settingsFormRepository) {
            $settingsFormRepository = new ilCertificateSettingsFormRepository(
                $objectId,
                $certificatePath,
                $hasAdditionalElements,
                $language,
                $ctrl,
                $access,
                $toolbar,
                $placeholderDescriptionObject
            );
        }
        $this->settingsFromFactory = $settingsFormRepository;
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
