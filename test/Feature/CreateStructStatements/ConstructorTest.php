<?php

namespace Krak\StructGen\Tests\Feature\CreateStructStatements;

use Krak\StructGen\CreateStructStatements;

final class ConstructorTest extends CreateStructStatementsTestCase
{
    /** @test */
    public function can_create_constructor_from_props() {
        $this->assertStructStatements(<<<'CLASS'
<?php
class Acme {
    public $id;
    /** @var string */
    public $code;
    /** @var string[] */
    public $tags;
    /** @var ?int */
    public $count;
    /** @var SplFileInfo */
    public $file;
    /** @var ArrayObject[] */
    public $arrays;
    /** @var ?Nested\Acme */
    public $nestedClass;
    /** @var int */
    public $default = 0;
}
CLASS
            ,<<<'STMT'
/**
 * @param string[] $tags
 * @param ArrayObject[] $arrays
 */
public function __construct($id, string $code, array $tags, ?int $count, SplFileInfo $file, array $arrays, ?Nested\Acme $nestedClass, int $default = 0)
{
    $this->id = $id;
    $this->code = $code;
    $this->tags = $tags;
    $this->count = $count;
    $this->file = $file;
    $this->arrays = $arrays;
    $this->nestedClass = $nestedClass;
    $this->default = $default;
}
STMT
);
    }

    /** @test */
    public function will_not_create_constructor_if_one_already_exists() {
        $this->assertStructStatements(<<<'CLASS'
<?php
class Acme {
    public $id;
    public function __construct(string $id) {}
}
CLASS
            ,<<<'STMT'
STMT
        );
    }

    public function createStructStatements(): CreateStructStatements {
        return new CreateStructStatements\ConstructorCreateStructStatements();
    }
}
