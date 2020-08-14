<?php

namespace Krak\StructGen;

use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/** @return iterable<\SplFileInfo> */
function traversePhpFiles(iterable $paths): iterable {
    foreach ($paths as $path) {
        if (is_file($path)) {
            yield new \SplFileInfo($path);
        } else {
            $files = new \RecursiveDirectoryIterator($path);
            $files = new \RecursiveIteratorIterator($files);
            /** @var \SplFileInfo $file */
            foreach ($files as $file) {
                if ($file->isFile() && $file->getExtension() === 'php') {
                    yield $file;
                }
            }
        }
    }
}

final class GeneratedStructsForFilesResult {
    private $hasChanges;

    public function __construct(bool $hasChanges) {
        $this->hasChanges = $hasChanges;
    }

    public function hasChanges(): bool {
        return $this->hasChanges;
    }
}

/**
 * @param \SplFileInfo[] $files
 * @param ?callable(string): string $generateStruct
 */
function generateStructsForFiles(iterable $files, LoggerInterface $logger = null, ?callable $generateStruct = null): GeneratedStructsForFilesResult {
    $logger = $logger ?: new NullLogger();
    $generateStruct = $generateStruct ?: new GenerateStruct();
    $hasChanges = false;
    foreach ($files as $file) {
        $logger->info('Generate Structs for File: ' . $file->getPathname());
        $original = file_get_contents($file->getPathname());
        $updated = $generateStruct(GenerateStructArgs::inline($original))->code();
        if (trim($original) === trim($updated)) {
            $logger->debug('No changes detected.');
        } else {
            $logger->info("New Struct Info: \n". $updated);
            file_put_contents($file->getPathname(), $updated);
            $hasChanges = true;
        }
    }

    return new GeneratedStructsForFilesResult($hasChanges);
}

function generateStructsExternallyForFiles(iterable $files, string $generatedFilePath, LoggerInterface $logger = null, ?callable $generateStruct = null): GeneratedStructsForFilesResult {
    $logger = $logger ?: new NullLogger();
    $generateStruct = $generateStruct ?: new GenerateStruct();
    $ast = [];
    foreach ($files as $file) {
        $logger->info('Generate Structs for File: ' . $file->getPathname());
        $original = file_get_contents($file->getPathname());
        $ast = array_merge($ast, $generateStruct(GenerateStructArgs::external($original))->ast());
    }

    $newContents = (new \PhpParser\PrettyPrinter\Standard())->prettyPrintFile($ast);

    // perform md5 hash to try and keep memory down so we don't need to load both strings in memory
    $currentHash = file_exists($generatedFilePath) ? md5_file($generatedFilePath) : '';
    $newHash = md5($newContents);

    if ($currentHash !== $newHash) {
        $logger->debug("Changes detected: old hash = $currentHash, new hash = $newHash");
    } else {
        $logger->debug("No changes detected: hash $currentHash");
    }

    $logger->info('Writing generated structs into: ' . $generatedFilePath);
    file_put_contents($generatedFilePath, $newContents);

    return new GeneratedStructsForFilesResult($currentHash !== $newHash);
}
