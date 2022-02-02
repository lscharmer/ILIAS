<?php declare(strict_types=1);

/* Copyright (c) 1998-2021 ILIAS open source, Extended GPL, see docs/LICENSE */

use PHPUnit\Framework\TestSuite;

require_once __DIR__ . '/bootstrap.php';

/**
 * @author Alexander Killing <killing@leifos.de>
 */
class ilModulesExerciseSuite extends TestSuite
{
    public static function suite()
    {
        $suite = new ilModulesExerciseSuite();

        require_once("./Modules/Exercise/test/PeerReview/ExcPeerReviewTest.php");
        $suite->addTestSuite("ExcPeerReviewTest");

        require_once("./Modules/Exercise/test/Certificate/ilCertificateSettingsExerciseRepositoryTest.php");
        $suite->addTestSuite(ilCertificateSettingsExerciseRepositoryTest::class);

        require_once("./Modules/Exercise/test/Certificate/ilExercisePlaceholderDescriptionTest.php");
        $suite->addTestSuite(ilExercisePlaceholderDescriptionTest::class);

        require_once("./Modules/Exercise/test/Certificate/ilExercisePlaceHolderValuesTest.php");
        $suite->addTestSuite(ilExercisePlaceHolderValuesTest::class);

        return $suite;
    }
}
