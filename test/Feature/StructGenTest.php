<?php

namespace Krak\StructGen\Tests\Feature;

use Krak\StructGen\GenerateStruct;
use Krak\StructGen\GenerateStructArgs;
use PhpParser\PrettyPrinter\Standard;
use function Krak\StructGen\{
    generateStructsExternallyForFiles,
    generateStructsForFiles,
    traversePhpFiles};
use PHPUnit\Framework\TestCase;

class StructGenTest extends TestCase
{
    const GENERATED_FILE_PATH = __DIR__ . '/Fixtures/generated-from-paths.php';

    public static function tearDownAfterClass(): void {
        if (file_exists(self::GENERATED_FILE_PATH)) {
            unlink(self::GENERATED_FILE_PATH);
        }
    }

    /**
     * @dataProvider provide_for_can_generate_struct_files_inline
     * @test
     */
    public function can_generate_struct_files_inline(string $testContent) {
        $genStruct = new GenerateStruct();
        [$test, $expected] = explode('-- EXPECTED --', $testContent);

        $result = $genStruct(GenerateStructArgs::inline($test));

        $this->assertEquals(
            trim($expected),
            trim($result->code())
        );
    }

    public function provide_for_can_generate_struct_files_inline() {
        yield from $this->listTestCaseFiles(__DIR__ . '/Fixtures/struct-inline-test-cases');
    }

    private function listTestCaseFiles(string $dir) {
        $files = new \DirectoryIterator($dir);
        foreach ($files as $file) {
            if ($file->isDot()) {
                continue;
            }

            yield $file->getFilename() => [file_get_contents($file->getPathname())];
        }
    }

    /**
     * @dataProvider provide_for_can_generate_struct_files_externally
     * @test
     */
    public function can_generate_struct_files_externally(string $testContent) {
        $genStruct = new GenerateStruct();
        [$test, $expected] = explode('-- EXPECTED --', $testContent);

        $result = $genStruct(GenerateStructArgs::external($test));

        $this->assertEquals(
            trim($expected),
            trim((new Standard())->prettyPrint($result->ast()))
        );
    }

    public function provide_for_can_generate_struct_files_externally() {
        yield from $this->listTestCaseFiles(__DIR__ . '/Fixtures/struct-external-test-cases');
    }

    /** @test */
    public function can_generate_structs_for_files() {
        $this->given_generated_from_paths_is_initialized_with('template');
        $expectedPath = __DIR__ . '/Fixtures/generated-from-paths-expected.php';
        generateStructsForFiles(traversePhpFiles([self::GENERATED_FILE_PATH]));
        $this->assertEquals(
            trim(file_get_contents($expectedPath)),
            trim(file_get_contents(self::GENERATED_FILE_PATH))
        );
    }

    /** @test */
    public function can_generate_structs_externally_for_files() {
        generateStructsExternallyForFiles(traversePhpFiles([
            __DIR__ . '/Fixtures/generate-struct-externally-for-files-test-cases/Acme.php',
            __DIR__ . '/Fixtures/generate-struct-externally-for-files-test-cases/GlobalClass.php',
            __DIR__ . '/Fixtures/generate-struct-externally-for-files-test-cases/Foo.php',
        ]), __DIR__ . '/Fixtures/generate-struct-externally-for-files-test-cases/.generated.php');

        $this->assertEquals(
            trim(file_get_contents(__DIR__ . '/Fixtures/generate-struct-externally-for-files-test-cases/.generated-expected.php')),
            trim(file_get_contents(__DIR__ . '/Fixtures/generate-struct-externally-for-files-test-cases/.generated.php'))
        );
    }

    /**
     * @depends can_generate_structs_externally_for_files
     * @test
     */
    public function generating_structs_inline_for_files_can_detect_changes() {
        // set of files is different, should result in changed output
        $res = generateStructsExternallyForFiles(traversePhpFiles([
            __DIR__ . '/Fixtures/generate-struct-externally-for-files-test-cases/Acme.php',
            __DIR__ . '/Fixtures/generate-struct-externally-for-files-test-cases/GlobalClass.php',
        ]), __DIR__ . '/Fixtures/generate-struct-externally-for-files-test-cases/.generated.php');
        $this->assertEquals(true, $res->hasChanges());
    }
    
    /**
     * @dataProvider provide_for_generating_structs_externally_for_files_can_detect_changes
     * @test
     */
    public function generating_structs_externally_for_files_can_detect_changes(string $type, bool $hasChanges) {
        $this->given_generated_from_paths_is_initialized_with($type);
        $res = generateStructsForFiles(traversePhpFiles([self::GENERATED_FILE_PATH]));
        $this->assertEquals($hasChanges, $res->hasChanges());
    }

    public function provide_for_generating_structs_externally_for_files_can_detect_changes() {
        yield 'has-changes' => ['template', true];
        yield 'no-changes' => ['expected', false];
    }

    /** can be template or expected */
    private function given_generated_from_paths_is_initialized_with(string $type = 'template') {
        $tplPath = __DIR__ . "/Fixtures/generated-from-paths-{$type}.php";
        copy($tplPath, self::GENERATED_FILE_PATH);
    }

    /** @test */
    public function files_without_any_matching_classes_are_left_untouched() {
        $genStruct = new GenerateStruct();
        $input = <<<'PHP'
<?php

interface Acme {}
class Foo {}
function bar() {

}
PHP;
        $this->assertEquals($input, $genStruct(GenerateStructArgs::inline($input))->code());
    }

    /** @test */
    public function external_struct_generation_does_not_support_multiple_namespaces() {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('External struct generation does not currently support multiple namespaces in one source.');

        (new GenerateStruct())(GenerateStructArgs::external(<<<'PHP'
<?php

namespace A {
    class Foo { use FooStruct; }
}
namespace B {}
PHP
        ));
    }
}
