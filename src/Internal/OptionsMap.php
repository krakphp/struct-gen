<?php

namespace Krak\StructGen\Internal;

final class OptionsMap
{
    private $options;

    private function __construct(array $options) {
        $this->options = $options;
    }

    public function isEmpty(): bool {
        return $this->options === [];
    }

    /**
     * @psalm-template T
     * @param string $optionName
     * @psalm-param callable(Option): T $fn
     * @psalm-return ?T
     */
    public function mapOption(string $optionName, callable $fn) {
        $opt = $this->options[$optionName] ?? null;
        return $opt ? $fn($opt) : null;
    }

    public function value(string $optionName): ?string {
        return $this->mapOption($optionName, function(Option $opt) {
            return $opt->value();
        });
    }

    public function json(string $optionName) {
        return $this->mapOption($optionName, function(Option $opt) {
            return $opt->valueAsJson();
        });
    }

    public function csv(string $optionName): ?array {
        return $this->mapOption($optionName, function(Option $opt) {
            return $opt->valueAsCSV();
        });
    }

    public static function empty(): self {
        return new self([]);
    }

    /**
     * Takes a set of options and returns a dictionary of option names to option
     * @param iterable<Option> $options
     * @return OptionsMap
     * @psalm-return array<string, Option>
     */
    public static function fromOptionsList(iterable $options): self {
        $indexed = [];
        foreach ($options as $opt) {
            $indexed[$opt->name()] = $opt;
        }
        return new self($indexed);
    }

    public static function merge(OptionsMap ...$maps): self {
        $joinedOptions = (function() use ($maps) {
            foreach ($maps as $map) {
                yield from $map->options;
            }
        })();
        return self::fromOptionsList($joinedOptions);
    }

    public static function fromDocBlock(string $docBlock, string $tagName = 'struct-gen'): self {
        return self::fromOptionsList(array_filter(array_map(function(string $line) use ($tagName) {
            $matches = [];
            if (!preg_match("/@{$tagName} ([a-zA-Z_\-0-9]+)( (.+))?$/", $line, $matches)) {
                return null;
            }

            $name = $matches[1];
            $value = trim($matches[2] ?? '');
            return new Option($name, $value ?: null);
        }, explode("\n", DocBlock::stripComments($docBlock)))));
    }

    public function toArray(): array {
        return $this->options;
    }
}
