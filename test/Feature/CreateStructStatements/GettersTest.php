<?php

namespace Krak\StructGen\Tests\Feature\CreateStructStatements;

use Krak\StructGen\CreateStructStatements;

final class GettersTest extends CreateStructStatementsTestCase
{
    /** @test */
    public function can_create_validated_array_constructor() {
        $this->assertStructStatements(<<<'CLASS'
<?php
class Acme {
    /** @var int */
    public $id;
    /** @var string */
    public $code;
    /** @var ?string[] */
    public $tags = [];
    /** @var ?Price[] */
    public $prices;
    /** @var bool */
    public $finished = false;
    /** @var ?Price */
    public $totalPrice;
}
CLASS
            ,<<<'STMT'
public function id() : int
{
    return $this->id;
}
public function code() : string
{
    return $this->code;
}
/** @return ?string[] */
public function tags() : array
{
    return $this->tags;
}
/** @return ?Price[] */
public function prices() : array
{
    return $this->prices;
}
public function finished() : bool
{
    return $this->finished;
}
public function totalPrice() : ?Price
{
    return $this->totalPrice;
}
STMT
);
    }

    public function createStructStatements(): CreateStructStatements {
        return new CreateStructStatements\GettersCreateStructStatements();
    }
}
