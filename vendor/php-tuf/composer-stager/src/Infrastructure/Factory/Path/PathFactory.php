<?php declare(strict_types=1);

namespace PhpTuf\ComposerStager\Infrastructure\Factory\Path;

use PhpTuf\ComposerStager\Domain\Value\Path\PathInterface;
use PhpTuf\ComposerStager\Infrastructure\Value\Path\UnixLikePath;
use PhpTuf\ComposerStager\Infrastructure\Value\Path\WindowsPath;

final class PathFactory implements PathFactoryInterface
{
    public static function create(string $path): PathInterface
    {
        if (DIRECTORY_SEPARATOR === '\\') {
            return new WindowsPath($path); // @codeCoverageIgnore
        }

        return new UnixLikePath($path);
    }
}
