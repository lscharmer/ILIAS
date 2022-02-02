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

use PHPUnit\Framework\TestCase;

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class ilCertificateSettingsCourseFormRepositoryTest extends TestCase
{
    public function testSaveSettings() : void
    {
        $object = $this->getMockBuilder(ilObjCourse::class)
            ->disableOriginalConstructor()
            ->getMock();

        $object
            ->expects($this->atLeastOnce())
            ->method('getId')
            ->willReturn(100);

        $language = $this->getMockBuilder(ilLanguage::class)
            ->disableOriginalConstructor()
            ->getMock();

        $controller = $this->getMockBuilder(ilCtrl::class)
            ->disableOriginalConstructor()
            ->getMock();

        $access = $this->getMockBuilder(ilAccess::class)
            ->disableOriginalConstructor()
            ->getMock();

        $toolbar = $this->getMockBuilder(ilToolbarGUI::class)
            ->disableOriginalConstructor()
            ->getMock();

        $placeholderDescriptionObject = $this->getMockBuilder(ilCertificatePlaceholderDescription::class)
            ->disableOriginalConstructor()
            ->getMock();

        $leaningProgressObject = $this->getMockBuilder(ilObjectLP::class)
            ->disableOriginalConstructor()
            ->getMock();

        $settingsFormFactory = $this->getMockBuilder(ilCertificateSettingsFormRepository::class)
            ->disableOriginalConstructor()
            ->getMock();

        $trackingHelper = $this->getMockBuilder(ilCertificateObjUserTrackingHelper::class)
            ->disableOriginalConstructor()
            ->getMock();

        $objectHelper = $this->getMockBuilder(ilCertificateObjectHelper::class)
            ->disableOriginalConstructor()
            ->getMock();

        $lpHelper = $this->getMockBuilder(ilCertificateObjectLPHelper::class)
            ->disableOriginalConstructor()
            ->getMock();

        $lpMock = $this->getMockBuilder(ilObjectLP::class)
            ->disableOriginalConstructor()
            ->getMock();

        $lpMock->method('getCurrentMode')
            ->willReturn(100);

        $lpHelper->method('getInstance')->willReturn($lpMock);

        $tree = $this->getMockBuilder(ilTree::class)
            ->disableOriginalConstructor()
            ->getMock();

        $setting = $this->getMockBuilder(ilSetting::class)
            ->disableOriginalConstructor()
            ->getMock();

        $setting
            ->expects($this->atLeastOnce())
            ->method('set');

        $repository = new ilCertificateSettingsCourseFormRepository(
            $object,
            '/some/where',
            false,
            $language,
            $controller,
            $access,
            $toolbar,
            $placeholderDescriptionObject,
            $leaningProgressObject,
            $settingsFormFactory,
            $trackingHelper,
            $objectHelper,
            $lpHelper,
            $tree,
            $setting
        );

        $repository->save(array('subitems' => array(1, 2, 3)));
    }

    public function testFetchFormFieldData() : void
    {
        $object = $this->getMockBuilder(ilObjCourse::class)
            ->disableOriginalConstructor()
            ->getMock();

        $object
            ->expects($this->atLeastOnce())
            ->method('getId')
            ->willReturn(100);

        $language = $this->getMockBuilder(ilLanguage::class)
            ->disableOriginalConstructor()
            ->getMock();

        $controller = $this->getMockBuilder(ilCtrl::class)
            ->disableOriginalConstructor()
            ->getMock();

        $access = $this->getMockBuilder(ilAccess::class)
            ->disableOriginalConstructor()
            ->getMock();

        $toolbar = $this->getMockBuilder(ilToolbarGUI::class)
            ->disableOriginalConstructor()
            ->getMock();

        $placeholderDescriptionObject = $this->getMockBuilder(ilCertificatePlaceholderDescription::class)
            ->disableOriginalConstructor()
            ->getMock();

        $leaningProgressObject = $this->getMockBuilder(ilObjectLP::class)
            ->disableOriginalConstructor()
            ->getMock();

        $settingsFormFactory = $this->getMockBuilder(ilCertificateSettingsFormRepository::class)
            ->disableOriginalConstructor()
            ->getMock();

        $settingsFormFactory
            ->expects($this->atLeastOnce())
            ->method('fetchFormFieldData')
            ->willReturn(
                array(
                    'subitems' => array(),
                    'something_else' => 'something'
                )
            );

        $trackingHelper = $this->getMockBuilder(ilCertificateObjUserTrackingHelper::class)
            ->disableOriginalConstructor()
            ->getMock();

        $objectHelper = $this->getMockBuilder(ilCertificateObjectHelper::class)
            ->disableOriginalConstructor()
            ->getMock();

        $lpHelper = $this->getMockBuilder(ilCertificateObjectLPHelper::class)
            ->disableOriginalConstructor()
            ->getMock();

        $tree = $this->getMockBuilder(ilTree::class)
            ->disableOriginalConstructor()
            ->getMock();

        $setting = $this->getMockBuilder(ilSetting::class)
            ->disableOriginalConstructor()
            ->getMock();

        $setting
            ->expects($this->atLeastOnce())
            ->method('get')
            ->willReturn('[1, 2, 3]');

        $repository = new ilCertificateSettingsCourseFormRepository(
            $object,
            '/some/where',
            false,
            $language,
            $controller,
            $access,
            $toolbar,
            $placeholderDescriptionObject,
            $leaningProgressObject,
            $settingsFormFactory,
            $trackingHelper,
            $objectHelper,
            $lpHelper,
            $tree,
            $setting
        );

        $result = $repository->fetchFormFieldData('Some Content');

        $this->assertEquals(
            array(
                'subitems' => array(1, 2, 3),
                'something_else' => 'something'
            ),
            $result
        );
    }
}
