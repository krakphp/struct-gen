# Struct Generation

Define structs as simple php classes with typed property definitions. Include a StructTrait and let the library generate all the boiler plate for you for making immutable value objects that work great with IDEs and static analysis.

## Installation

Install with composer at `krak/struct-gen`

## Usage

Given some class definitions that use a special trait called `{className}Struct` (where {className} is the actual class name):

```php
<?php

namespace App\Catalog;

final class Product
{
    use ProductStruct;
    
    /** @var int */
    private $id;
    /** @var ?string */
    private $code;
    /** @var Category[] */
    private $categories;
}

final class Category
{
    use CategoryStruct;
    
    /** @var int */
    private $id;
    /** @var string */
    private $name;
}
```

Run `composer struct-gen:generate` and then those traits get filled with methods to allow an api like:

```php
<?php

$product = new App\Catalog\Product(1, null, []);
$product->id();
$product->code();
$product = $product->withCategories([
    App\Catalog\Category::fromValidatedArray(['id' => 1, 'name' => 'Nike'])
]);
$product->toArray();
// ['id' => 1, 'code' => null, [['id' => 1', 'name' => 'Nike']]]
```

Struct traits are generated that provide all of the boilerplate associated with making immutable value objects.  

```php
<?php

namespace App\Catalog;

trait ProductStruct
{
    /** @param Category[] $categories */
    public function __construct(int $id, ?string $code, array $categories)
    {
        $this->id = $id;
        $this->code = $code;
        $this->categories = $categories;
    }
    public static function fromValidatedArray(array $data) : self
    {
        return new self($data['id'], $data['code'], \array_map(function (array $value) : Category {
            return Category::fromValidatedArray($value);
        }, $data['categories']));
    }
    public function toArray() : array
    {
        return ['id' => $this->id, 'code' => $this->code, 'categories' => \array_map(function (Category $value) : array {
            return $value->toArray();
        }, $this->categories)];
    }
    public function id() : int
    {
        return $this->id;
    }
    public function code() : ?string
    {
        return $this->code;
    }
    /** @return Category[] */
    public function categories() : array
    {
        return $this->categories;
    }
    public function withId(int $id) : self
    {
        $self = clone $this;
        $self->id = $id;
        return $self;
    }
    public function withCode(?string $code) : self
    {
        $self = clone $this;
        $self->code = $code;
        return $self;
    }
    /** @param Category[] $categories */
    public function withCategories(array $categories) : self
    {
        $self = clone $this;
        $self->categories = $categories;
        return $self;
    }
}
trait CategoryStruct
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
```

### Configuring Paths

To configure what paths you want to search for generating the structs, you can easily just pass in paths to the `struct-gen:generate` command. By default, it looks into the `./src` folder.

You can also configure the paths to search inside of your composer.json so that you don't have to list the paths each time you run the command:

```json
{
  "extra": {
    "struct-gen": {
      "paths": ["src/*/DTO", "lib/DTO"]
    }
  }
}
```

### Generated File vs Inline Generation

By default, struct-gen will save the generated structs inline with php file of the original class. In this format, it's intended that the generated structs are committed into your repository.

However, you can optionally configure struct-gen to save all generated structs into a single file and automatically have composer register that file as a class map.

To do so, just update your composer json config like so:

```json
{
  "extra": {
    "struct-gen": {
      "generated-path": ".generated-structs.php" 
    }
  }
}
```

That file can be committed into your repo, or run on your CI pipeline to ensure the latest version of the structs are available.

### Generators

The structs are generated from a set of generators that implement the CreateStructStatements interface. Each generator is responsible for building up some part of the final struct.

#### Constructor Generation

Generates a constructor based off of all the properites in the class. If the system detects that a constructor already is defined, no constructor will be added to the struct trait.

#### From Validated Array Constructor Generation

Static constructor that takes an array that is assumed to be a valid array and converts into an object representation. This can be seen as the inverse operation of `toArray`. The term `validated` is meant to imply that this is unsafe to call with non-validated user data.

This constructor can handle nested structs and collections of structs. The library niavely assumes that any property whose type is some class must implement the `fromValidatedArray` function. So if your struct contains objects that don't have that function, you'll receive an error when calling `fromValidatedArray`.

#### Getter Generation

All getters are generated based off of the properties and don't use the `get` prefix. They are simply just the property names as function calls.

#### Wither Generation

All structs are immutable by default, so, if you want to change a value of a struct, you can use the `with{propName}` convention to set the value and return a new instance with the changed value.

#### To Array Generation

Converts the struct into an array representation. This works for nested structs and collections of structs as well.

### Generate Struct Options

Above the `use {className}Struct`, you can specify options to use for that certain class which can affect the generation process with the docblock tag `@struct-gen`

The format is `@struct-gen {option-name} ?{option-value}`. Where the value is optional and can be a simple string, comma separated list, or json string.

Here's an example:

```php
<?php

class Acme {
    /** @struct-gen generate getters,withers */
    use AcmeStruct;
    // ...
}
```

You can have multiple struct-gen tags with various different option names and values and those all get merged together.

#### generate <csv-of-generators>

The generate option allows you to control which generators get used for the specific struct. If you only want getters and withers, you can specify that accordingly.

The list of generator names you can use are: `constructor,from-validated-array,to-array,getters,withers`

## Why use static generation?

Most libraries for DTOs or structs are not IDE friendly and give access to helpful methods for a struct via runtime magic and reflection. Not only is there a slight performance hit with these methods, it's difficult to get typesafe while also working well with ides and static analysis tools.

## Development

Run composer:

```
composer install
```

Psalm:

```
./vendor/bin/psalm
```

PHPUnit:

```
./vendor/bin/phpunit
```

### Testing the Composer Plugin

The composer plugin is currently manually tested carefully with another local repo that requires the struct-gen package locally. I typically do some manual tests over all the features. 

## Roadmap

- Create from non-validated arrays
- Better psalm support for type definitions
- Plugin system
- Generate from Open Api 3
- Export to Open API 3
