<?php

final class AcmeRequest
{
    use AcmeRequestStruct;

    /** @var int */
    private $id;
    /** @var string */
    private $title;
    /** @var string */
    private $slug;
    /** @var array */
    private $myArray;
    /** @var Prop[] */
    private $props;
    /** @var ?string */
    private $defaultString = null;
}
