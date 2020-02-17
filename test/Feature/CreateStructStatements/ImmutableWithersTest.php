<?php

namespace Krak\StructGen\Tests\Feature\CreateStructStatements;

use Krak\StructGen\CreateStructStatements;

final class ImmutableWithersTest extends CreateStructStatementsTestCase
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
public function withId(int $id) : self
{
    $self = clone $this;
    $self->id = $id;
    return $self;
}
public function withCode(string $code) : self
{
    $self = clone $this;
    $self->code = $code;
    return $self;
}
/** @param ?string[] $tags */
public function withTags(array $tags = []) : self
{
    $self = clone $this;
    $self->tags = $tags;
    return $self;
}
/** @param ?Price[] $prices */
public function withPrices(array $prices) : self
{
    $self = clone $this;
    $self->prices = $prices;
    return $self;
}
public function withFinished(bool $finished = false) : self
{
    $self = clone $this;
    $self->finished = $finished;
    return $self;
}
public function withTotalPrice(?Price $totalPrice) : self
{
    $self = clone $this;
    $self->totalPrice = $totalPrice;
    return $self;
}
STMT
);
    }

    public function createStructStatements(): CreateStructStatements {
        return new CreateStructStatements\ImmutableWithersCreateStructStatements();
    }
}
