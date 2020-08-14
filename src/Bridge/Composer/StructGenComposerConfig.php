<?php

namespace Krak\StructGen\Bridge\Composer;

use Composer\Composer;

final class StructGenComposerConfig
{
    private $paths;
    private $generatedPath;
    private $basicTraverseFiles;

    public function __construct(array $paths, ?string $generatedPath, bool $basicTraverseFiles) {
        $this->paths = $paths;
        $this->generatedPath = $generatedPath;
        $this->basicTraverseFiles = $basicTraverseFiles;
    }

    /** create from potentially empty array from config and default the options accordingly */
    public static function fromConfigArray(array $config) {
        return new self(
            $config['paths'] ?? ['src'],
            $config['generated-path'] ?? null,
            $config['basic-traverse-files'] ?? false
        );
    }

    public static function fromComposer(Composer $composer) {
        return self::fromConfigArray($composer->getPackage()->getExtra()['struct-gen'] ?? []);
    }

    public function paths(): array {
        return $this->paths;
    }

    public function generatedPath(): ?string {
        return $this->generatedPath;
    }

    public function basicTraverseFiles(): bool {
        return $this->basicTraverseFiles;
    }
}
