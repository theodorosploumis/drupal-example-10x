<?php declare(strict_types=1);

namespace PhpTuf\ComposerStager\Domain\Core\Cleaner;

use PhpTuf\ComposerStager\Domain\Aggregate\PreconditionsTree\CleanerPreconditionsInterface;
use PhpTuf\ComposerStager\Domain\Exception\IOException;
use PhpTuf\ComposerStager\Domain\Exception\RuntimeException;
use PhpTuf\ComposerStager\Domain\Service\Filesystem\FilesystemInterface;
use PhpTuf\ComposerStager\Domain\Service\Precondition\PreconditionInterface;
use PhpTuf\ComposerStager\Domain\Service\ProcessOutputCallback\ProcessOutputCallbackInterface;
use PhpTuf\ComposerStager\Domain\Service\ProcessRunner\ProcessRunnerInterface;
use PhpTuf\ComposerStager\Domain\Value\Path\PathInterface;

final class Cleaner implements CleanerInterface
{
    private FilesystemInterface $filesystem;

    private PreconditionInterface $preconditions;

    public function __construct(FilesystemInterface $filesystem, CleanerPreconditionsInterface $preconditions)
    {
        $this->filesystem = $filesystem;
        $this->preconditions = $preconditions;
    }

    public function clean(
        PathInterface $activeDir,
        PathInterface $stagingDir,
        ?ProcessOutputCallbackInterface $callback = null,
        ?int $timeout = ProcessRunnerInterface::DEFAULT_TIMEOUT
    ): void {
        $this->preconditions->assertIsFulfilled($activeDir, $stagingDir);

        try {
            $this->filesystem->remove($stagingDir, $callback, $timeout);
        } catch (IOException $e) {
            throw new RuntimeException($e->getMessage(), $e->getCode(), $e);
        }
    }
}
