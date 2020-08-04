<?php

namespace Krak\StructGen\Internal;

final class DocBlock
{
    /**
     * Takes a Docblock and returns the stripped out comment piece of the doc comment
     * e.g.
     *     /** abc *\/
     *     would be come
     *     abc
     */
    public static function stripComments(string $docComment): string {
        return implode("\n", array_filter(array_map(function(string $line) {
            // strip leading '/** ' '* '
            $line = preg_replace('@^\s*(/\*\*|\*(?!/))\s?(.*)$@', '$2', $line);

            // check for trailing '*/'
            if (!preg_match('@\*/@', $line)) {
                return $line;
            }

            // strip trailing '*/' and trim any trailing space.
            return rtrim(substr($line, 0, -2));
        }, explode("\n", $docComment))));
    }
}
