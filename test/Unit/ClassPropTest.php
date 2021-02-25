<?php

namespace Krak\StructGen\Tests\Unit;

use Krak\StructGen\Internal\AtomicType;
use Krak\StructGen\Internal\Props\ClassProp;
use PhpParser\BuilderFactory;
use PHPUnit\Framework\TestCase;

final class ClassPropTest extends TestCase
{

    /** @test */
    public function converts_to_param() {
        $prop = new ClassProp('acme', AtomicType::asUnion('string'));

        $res = $prop->toParam(new BuilderFactory());

        $this->assertEquals(
            (new BuilderFactory())->param('acme')->setType('string'),
            $res
        );
    }

    /** @test */
    public function converts_to_param_with_default() {
        $prop = new ClassProp('acme', AtomicType::asUnion('string'), (new BuilderFactory())->val(null));

        $res = $prop->toParam(new BuilderFactory());

        $this->assertEquals(
            (new BuilderFactory())->param('acme')->setType('string')->setDefault((new BuilderFactory())->val(null)),
            $res
        );
    }

    /** @test */
    public function supports_php74_typed_properties() {
        $prop = ClassProp::fromProperty(
            (new BuilderFactory())
                ->property('acme')
                ->setType('string')
                ->getNode()
        );

        $this->assertEquals('string', $prop->type()->toString());
    }

    /** @test */
    public function supports_php74_typed_properties_with_doc_comments() {
        $prop = ClassProp::fromProperty(
            (new BuilderFactory())
                ->property('acme')
                ->setType('array')
                ->setDocComment('/** @var int[] */')
                ->getNode()
        );

        $this->assertEquals('int[]', $prop->type()->toString());
    }
}
