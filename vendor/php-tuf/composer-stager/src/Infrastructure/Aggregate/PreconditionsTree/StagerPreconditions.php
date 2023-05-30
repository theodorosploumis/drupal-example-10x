<?php declare(strict_types=1);

namespace PhpTuf\ComposerStager\Infrastructure\Aggregate\PreconditionsTree;

use PhpTuf\ComposerStager\Domain\Aggregate\PreconditionsTree\CommonPreconditionsInterface;
use PhpTuf\ComposerStager\Domain\Aggregate\PreconditionsTree\StagerPreconditionsInterface;
use PhpTuf\ComposerStager\Domain\Aggregate\PreconditionsTree\StagingDirIsReadyInterface;

final class StagerPreconditions extends AbstractPreconditionsTree implements StagerPreconditionsInterface
{
    public function __construct(
        CommonPreconditionsInterface $commonPreconditions,
        StagingDirIsReadyInterface $stagingDirIsReady
    ) {
        /** @var array<\PhpTuf\ComposerStager\Domain\Service\Precondition\PreconditionInterface> $children */
        $children = func_get_args();

        parent::__construct(...$children);
    }

    public function getName(): string
    {
        return 'Stager preconditions'; // @codeCoverageIgnore
    }

    public function getDescription(): string
    {
        return 'The preconditions for staging Composer commands.'; // @codeCoverageIgnore
    }

    protected function getFulfilledStatusMessage(): string
    {
        return 'The preconditions for staging Composer commands are fulfilled.'; // @codeCoverageIgnore
    }

    protected function getUnfulfilledStatusMessage(): string
    {
        return 'The preconditions for staging Composer commands are unfulfilled.'; // @codeCoverageIgnore
    }
}
