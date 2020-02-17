<?php

namespace Krak\StructGen\Tests\Feature\CreateStructStatements;

use Krak\StructGen\CreateStructStatements;

final class FromValidatedArrayConstructorTest extends CreateStructStatementsTestCase
{
    /** @test */
    public function can_create_validated_array_constructor() {
        $this->assertStructStatements(<<<'CLASS'
<?php
class Acme {
    /** @var int */
    public $id;
    /** @var ?string */
    public $code;
    /** @var string[] */
    public $tags = [];
    /** @var bool */
    public $finished = false;
    /** @var Price */
    public $price;
    /** @var ?Price */
    public $salePrice;
    /** @var ?Price[] */
    public $allPrices = [];
}
CLASS
            ,<<<'STMT'
public static function fromValidatedArray(array $data) : self
{
    return new self($data['id'], $data['code'], array_key_exists('tags', $data) ? $data['tags'] : [], array_key_exists('finished', $data) ? $data['finished'] : false, Price::fromValidatedArray($data['price']), $data['salePrice'] === null ? null : Price::fromValidatedArray($data['salePrice']), array_key_exists('allPrices', $data) ? \array_map(function (?array $value) : ?Price {
        return $value === null ? null : Price::fromValidatedArray($value);
    }, $data['allPrices']) : []);
}
STMT
);
    }

    public function createStructStatements(): CreateStructStatements {
        return new CreateStructStatements\FromValidatedArrayConstructorCreateStructStatements();
    }
}
