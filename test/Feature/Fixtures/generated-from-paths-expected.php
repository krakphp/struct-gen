<?php

final class AcmeRequest
{
    use AcmeRequestStruct;

    /** @var int */
    private $id;
    /** @var string */
    private $name;
}

trait AcmeRequestStruct
{
    public function __construct(int $id, string $name)
    {
        $this->id = $id;
        $this->name = $name;
    }
    public static function fromValidatedArray(array $data) : self
    {
        return new self($data['id'], $data['name']);
    }
    public function toArray() : array
    {
        return ['id' => $this->id, 'name' => $this->name];
    }
    public function id() : int
    {
        return $this->id;
    }
    public function name() : string
    {
        return $this->name;
    }
    public function withId(int $id) : self
    {
        $self = clone $this;
        $self->id = $id;
        return $self;
    }
    public function withName(string $name) : self
    {
        $self = clone $this;
        $self->name = $name;
        return $self;
    }
}
