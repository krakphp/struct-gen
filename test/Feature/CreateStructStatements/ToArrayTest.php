<?php

namespace Krak\StructGen\Tests\Feature\CreateStructStatements;

use Krak\StructGen\CreateStructStatements;

final class ToArrayTest extends CreateStructStatementsTestCase
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
    /** int|string */
    public $union;
}
CLASS
            ,<<<'STMT'
public function toArray() : array
{
    return ['id' => $this->id, 'code' => $this->code, 'tags' => $this->tags, 'prices' => \array_map(function (?Price $value) : ?array {
        return $value === null ? null : $value->toArray();
    }, $this->prices), 'finished' => $this->finished, 'totalPrice' => $this->totalPrice === null ? null : $this->totalPrice->toArray(), 'union' => $this->union];
}
STMT
);
    }

    public function createStructStatements(): CreateStructStatements {
        return new CreateStructStatements\ToArrayCreateStructStatements();
    }
}
