<?php declare(strict_types=1);

namespace PhpTuf\ComposerStager\Infrastructure\Value\Path;

use PhpTuf\ComposerStager\Domain\Value\Path\PathInterface;

abstract class AbstractPath implements PathInterface
{
    protected string $cwd;

    protected string $path;

    abstract protected function doResolve(string $basePath): string;

    /**
     * @param string $path
     *   The path string may be absolute or relative to the current working
     *   directory as returned by `getcwd()` at runtime, e.g., "/var/www/example"
     *   or "example". Nothing needs to actually exist at the path.
     */
    public function __construct(string $path)
    {
        $this->path = $path;

        // Especially since it accepts relative paths, an immutable path value
        // object should be immune to environmental details like the current
        // working directory. Cache the CWD at time of creation.
        $this->cwd = $this->getcwd();
    }

    public function resolve(): string
    {
        return $this->doResolve($this->cwd);
    }

    public function resolveRelativeTo(PathInterface $path): string
    {
        $basePath = $path->resolve();

        return $this->doResolve($basePath);
    }

    // Once support for Symfony 4 is dropped, some of this logic could possibly be
    // eliminated in favor of the new path manipulation utilities in Symfony 5.4:
    // https://symfony.com/doc/5.4/components/filesystem.html#path-manipulation-utilities
    protected function normalize(string $absolutePath, string $prefix = ''): string
    {
        // If the absolute path begins with a directory separator, append it to
        // the prefix, or it will be lost below when exploding the string. (A
        // trailing directory separator SHOULD BE lost.)
        if (strpos($absolutePath, DIRECTORY_SEPARATOR) === 0) {
            $prefix .= DIRECTORY_SEPARATOR;
        }

        // Strip the given prefix.
        $absolutePath = substr($absolutePath, strlen($prefix));

        // Normalize directory separators and explode around them.
        $absolutePath = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $absolutePath);
        $parts = explode(DIRECTORY_SEPARATOR, $absolutePath);

        $normalized = [];

        foreach ($parts as $part) {
            // A zero-length part comes from (meaningless) double slashes. Skip it.
            if ($part === '') {
                continue;
            }

            // A single dot has no effect. Skip it.
            if ($part === '.') {
                continue;
            }

            // Two dots goes "up" a directory. Pop one off the current normalized array.
            if ($part === '..') {
                array_pop($normalized);

                continue;
            }

            // Otherwise, add the part to the current normalized array.
            $normalized[] = $part;
        }

        // Replace directory separators.
        $normalized = implode(DIRECTORY_SEPARATOR, $normalized);

        // Replace the prefix and return.
        return $prefix . $normalized;
    }

    /**
     * In order to avoid class dependencies, PHP's internal getcwd() function is
     * called directly here.
     */
    private function getcwd(): string
    {
        // It is technically possible for getcwd() to fail and return false. (For
        // example, on some Unix variants, this check will fail if any one of the
        // parent directories does not have the readable or search mode set, even
        // if the current directory does.) But the likelihood is probably so slight
        // that it hardly seems worth cluttering up client code handling theoretical
        // IO exceptions. Cast the return value to a string for the purpose of
        // static analysis and move on.
        return (string) getcwd();
    }
}
