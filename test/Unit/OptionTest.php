<?php

namespace Krak\StructGen\Tests\Unit;

use Krak\StructGen\Internal\Option;
use Krak\StructGen\Internal\OptionsMap;
use PHPUnit\Framework\TestCase;

class OptionTest extends TestCase
{
    /** @test */
    public function can_retrieve_value_as_json() {
        $option = new Option('generate', json_encode([1,2,3]));
        $this->assertEquals([1,2,3], $option->valueAsJson());
    }

    /** @test */
    public function can_retrieve_value_as_csv() {
        $option = new Option('generate', '1,2,3');
        $this->assertEquals([1,2,3], $option->valueAsCSV());
    }
    
    /** @test */
    public function can_index_a_collection_of_options() {
        $options = OptionsMap::fromOptionsList([
            new Option('a'),
            new Option('b'),
        ]);
        $this->assertEquals([
            'a' => new Option('a'),
            'b' => new Option('b')
        ], $options->toArray());
    }

    /** @test */
    public function can_merge_option_maps() {
        $options = OptionsMap::merge(
            OptionsMap::fromOptionsList([
                new Option('a', '1'),
                new Option('b', '1'),
            ]),
            OptionsMap::fromOptionsList([
                new Option('b', '2'),
                new Option('c', '2'),
            ])
        );
        $this->assertEquals([
            'a' => new Option('a', '1'),
            'b' => new Option('b', '2'),
            'c' => new Option('c', '2'),
        ], $options->toArray());
    }

    /** @test */
    public function can_create_from_doc_block() {
        $options = OptionsMap::fromDocBlock(<<<'BLOCK'
/** 
 * @struct-gen generate getters,withers,to-array
 * @struct-gen hide
 */
BLOCK
);
        $this->assertEquals([
            'generate' => new Option('generate', 'getters,withers,to-array'),
            'hide' => new Option('hide'),
        ], $options->toArray());
    }
}
