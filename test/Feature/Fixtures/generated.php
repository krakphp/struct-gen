<?php

final class AcmeRequest
{
    use AcmeRequestStruct, OtherTrait;

    /** @var int */
    private $id;
    /** @var string */
    private $title;
}

trait OtherTrait {
    private $hi;
}

final class BazRequest
{
    use BazRequestStruct;

    /** @var int */
    private $id;
}

trait AcmeRequestStruct
{
    public function id() : int
    {
        return $this->id;
    }
}

trait BazRequestStruct
{
    public function id() : int
    {
        return $this->id;
    }
}
