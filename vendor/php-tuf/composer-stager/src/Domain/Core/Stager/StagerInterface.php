<?php declare(strict_types=1);

namespace PhpTuf\ComposerStager\Domain\Core\Stager;

use PhpTuf\ComposerStager\Domain\Service\ProcessOutputCallback\ProcessOutputCallbackInterface;
use PhpTuf\ComposerStager\Domain\Service\ProcessRunner\ProcessRunnerInterface;
use PhpTuf\ComposerStager\Domain\Value\Path\PathInterface;

/** Executes a Composer command in the staging directory. */
interface StagerInterface
{
    /**
     * @param array<string> $composerCommand
     *   The Composer command parts exactly as they would be typed in the terminal.
     *   There's no need to escape them in any way, only to separate them. Example:
     *   ```php
     *   $command = [
     *       // "composer" is implied.
     *       'require',
     *       'example/package:"^1 || ^2"',
     *       '--with-all-dependencies',
     *   ];
     *   ```
     * @param \PhpTuf\ComposerStager\Domain\Value\Path\PathInterface $activeDir
     *   The active directory.
     * @param \PhpTuf\ComposerStager\Domain\Value\Path\PathInterface $stagingDir
     *   The staging directory.
     * @param \PhpTuf\ComposerStager\Domain\Service\ProcessOutputCallback\ProcessOutputCallbackInterface|null $callback
     *   An optional PHP callback to run whenever there is process output.
     * @param int|null $timeout
     *   An optional process timeout (maximum runtime) in seconds. Set to null
     *   to disable.
     *
     * @throws \PhpTuf\ComposerStager\Domain\Exception\InvalidArgumentException
     *   If the given Composer command is invalid.
     * @throws \PhpTuf\ComposerStager\Domain\Exception\PreconditionException
     *   If the preconditions are unfulfilled.
     * @throws \PhpTuf\ComposerStager\Domain\Exception\RuntimeException
     *   If the operation fails.
     */
    public function stage(
        array $composerCommand,
        PathInterface $activeDir,
        PathInterface $stagingDir,
        ?ProcessOutputCallbackInterface $callback = null,
        ?int $timeout = ProcessRunnerInterface::DEFAULT_TIMEOUT
    ): void;
}
