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
class ilCoursePlaceholderDescriptionTest extends TestCase
{
    public function testPlaceholderGetHtmlDescription() : void
    {
        $languageMock = $this->getMockBuilder(ilLanguage::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['txt', 'loadLanguageModule'])
            ->getMock();

        $templateMock = $this->getMockBuilder(ilTemplate::class)
            ->disableOriginalConstructor()
            ->getMock();

        $templateMock->method('get')
            ->willReturn('');

        $userDefinePlaceholderMock = $this->getMockBuilder(ilUserDefinedFieldsPlaceholderDescription::class)
            ->disableOriginalConstructor()
            ->getMock();

        $userDefinePlaceholderMock->method('createPlaceholderHtmlDescription')
            ->willReturn('');

        $userDefinePlaceholderMock->method('getPlaceholderDescriptions')
            ->willReturn([]);

        $customUserPlaceholderObject = $this->getMockBuilder(ilObjectCustomUserFieldsPlaceholderDescription::class)
                                            ->disableOriginalConstructor()
                                            ->getMock();

        $customUserPlaceholderObject->method("getPlaceholderDescriptions")
                                    ->willReturn(array(
                                        '+SOMETHING' => 'SOMEWHAT',
                                        '+SOMETHING_ELSE' => 'ANYTHING'
                                    ));

        $customUserPlaceholderObject->method('createPlaceholderHtmlDescription')
                                  ->willReturn('');

        $placeholderDescriptionObject = new ilCoursePlaceholderDescription(200, null, $languageMock, $userDefinePlaceholderMock, $customUserPlaceholderObject);

        $html = $placeholderDescriptionObject->createPlaceholderHtmlDescription($templateMock);

        $this->assertEquals('', $html);
    }

    public function testPlaceholderDescriptions() : void
    {
        $languageMock = $this->getMockBuilder(ilLanguage::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['txt'])
            ->getMock();

        $languageMock->expects($this->exactly(3))
                     ->method('txt')
                     ->willReturn('Something translated');

        $defaultPlaceholder = $this->getMockBuilder(ilDefaultPlaceholderDescription::class)
            ->disableOriginalConstructor()
            ->getMock();

        $defaultPlaceholder->method('getPlaceholderDescriptions')
            ->willReturn(
                array(
                    'SOMETHING' => 'SOMEWHAT',
                    'SOMETHING_ELSE' => 'ANYTHING'
                )
            );

        $customUserPlaceholderObject = $this->getMockBuilder(ilObjectCustomUserFieldsPlaceholderDescription::class)
            ->disableOriginalConstructor()
            ->getMock();

        $customUserPlaceholderObject->method('getPlaceholderDescriptions')
            ->willReturn(
                array(
                    '+SOMETHING' => 'SOMEWHAT',
                    '+SOMETHING_ELSE' => 'ANYTHING'
                )
            );

        $placeholderDescriptionObject = new ilCoursePlaceholderDescription(200, $defaultPlaceholder, $languageMock, null, $customUserPlaceholderObject);

        $placeHolders = $placeholderDescriptionObject->getPlaceholderDescriptions();

        $this->assertEquals(
            array(
                'COURSE_TITLE' => 'Something translated',
                'SOMETHING' => 'SOMEWHAT',
                'SOMETHING_ELSE' => 'ANYTHING',
                '+SOMETHING' => 'SOMEWHAT',
                '+SOMETHING_ELSE' => 'ANYTHING',
                'DATE_COMPLETED' => 'Something translated',
                'DATETIME_COMPLETED' => 'Something translated'
            ),
            $placeHolders
        );
    }
}
