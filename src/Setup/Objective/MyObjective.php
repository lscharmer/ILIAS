<?php declare(strict_types=1);

namespace ILIAS\Setup\Objective;

use ILIAS\Setup\NoConfirmationException;
use ILIAS\Setup\Environment;
use ILIAS\Setup\Artifact\BuildArtifactObjective;
use ILIAS\Setup\Artifact\ArrayArtifact;
use ILIAS\Setup\Artifact;
use ILIAS\Setup\ImplementationOfInterfaceFinder;
use ILIAS\Certificate\Event\Listener;

class MyObjective extends BuildArtifactObjective
{
    public function getLabel() : string
    {
        return "Collect all types.";
    }

    public function getArtifactPath() : string
    {
        return 'mymy.php';
    }

    public function build() : Artifact
    {
        $finder = new ImplementationOfInterfaceFinder();
        $classes = \iterator_to_array($finder->getMatchingClassNames(Listener::class));

        return new ArrayArtifact($classes);
    }
}
