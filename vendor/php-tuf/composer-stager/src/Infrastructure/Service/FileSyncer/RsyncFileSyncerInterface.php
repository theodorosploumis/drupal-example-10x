<?php declare(strict_types=1);

namespace PhpTuf\ComposerStager\Infrastructure\Service\FileSyncer;

use PhpTuf\ComposerStager\Domain\Service\FileSyncer\FileSyncerInterface;

/**
 * Provides an rsync-based file syncer.
 *
 * This interface exists solely to facilitate autowiring dependencies through type hinting.
 *
 * @see https://symfony.com/doc/current/service_container/autowiring.html
 */
interface RsyncFileSyncerInterface extends FileSyncerInterface
{
}
