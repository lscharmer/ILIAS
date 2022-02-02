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
class ilTestPlaceholderDescriptionTest extends TestCase
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
            ->willReturn("Something");

        $userDefinePlaceholderMock->method('getPlaceholderDescriptions')
            ->willReturn(array());

        $placeholderDescriptionObject = new ilTestPlaceholderDescription(null, $languageMock, $userDefinePlaceholderMock);

        $html = $placeholderDescriptionObject->createPlaceholderHtmlDescription($templateMock);

        $this->assertEquals('', $html);
    }

    public function testPlaceholderDescriptions() : void
    {
        $languageMock = $this->getMockBuilder(ilLanguage::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['txt', 'loadLanguageModule'])
            ->getMock();

        $languageMock->expects($this->exactly(25))
            ->method('txt')
            ->willReturn('Something translated');

        $userDefinePlaceholderMock = $this->getMockBuilder(ilUserDefinedFieldsPlaceholderDescription::class)
            ->disableOriginalConstructor()
            ->getMock();

        $userDefinePlaceholderMock->method('createPlaceholderHtmlDescription')
            ->willReturn("Something");

        $userDefinePlaceholderMock->method('getPlaceholderDescriptions')
            ->willReturn(array());

        $placeholderDescriptionObject = new ilTestPlaceholderDescription(null, $languageMock, $userDefinePlaceholderMock);

        $placeHolders = $placeholderDescriptionObject->getPlaceholderDescriptions();

        $this->assertEquals(
            array(
                'USER_LOGIN' => 'Something translated',
                'USER_FULLNAME' => 'Something translated',
                'USER_FIRSTNAME' => 'Something translated',
                'USER_LASTNAME' => 'Something translated',
                'USER_TITLE' => 'Something translated',
                'USER_SALUTATION' => 'Something translated',
                'USER_BIRTHDAY' => 'Something translated',
                'USER_INSTITUTION' => 'Something translated',
                'USER_DEPARTMENT' => 'Something translated',
                'USER_STREET' => 'Something translated',
                'USER_CITY' => 'Something translated',
                'USER_ZIPCODE' => 'Something translated',
                'USER_COUNTRY' => 'Something translated',
                'USER_MATRICULATION' => 'Something translated',
                'DATE' => 'Something translated',
                'DATETIME' => 'Something translated',
                'RESULT_PASSED' => 'Something translated',
                'RESULT_POINTS' => 'Something translated',
                'RESULT_PERCENT' => 'Something translated',
                'MAX_POINTS' => 'Something translated',
                'RESULT_MARK_SHORT' => 'Something translated',
                'RESULT_MARK_LONG' => 'Something translated',
                'TEST_TITLE' => 'Something translated',
                'DATE_COMPLETED' => 'Something translated',
                'DATETIME_COMPLETED' => 'Something translated'
            ),
            $placeHolders
        );
    }
}
