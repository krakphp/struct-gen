<?php

namespace Core {
    trait AcmeStruct
    {
        public function __construct(string $id)
        {
            $this->id = $id;
        }
        public static function fromValidatedArray(array $data) : self
        {
            return new self($data['id']);
        }
        public function toArray() : array
        {
            return ['id' => $this->id];
        }
        public function id() : string
        {
            return $this->id;
        }
        public function withId(string $id) : self
        {
            $self = clone $this;
            $self->id = $id;
            return $self;
        }
    }
}
namespace {
    trait GlobalClassStruct
    {
        public function __construct(string $id)
        {
            $this->id = $id;
        }
        public static function fromValidatedArray(array $data) : self
        {
            return new self($data['id']);
        }
        public function toArray() : array
        {
            return ['id' => $this->id];
        }
        public function id() : string
        {
            return $this->id;
        }
        public function withId(string $id) : self
        {
            $self = clone $this;
            $self->id = $id;
            return $self;
        }
    }
}
namespace Foo {
    trait BarStruct
    {
        public function __construct(string $id)
        {
            $this->id = $id;
        }
        public static function fromValidatedArray(array $data) : self
        {
            return new self($data['id']);
        }
        public function toArray() : array
        {
            return ['id' => $this->id];
        }
        public function id() : string
        {
            return $this->id;
        }
        public function withId(string $id) : self
        {
            $self = clone $this;
            $self->id = $id;
            return $self;
        }
    }
}
