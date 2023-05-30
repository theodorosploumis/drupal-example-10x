<?php declare(strict_types=1);

namespace PhpTuf\ComposerStager\Domain\Aggregate\PreconditionsTree;

/**
 * Asserts that the staging directory is ready for use.
 *
 * This interface exists solely to facilitate autowiring dependencies through type hinting.
 *
 * @see https://symfony.com/doc/current/service_container/autowiring.html
 */
interface StagingDirIsReadyInterface extends PreconditionsTreeInterface
{
}
