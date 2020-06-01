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

/**
 * @param \SplFileInfo[] $files
 * @param ?callable(string): string $generateStruct
 */
function generateStructsForFiles(iterable $files, LoggerInterface $logger = null, ?callable $generateStruct = null): void {
    $logger = $logger ?: new NullLogger();
    $generateStruct = $generateStruct ?: new GenerateStruct();
    foreach ($files as $file) {
        $logger->info('Generate Structs for File: ' . $file->getPathname());
        $original = file_get_contents($file->getPathname());
        $updated = $generateStruct($original);
        if ($original === $updated) {
            $logger->debug('No changes detected.');
        } else {
            $logger->info("New Struct Info: \n". $updated);
            file_put_contents($file->getPathname(), $updated);
        }
    }
}
