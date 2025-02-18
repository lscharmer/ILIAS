# Refinery

The `Refinery` library is used to unify the way input is
processed by the ILIAS project.

The keywords “MUST”, “MUST NOT”, “REQUIRED”, “SHALL”, “SHALL NOT”, “SHOULD”,
“SHOULD NOT”, “RECOMMENDED”, “MAY”, and “OPTIONAL” in this document are to be
interpreted as described in [RFC 2119](https://www.ietf.org/rfc/rfc2119.txt).

**Table of Contents**
- [General](#general)
- [Quickstart example](#quickstart-example)
- [Usage](#usage)
  * [Factory](#factory)
    + [Groups](#groups)
      - [to](#to)
        * [Natives](#natives)
        * [Structures](#structures)
      - [in](#in)
        * [series](#series)
        * [parallel](#parallel)
      - [encode](#encode)
        * [htmlSpecialCharsAsEntities](#htmlSpecialCharsAsEntities)
        * [htmlAttributeValue](#htmlAttributeValue)
        * [json](#json)
        * [url](#url)
  * [Custom Transformation](#custom-transformation)
    + [DeriveApplyToFromTransform](#deriveapplytofromtransform)
      - [Error Handling](#error-handling)
    + [DeriveTransformFromApplyTo](#derivetransformfromapplyto)
      - [Error Handling](#error-handling-1)
- [Libraries](#libraries)
  * [Transformation](#transformation)
  * [Validation](#validation)
- [Data](#data)
  * [Result](#result)
  * [Color](#color)
  * [URI](#uri)
  * [DataSize](#datasize)
  * [Password](#password)
  * [ClientId](#clientid)
  * [ReferenceId](#referenceid)
  * [ObjectId](#objectid)
  * [Alphanumeric](#alphanumeric)
  * [PositiveInteger](#positiveinteger)
  * [DateFormat](#dateformat)
  * [Range](#Range)
  * [Order](#order)
  * [Clock](#clock)
  * [Dimension](#dimension)
  * [Dataset](#dataset)
  * [HTML Metadata](#htmlmetadata)
  * [OpenGraph Metadata](#opengraphmetadata)
  * [LanguageTag](#languagetag)

## General

This library contains various implementations and
interfaces to establish a way to secure input
and transform values in a secure way.

The initial concept for this library can be found
[here](/docs/documentation/input-processing.md).

These library also consists of sub-libraries,
that can be used for transformations and
validation.
Checkout the [chapter](#libraries) about these
additional libraries.

## Quickstart example

```php
global $DIC;

$refinery = $DIC->refinery();

$transformation = $refinery->in()->series(
    array(
        new ILIAS\Refinery\To\Transformation\IntegerTransformation(),
        new ILIAS\Refinery\To\Transformation\IntegerTransformation()
    )
);

$result = $transformation->transform(5);

$data = $refinery->data('alphanumeric')->transform(array($result));

echo $data->getData();
```

The output will be a `integer` value: `5` 

## Usage

### Factory

The factory of the refinery interface can create
an implementations of very different [groups](#groups).
These groups can be used for several validations and
transformations.

A concrete implementation of the `Refinery\Factory`
interface is `Refinery\Factory\BasicFactory`.
This implementation will create new instances of the
different [groups](#groups).
The `Refinery\Factory\BasicFactory` can also be accessed
via the `ILIAS Dependency Injection Container(DIC)`.

```php
global $DIC;

$refinery = $DIC->refinery();
$transformation = $refinery->to()->string();
// ...
```

Checkout the [examples](/src/Refinery/examples) to
see how these library can be used.

_Info: These examples are just for a show case.
These examples are non-operable from the console,
because of the missing initialization of ILIAS_

#### Groups

The different groups are used to validate and/or transform
the input given to the certain transformation.

Because of the usage of the `Transformation` interface
these groups can interact with each other and
with other implementation interfaces.
E.g. transformation from the `to` group can be used in
the `in` group and vice versa.

##### to

The `to` group consists of combined validations and transformations
for native data types that establish a baseline for further constraints
and more complex transformations.

A concrete implementation for the `Refinery\To\Group` interface
is the `Refinery\To\BasicGroup`.

To learn more about transformations checkout the
[README about Transformations](/src/Refinery/Transformation/README.md).

The transformations of this group are very strict, which means
that there are several type checks before the transformation is
executed.

```php
global $DIC;

$refinery = $DIC->refinery();

$transformation = $refinery->to()->int();

$result = $transformation->transform(3.5); // Will throw exception because, values is not an integer value
$result = $transformation->transform('hello'); // Will throw exception because, values is not an integer value
$result = $transformation->transform(3); // $result = 3
```

In this example the `ILIAS\Refinery\To\IntegerTransformation` of the `to` group is
used.
The `ILIAS\Refinery\To\IntegerTransformation` of this group is very strict,
so only elements of the `integer` type are allowed.
Every non-matching value will throw an exception.

To avoid exception handling the `applyTo` method can be used instead.
Find out more about the `applyTo` method of instances of the `Transformation`
interface in the
[README about Transformations](/src/Refinery/Transformation/README.md).

###### Natives

As seen in the example of the [previous chapter](#to)
there are transformations which cover the native data
types of PHP (`int`, `string`, `float` and `boolean`).

* `string()`   - Returns an object that allows to transform a value to a string value.
* `int()`      - Returns an object that allows to transform a value to a integer value.
* `float()`    - Returns an object that allows to transform a value to a float value.
* `bool()`     - Returns an object that allows to transform a value to a boolean value.

###### Structures

Beside the [native transformations](#natives) there also
transformation to create structures like `list`, `dictonary`,
`record` and `tuple`.

* `listOf()`   - Returns an object that allows to transform an value in a given array
                 with the given transformation object.
                 The transformation will be executed on every element of the array.
* `dictOf()`   - Returns an object that allows to transform an value in a given array
                 with the given transformation object.
                 The transformation will be executed on every element of the array.
* `tupleOf()`  - Returns an object that allows to transform the values of an array
                 with the given array of transformations objects.
                 The length of the array of transformations MUST be identical to the
                 array of values to transform.
                 The keys of the transformation array will be the same as the key
                 from the value array e.g. Transformation on position 2 will transform
                 value on position 2 of the value array.
* `recordOf()` - Returns an object that allows to transform the values of an
                 associative array with the given associative array of
                 transformations objects.
                 The length of the array of transformations MUST be identical to the
                 array of values to transform.
                 The keys of the transformation array will be the same as the key
                 from the value array e.g. Transformation with the key "hello" will transform
                 value with the key "hello" of the value array.
* `toNew()`    - Returns either an transformation object to create objects of an
                 existing class, with variations of constructor parameters OR returns
                 an transformation object to execute a certain method with variation of
                 parameters on the objects.
* `data()`     - Returns a data factory to create a certain data type

###### Other

* `inArray()` - Returns an object that validates that a value is a member of the given array.
                 E.g.: `$t = $refinery->to()->memberOf(['red', 'green', 'blue']); $t->transform('blue'); /* => 'blue' */ $t->transform('yellow'); /* => Exception */`

##### in

The `in` group is a group with a dict of `Transformations`
as parameters that define the content at the indices.

A concrete implementation for the `Refinery\In\Group` interface
is the `Refinery\In\BasicGroup`.

There are currently two different strategies supported by this group,
that are accessible by the methods:

* [series](#series)
* [parallel](#parallel)

###### series

The transformation `series` takes an array of transformations and
performs them one after another on the result of the previous transformation.

```php
global $DIC;

$refinery = $DIC->refinery();

$transformation = $refinery->in()->series(
    array(
        new ILIAS\Refinery\To\Transformation\IntegerTransformation(),
        new ILIAS\Refinery\To\Transformation\StringTransformation()
    )
);

$result = $transformation->transform(5.5);
// $result => '5'
```

The result will be the end result of the transformations that were executed
in the strict order added in the `series` method.

In this case it is a `string` with the value '5'.

###### parallel

The transformation `parallel` takes an array of transformations and
performs each on the input value to form a tuple of the results.

```php
global $DIC;

$refinery = $DIC->refinery();

$transformation = $refinery->in()->parallel(
    array(
        new ILIAS\Refinery\To\Transformation\IntegerTransformation(),
        new ILIAS\Refinery\To\Transformation\IntegerTransformation()
    )
);

$result = $transformation->transform(5);
// $result => array(5, 5)
```

The result will be an array of results of each transformation.

In this case this is an array with an `integer` and a `string`
value.

##### encode

The `encode` group is a group which encodes a given UTF-8 string to be used in different context while retaining it's meaning.
These transformations can be used to prevent Cross-Site-Scripting.

###### htmlSpecialCharsAsEntities

This transformation ensures that the given string can be used within HTML content without injecting HTML tags.
This can be used when using templates where the content of the variable is not safe:

```php
$template = new ilTemplate('tpl.dummy.html', true, true);
$template->setVariable('TITLE', $refinery->encode()->htmlSpecialCharsAsEntities()->transform($foo));
```

tpl.dummy.html:
```html
<h1>{TITLE}</h1>
```

Please keep in mind that the context where the variable in the template is used **is relevant**.
When the variable is used e.g. as an attribute value use the [htmlAttributeValue](#htmlAttributeValue) transformation instead.

###### htmlAttributeValue

HTML attribute values are more restricted than HTML content. This transformation ensures that the transformed string can be safely used as an HTML attribute value.

This is can be used for example in the UI Renderer classes to ensure that strings from ui components cannot be used to inject HTML:
```php
$template = new ilTemplate('tpl.dummy.html', true, true);
$template->setVariable('HREF', $refinery->encode()->htmlAttributeValue()->transform($component->getAction()));
```

tpl.dummy.html:
```html
<a href="{HREF}">Foo</a>
```

###### json

This tranformation is a wrapper around `json_encode` but ensures that the correct flags are set, to circumvent common pitfalls.
These flags ensure that the text can also be embedded in (X)HTML.
Please note that the transformed strings don't need to be in a JS string when embedding in JS (`const foo = {FOO};` instead of `const foo = JSON.parse('{FOO}');`).
```php
$template = new ilTemplate('tpl.dummy.html', true, true);
$template->setVariable('FOO', $refinery->encode()->json()->transform($foo));
```

tpl.dummy.html:
```html
<script>
foo({FOO});
</script>
```

###### url

This transformation can be used to encode a given string which can be used in an URL.
This is a wrapper around `rawurlencode`.
This can be used to encode a string as a valid URL component, which will not be misinterpreted as URL delimiters.
The transformation prevents a value to change other URL parameters & values or the target url path.

```php
$link = $ctrl->setParameterByClass(FooGUI::class, 'bar', $refinery->encode()->url()->transform($foobar));
```

##### Custom

The `Custom` group contains `Transformations` and `Constraints`
that can be used to create individual transformations and constraints.

##### Logical

The `Logical` group contains of `Constraints` that can be used to create
different logical operation that can be used on concrete `Constraints`-

##### Null

`Null` group contains of constraints that can be used to identify the
`null` value via a `Constraint`.

##### Numeric

`Numeric` group consists of a constraints that can be used to identify a
numeric value via a `Constraint`.

##### Password

`Password` consists of a contains that can be used to create constraints
for validating password.

##### String

`String` consist of transformations and constraints which can be applied
to string inputs.

### Custom Transformation

Sometimes the default transformations of this library are not enough, so a
custom transformation is needed.

As every other transformation it must implement the
`ILIAS\Refinery\To\Transformation` interface.

By default these transformation need an implementation for the 
methods `transformation` and `applyTo`.
Because these methods are always containing the same basic process
(with different results types and exception handling),
this library contains traits to ease the creation of new transformation.

The traits that can be used are:
 * [DeriveApplyToFromTransform](#deriveapplytofromtransform)
 * [DeriveTransformFromApplyTo](#derivetransformfromapplyto)

An example shows how the traits can be used.

```php
class BooleanTransformation implements ILIAS\Refinery\Transformation
{
	use ILIAS\Refinery\DeriveApplyToFromTransform;
	use ILIAS\Refinery\DeriveInvokeFromTransform;

	/**
	 * @inheritdoc
	 */
	public function transform($from)
	{
		if (false === is_bool($from)) {
			throw new ILIAS\Refinery\ConstraintViolationException(
				'The value MUST be of type boolean',
				'not_boolean'
			);
		}
		return (bool) $from;
	}
}
```

In the above example we use the trait `DeriveApplyToFromTransform`
and only define the `transform` method.

Please be aware that the error handling can vary
by using these traits.
Checkout the  following chapters for more information.

#### DeriveApplyToFromTransform

This trait is used define `applyTo` on its own.
Just the `transform` method needs to be created in the new transformation class.

##### Error Handling

Exceptions thrown inside the `transformation` method will be
caught and added to new
[error result object (`Result\Error`)](/src/Data/README.md#result).

The origin exception can be accessed through this error object.

#### DeriveTransformFromApplyTo

This trait is used define `transform` on its own.
Just the `applyTo` method needs to be created in the new transformation class.

##### Error Handling

Exceptions thrown inside the `applyTo` method will
**not be** caught.
On return of an [error result object (`Result\Error`)](/src/Data/README.md#result)
the `transform` method will throw an exception.

* If the content of the error object is an exception the exception will be
  thrown.
* If the content of the error object is an string this string will be added
  to a an `Exception` which will be thrown.

#### DeriveTransformWithProblem

This trait is used to simplify the creation of new constraints and reduce duplicated code.
For a constraint only the methods `accepts($value): bool` and `getError()` must be implemented.

#### Other Transformations

* `$refinery->executable()` Returns an object that validates that a given path designates an executable OS path.

## Libraries

These library consists of several sub-libraries,
which have their own descriptions.

### Transformation

A transformation is a function from one type or structure of data to another.
It MUST NOT perform any sideeffects, i.e. it must be morally impossible to observe
how often the transformation was actually performed. It MUST NOT touch the provided
value, i.e. it is allowed to create new values but not to modify existing values.
This would be an observable sideeffect.

The actual usage of this interface is quite boring, but we could typehint on
`Transformation` to announce we indeed want some function having the aforementioned
properties. Typehinting on `Transformation` will be useful when code talks about
structures containing data in some sense, e.g. lists or trees, where the code is
involved with containing structure but not with the contained data. This would be
a classic case for generics in languages that support them. PHP unfortunately is
a language that does not support generics.

The use case that actually led to the proposal of this library is the forms 
abstraction in the UI framework, where the abstraction deals with forms, extraction
of data from them and validation of data in them. The concept of transformation
is required, not matter if we typehint on them or not. Other facilities in PHP
do not allow a more accurate typehinting, due to lack of generics.

Having common transformations ready in a factory, connected with the promise
given by the developer that the `Transformation` indeed respects the intended
properties, should be useful in other scenarios as well, especially at the
boundaries of the system, where data needs to be re- and destructured to fit
interfaces to other systems or even users.

```php

global $DIC;

$f = $DIC->refinery();

// Adding labels to an array to name the elements.
$add_abc_label = $f->container()->addLabels(["a", "b", "c"]);
$labeled = $add_abc_label->transform([1,2,3]);
assert($labeled === ["a" => 1, "b" => 2, "c" => 3]);

// Split a string at some delimiter.
$split_string_at_dot = $f->string()->splitString(".");
$split = $split_string_at_dot->transform("a.b.c");
assert($split === ["a", "b", "c"]);

// Use a closure for the transformation.
$int_to_string = $f->custom()->transformation(function ($v) {
	if (!is_int($v)) {
		throw new \InvalidArgumentException("Expected int, got ".gettype($v));
	}
	return "$v";
});
$str = $int_to_string->transform(5);
assert($str === "5");
assert($str !== 5);
```
### Validation

A validation checks some supplied value for compliance with some constraints.
Validations MUST NOT modify the supplied value.

Having an interface to Validations allows to typehint on them and allows them
to be combined in structured ways. Understanding validation as a separate service
in the system with objects performing the validations makes it possible to build
a set of common validations used throughout the system. Having a known set of
validations makes it possible to perform at least some of the validations on
client side someday.

```php

global $DIC;

$f = $DIC->refinery();

// Build some basic constraints
$gt0 = $f->int()->isGreaterThan(0);
$lt10 = $f->int()->isLessThan(10);

// Check them and react:
if (!$gt0->accepts(1)) {
	assert(false); // does not happen
}

// Let them throw an exception:
$raised = false;
try {
	$lt10->check(20);
	assert(false); // does not happen
}
catch (\UnexpectedValueException $e) {
	$raised = true;
}
assert($raised);

// Get to know what the problem with some value is:
assert(is_string($gt0->problemWith(-10)));

// Combine them in a way that the constraints are checked one after another:
$between_0_10 = $f->logical()->sequential([$gt0, $lt10]);

// Or in a way that they are checked independently:
$also_between_0_10 = $f->logical()->parallel([$gt0, $lt10]);

// One can also create a new error message by supplying a builder for an error
// message:

$between_0_10->withProblemBuilder(function($txt, $value) {
	return "Value must be between 0 and 10, but is '$value'.";
});

// To perform internationalisation, the provided $txt could be used, please
// see `ILIAS\Refinery\Validation\Constraint::withProblemBuilder` for further information.

```

## Data

This folder should contain standard datatypes for ILIAS that are used in many
locations in the system and do not belong to a certain service.

Other examples for data types that could (and maybe should) be added here:

* Option (akin to rusts type)
* (il)Datetime
* ObjectId, ReferenceId
* HTML, Text, Markdown
* List<int>, List<bool>, ...

This is not to be confused with the service for types. This services is about
the data, not the types thereof. It still uses types to talk about data (like
a lot of code does), but it does not reify types as data (which the service
for types and the PHP-ReflectionClass does).

### Result

A result encapsulates a value or an error and simplifies the handling of those.

#### Example 1: Ok

```php
<?php

$f = new \ILIAS\Refinery\Data\Factory;

// Build a value that is ok.
$pi = $f->ok(3.1416);

// Value is ok and thus no error.
itIsTrueThat($pi->isOK());
itIsTrueThat(!$pi->isError());

// Do some transformation with the value.
$r = 10;
$A = $pi->map(function($value_of_pi) use ($r) { return 2 * $value_of_pi * $r; });

// Still ok and no error.
itIsTrueThat($A->isOk());
itIsTrueThat(!$A->isError());

// Retrieve the contained value.
$A_value = $A->value();
itIsTrueThat($A_value == 2 * 3.1416 * 10);

// No error contained...
$raised = false;
try {
	$A->error();
	itIsTrueThat(false); // Won't happen, error raises.
}
catch(\LogicException $e) {
	$raised = true;
}
itIsTrueThat($raised);

?>
```

#### Example 2: Error

```php
<?php

$f = new \ILIAS\Refinery\Data\Factory;

// Build a value that is ok.
$pi = $f->ok(3.1416);

// Build a value that is not ok.
$error = $f->error("There was some error...");

// This is of course an error.
itIsTrueThat(!$error->isOK());
itIsTrueThat($error->isError());

// Transformations do nothing.
$A = $error->map(function($v) { itIsTrueThat(false); });

// Attempts to retrieve the value will throw.
$raised = false;
try {
	$A->value();
	itIsTrueThat(false); // Won't happen.
}
catch (\ILIAS\Refinery\Data\NotOKException $e) {
	$raised = true;
}
itIsTrueThat($raised);

// For retrieving a default could be supplied.
$v = $error->valueOr("default");
itIsTrueThat($v == "default");

// Result also has an interface for chaining computations known as promise
// interface (or monad interface for pros!).

$pi = $pi->then(function($value_of_pi) use ($f) {
	// replace contained value with a more accurate number.
	return $f->ok(3.1415927);
});

// $pi is ok("3.1415927") now. If one had used map instead of then, $pi
// would have been ok(ok(3.1415927).

// One could also inject an error with then, this is not possible with map.
$pi = $pi->then(function($_) use ($f) {
	return $f->error("Do not know value of Pi.");
});

// The error can be catched later on and be corrected:
$pi = $pi->except(function($e) use ($f) {
	itIsTrueThat($e === "Do not know value of Pi.");
	return $f->ok(3); // for large threes
});

itIsTrueThat($pi->value() === 3);

?>
```

### Color
Color is a data type representing a color in HTML.
Construct a color with a hex-value or list of RGB-values.

#### Example

```php
<?php

$f = new \ILIAS\Refinery\Data\Factory;

//construct color with rgb-values:
$rgb = $f->color(array(255,255,0));

//construct color with hex-value:
$hex = $f->color('#ffff00');

itIsTrueThat($rgb->asHex() === '#ffff00');
itIsTrueThat($hex->asRGBString() === 'rgb(255, 255, 0)');
?>
```

### URI
Object representing an uri valid according to RFC 3986 with restrictions imposed on valid characters and obliagtory parts.
Construct a uri with a valid string.

#### Example

```php
<?php

$f = new \ILIAS\Refinery\Data\Factory;

// construct uri
$uri = $f->uri('https://example.org:12345/test?search=test#frag');

itIsTrueThat($uri->getBaseURI() === 'https://example.org:12345/test');
itIsTrueThat($uri->getSchema() === 'https');
itIsTrueThat($uri->getAuthority() === 'example.org:12345');
itIsTrueThat($uri->getHost() === 'example.org');
itIsTrueThat($uri->getPath() === 'test');
itIsTrueThat($uri->getQuery() === 'search=test');
itIsTrueThat($uri->getFragment() === 'frag');
itIsTrueThat($uri->getPort() === 12345);
itIsTrueThat($uri->getParameters() === ['search' => 'test']);
itIsTrueThat($uri->getParameter('search') === 'test');
?>
```

### DataSize
Object representing the size of some data.
Construct a data size object with a size and an unit.

#### Example

```php
<?php

$f = new \ILIAS\Refinery\Data\Factory;

// construct data size
$data_size = $f->dataSize(123, 'GB');

itIsTrueThat($data_size->getSize() === 123.0);
itIsTrueThat($data_size->getUnit() === 1000000000);
itIsTrueThat($data_size->inBytes() === 123000000000.0);
?>
```

### Password
Object representing a password.
Construct a password with a string.

#### Example

```php
<?php

$f = new \ILIAS\Refinery\Data\Factory;

// construct password
$password = $f->Password('secret');

itIsTrueThat($password->toString() === 'secret');
?>
```

### ClientId
Object representing a a alphanummeric string plus #, _, . and -.
Construct a client with a valid string.

#### Example

```php
<?php

$f = new \ILIAS\Refinery\Data\Factory;

// construct client id
$client_id = $f->clientId('Client_Id-With.Special#Chars');

itIsTrueThat($client_id->toString() === 'Client_Id-With.Special#Chars');
?>
```

### ReferenceId
ReferenceId is a data type representing an integer.
Construct a reference id with an integer.

#### Example

```php
<?php

$f = new \ILIAS\Refinery\Data\Factory;

// construct reference id
$ref_id = $f->refId(9);

itIsTrueThat($ref_id->toInt() === 9);
?>
```

### ObjectId
ObjectId is a data type representing an integer.
Construct an object id with an integer.

#### Example

```php
<?php

$f = new \ILIAS\Refinery\Data\Factory;

// construct object id
$ref_id = $f->objId(9);

itIsTrueThat($ref_id->toInt() === 9);
?>
```

### Alphanumeric
Alphanumeric is a data type representing an alphanumeric value.
Construct an alphanumeric with an integer and an alphanumeric value.

#### Example

```php
<?php

$f = new \ILIAS\Refinery\Data\Factory;

// construct alphanumric with integers
$numeric = $f->alphanumeric(963);

// construct alphanumeric with mixed values as string
$alphanumeric = $f->alphanumeric('23da33');

itIsTrueThat($numeric->getValue() === 963);
itIsTrueThat($alphanumeric->getValue() === '23da33');
?>
```

### PositiveInteger
PositiveInteger is a data type representing an positive integer.
Construct an positive integer with an integer.

#### Example

```php
<?php

$f = new \ILIAS\Refinery\Data\Factory;

// construct a positive integer
$positive_integer = $f->positiveInteger(963);

itIsTrueThat($positive_integer->getValue() === 963);
?>
```


### DateFormat
DateFormat is a data type representing a dateformat.
Construct a date format representing a standard, german_short, german_long or custom date format.

#### Example

```php
<?php

$f = new \ILIAS\Refinery\Data\Factory;

// construct standard date format
$standard = $f->dateFormat()->standard();

// construct german short date format
$german_short = $f->dateFormat()->germanShort();

// construct german long date format
$german_long = $f->dateFormat()->germanLong();

// construct custom date format
$custom = $f->dateFormat()->custom()->twoDigitYear()->dash()->month()->dash()->day()->get();

itIsTrueThat($standard->toString() === "Y-m-d");
itIsTrueThat($standard->toArray() === ['Y', '-', 'm', '-', 'd']);

itIsTrueThat($german_short->toString() === "d.m.Y");
itIsTrueThat($german_short->toArray() === ['d', '.', 'm', '.', 'Y']);

itIsTrueThat($german_long->toString() === "l, d.m.Y");
itIsTrueThat($german_long->toArray() === ['l', ',', ' ', 'd', '.', 'm', '.', 'Y']);

itIsTrueThat($custom->toString() === "y-m-d");
itIsTrueThat($custom->toArray() === ['y', '-', 'm', '-', 'd']);
?>
```

### Range
Range is a data type representing a naive range of whole positive numbers.
Construct an range with a start integer and a length integer.

#### Example

```php
<?php

$f = new \ILIAS\Refinery\Data\Factory;

// construct a range
$range = $f->range(10, 20);

itIsTrueThat($range->unpack() === [10, 20]);
itIsTrueThat($range->getStart() === 10);
itIsTrueThat($range->getLength() === 20);
itIsTrueThat($range->getEnd() === 30);
?>
```

### Order
Order is a data type representing a subject with a specific order.
Construct an order with a subject and a direction.

#### Example

```php
<?php

$f = new \ILIAS\Refinery\Data\Factory;

// construct a order
$order1 = $f->order('subject1', 'ASC');

// append subject to order
$order2 = $order1->append('subject2', 'DESC');

// join the subjects to an order statement
$join = $order2->join('sort', function($pre, $k, $v) { return "$pre $k $v,"; });

itIsTrueThat($order1->get() === ['subject1' => 'ASC']);
itIsTrueThat($order2->get() === ['subject1' => 'ASC', 'subject2' => 'DESC']);
itIsTrueThat($join === 'sort subject1 ASC, subject2 DESC,');
?>
```

### Clock

This package provides a fully psr-20 compliant clock handling.

#### Example

##### System Clock

The `\ILIAS\Refinery\Data\Clock\SystemClock` returns a `\DateTimeImmutable` instance always referring to the
current default system timezone.

```php
<?php
$f = new \ILIAS\Refinery\Data\Factory;

$clock = $f->clock()->system();
$now = $clock->now();
?>
```

##### UTC Clock

The `\ILIAS\Refinery\Data\Clock\UtcClock` returns a `\DateTimeImmutable` instance always referring to the
`UTC` timezone.

```php
<?php
$f = new \ILIAS\Refinery\Data\Factory;

$clock = $f->clock()->utc();
$now = $clock->now();
?>
```

##### Local Clock

The `\ILIAS\Refinery\Data\Clock\UtcClock` returns a `\DateTimeImmutable` instance always referring to the
timezone passed to the factory method.

```php
<?php
$f = new \ILIAS\Refinery\Data\Factory;

$clock = $f->clock()->local(new \DateTimeZone('Europe/Berlin'));
$now = $clock->now();
?>
```

### Dimension

#### CardinalDimension
Object representing a metric order, where the distances of the categories are known
and can be described quantitatively.
Construct a cardinal dimension object with numerical or textual variables representing 
the categories.

##### Example

```php
<?php

$f = new \ILIAS\Refinery\Data\Factory;

// construct dimension
$cardinal = $f->dimension()->cardinal(["low", "medium", "high"]);

itIsTrueThat($cardinal->getLabels() === ["low", "medium", "high"]);
?>
```

#### RangeDimension
Object representing a range on a cardinal dimension.
Construct a range dimension object with an existing cardinal dimension.

##### Example

```php
<?php

$f = new \ILIAS\Refinery\Data\Factory;

// construct dimensions
$cardinal = $f->dimension()->cardinal(["low", "medium", "high"]);
$range = $f->dimension()->range($cardinal);

itIsTrueThat($range->getLabels() === $cardinal->getLabels());
?>
```

### Dataset
Object representing a dataset for one or more dimensions.
Construct a dataset with an amount of named dimensions.
Extend a dataset with one or more items by determining e.g. points for each
dimension of the dataset.

#### Example

```php
<?php

$f = new \ILIAS\Refinery\Data\Factory;

// construct dimensions and dataset
$cardinal = $f->dimension()->cardinal([
    0 => "very low",
    1 => "low",
    2 => "medium",
    3 => "high",
    4 => "very high"
]);
$range = $f->dimension()->range($cardinal);
$dataset = $f->dataset([
    "Measurement 1" => $cardinal,
    "Measurement 2" => $cardinal,
    "Target" => $range
]);
$dataset = $dataset->withPoint(
    "Item 1",
    [
        "Measurement 1" => 1,
        "Measurement 2" => 0,
        "Target" => [0, 1.5],
    ]
);
$dataset = $dataset->withPoint(
    "Item 2",
    [
        "Measurement 1" => -1,
        "Measurement 2" => 1.75,
        "Target" => [0.95, 1.05],
    ]
);

itIsTrueThat($dataset->getMinValueForDimension("Measurement 1") === -1.0);
itIsTrueThat($dataset->getMaxValueForDimension("Target") === 1.5);
?>
```

### HTMLMetadata

When working with HTML metadata, you MUST always type-hint `\ILIAS\Refinery\Data\Meta\Html\Tag`, except in rare cases where you
have to work with a collection of tags explicitly (`\ILIAS\Refinery\Data\Meta\Html\TagCollection`).

Currently the factory can only provide `UserDefined` metadata which accepts key => value pairs mapped to a
HTML-meta-tags name and content attribute.

If you have to use something more speficic like e.g. the pragma directive feel free to implement it, derrived from the
abstract class `\ILIAS\Refinery\Data\Meta\Html\Tag`.

#### Example

```php
<?php

$f = new \ILIAS\Refinery\Data\Factory;

$viewport_metadata = $f->htmlMetadata()->userDefined('viewport', 'widht=device-width');

class ExpectsHtmlMetadata {
    public function __construct(
        protected \ILIAS\Refinery\Data\Meta\Html\Tag $html_metadata,
    ) {
    }
    
    public function getMetadata(): \ILIAS\Refinery\Data\Meta\Html\Tag
    {
        return $this->html_metadata;
    }
}

$class = new ExpectsHtmlMetadata(
    $f->htmlMetadata()->collection([
        $f->htmlMetadata()->userDefined('description', 'Lorem ipsum dolor sit amet.'),
        $viewport_metadata
    ])
);

itIsTrueThat(is_string($viewport_metadata->toHtml()));
itIsTrueThat(is_string($class->getMetadata()->toHtml()));
?>
```

### OpenGraphMetadata

OpenGraph metadata is HTML metadata as well, but it's more structured and follows the
open-graph-protocol ([ogp.me](https://ogp.me)).

The factory currently only provides the website-type (of all the possible object-types
documented [here](https://ogp.me/#types)). If you ever need a more specific object-type for e.g. articles or books, feel
free to implement it accordingly.

The factory also provides resources (`\ILIAS\Refinery\Data\Meta\Html\OpenGraph\Resource`), which MUST NOT be used in any other
way than the factory itself. These resources are [structured properties](https://ogp.me/#structured) which cannot be
used standalone and MUST belong to an object-type.

#### Example

```php
<?php

$f = new \ILIAS\Refinery\Data\Factory;

$structured_image = $f->openGraphMetadata()->image($f->uri('https://picsum.photos/200/300'), 'image/jpeg');

$minimal_website_graph = $basic_website_graph = $f->openGraphMetadata()->website(
    $f->uri('https://docu.ilias.de/object/101'),
    $structured_image,
    'object title 101'
);

$full_website_graph = $f->openGraphMetadata()->website(
    $f->uri('https://docu.ilias.de/object/101'),
    $structured_image,
    'object title 101',
    'ILIAS',
    'lorem ipsum dolor sit amet.',
    'en_US',
    ['de_DE', 'de_CH'],
    [
        $f->openGraphMetadata()->image($f->uri('https://picsum.photos/100/100'), 'image/jpeg'),
    ]
);

itIsTrueThat(is_string($minimal_website_graph->toHtml()));
itIsTrueThat(is_string($full_website_graph->toHtml()));
?>
```

### Helper

To make this run, we need a little helper:

```php
<?php

function itIsTrueThat(bool $truth) {
    if (!$truth) {
        throw new \LogicException("Some code in the Data/README.md is wrong!");
    }
}

?>
```

### LanguageTag

This represents a RFC 5646 compliant language tag.
To create a language tag:

```php
(new \ILIAS\Refinery\Data\Factory())->languageTag('de');
```

If the given string is no valid tag an exception is thrown.

The language tag can have several different forms, which are represented with the classes: `Standard`, `Irregular`, `Regular` and `Privateuse`.

The specific meaning of the different language tags can be found in the [RFC](https://www.ietf.org/rfc/bcp/bcp47.txt).
