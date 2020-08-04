<?php

namespace Krak\StructGen\Internal;

final class Option
{
    private $name;
    private $value;

    public function __construct(string $name, ?string $value = null) {
        $this->name = $name;
        $this->value = $value;
    }

    public function name(): string {
        return $this->name;
    }

    public function value(): ?string {
        return $this->value;
    }

    /** Return the value decoded as json */
    public function valueAsJson() {
        return json_decode($this->value, true);
    }

    public function valueAsCSV(): array {
        return explode(',', $this->value);
    }
}
