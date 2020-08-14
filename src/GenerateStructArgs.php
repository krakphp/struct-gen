<?php

namespace Krak\StructGen;

final class GenerateStructArgs
{
    // Append the traits to source code inline with the classes
    const GENERATION_TYPE_INLINE = 'inline';
    // Return only the traits wrapped in appropriate namespace
    const GENERATION_TYPE_EXTERNAL = 'external';

    private $code;
    private $generationType;

    private function __construct(string $code, string $generationType) {
        $this->code = $code;
        $this->generationType = $generationType;
    }

    public static function inline(string $code) {
        return new self($code, self::GENERATION_TYPE_INLINE);
    }

    public static function external(string $code) {
        return new self($code, self::GENERATION_TYPE_EXTERNAL);
    }

    public function code(): string {
        return $this->code;
    }

    public function generationType(): string {
        return $this->generationType;
    }

    public function generateInline(): bool {
        return $this->generationType === self::GENERATION_TYPE_INLINE;
    }
}
