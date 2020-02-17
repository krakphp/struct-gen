<?php

namespace Foo\Bar;

final class AcmeStruct
{
    use AcmeStructStruct;
    /** @var int */
    private $id;
    /** @var string */
    private $title;
    /** @var string */
    private $slug;
}

final class BazStruct
{
    use BazStructStruct;

    /** @var int */
    private $id;
    /** @var string */
    private $title;
    /** @var string */
    private $slug;
}
