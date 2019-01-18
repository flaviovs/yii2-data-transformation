Data transformation behavior for Yii2 models
============================================

This extension provides a behavior that makes easy to add
data-transformed attributes into Yii2 models. It does that by creating
virtual attributes that automatically convert data back-and-forth upon
set/get operations.

Common use cases:

 * You store dates in the database using Unix timestamps and/or date
   strings, but want to manipulate them as
   [DateTime objects](http://php.net/manual/en/class.datetime.php)

 * Automatically data serialization

 * Calculated attributes

See the
[Data Transformation section in the ActiveRecord documentation](https://www.yiiframework.com/doc/guide/2.0/en/db-active-record#data-transformation)
for more information about what problems this extensions helps you to
solve.


Installation
============

```
composer require flaviovs/yii2-data-transformation
```


How to use
==========

This extension provides an **abstract** class that you can use to
create data transformation attributes. In other words, **you need to
define your own behavior class to do the transformations you want**
(or look for one created by somebody else).

To create a data transformation class, extend from
`\fv\yii\behaviors\DataTransformation` and add the following methods:

* `transform($value)` (required) - apply data transformation to
  original model value `$value` (e.g. a Unix timestamp) and return the
  transformed result (e.g. a `\DateTimeImmutable`).

* `transformBack($value)` (optional) - apply data transformation to a
  transformed value `$value` (e.g. a `\DateTimeImmutable`) and return
  the original model representation (e.g. a Unix timestamp). This
  method is optional -- the inherited implementation just cast
  `$value` to `string`.


NULL handling
-------------

By default data transformation methods will never receive `null`
values -- `fv\yii\behaviors\DataTransformation` will automatically
intercept then, and automatically return `null`. You can change this
behavior by setting `$autoConvertNull` to `false` in your class (in
which case, of course, your conversion methods must be prepared to
handle `null` values).

For example:

```php
class MyBehavior extends \fv\yii\behaviors\DataTransformation
{
    public $autoConvertNull = false;

    // (...)
}
```


Model usage
-----------

When adding data-transformation behaviors to models, you should
specify the attributes to be transformed in an `attributes` key in the
configuration array. Attributes must be specified as key/value pair,
where *key* is the transformed (virtual) attribute, and *value* is the
original attribute.


Examples
========

## Automatic date conversion

This behavior will allow `\DateTime` objects access to Unix
timestamps-based attributes:

```php
class DateTimeImmutableBehavior extends \fv\yii\behaviors\DataTransformation
{
	protected function transform($value)
	{
		return new \DateTimeImmutable('@' . $value);
	}

    protected function transformBack($value)
    {
        return $value->getTimestamp();
    }
}
```

Now on your model:

```php
class MyModel extends \yii\base\Model
{
	/**
	 * Unix timestamp attributes.
	 *
	 * @var integer
	 */
	public $created;
	public $updated;

	function behaviors()
	{
		return [
			'datetime' => [
				'class' => \app\behaviors\DateTimeImmutableBehavior::class,
					'attributes' => [
					'createdDateTime' => 'created',
					'updatedDateTime' => 'updated',
				],
			],
		];
	}
}
```

In your code:

```php
// Print our timestamp property.
print_r($mymodel->created);
/*
1547346253
*/

// Print our data transformation property.
echo $mymodel->createdDateTime;
/*
DateTimeImmutable Object
(
    [date] => 2019-01-13 02:24:13.000000
    [timezone_type] => 1
    [timezone] => +00:00
)
*/

// Set value using date transformation.
$mymodel->updatedDateTime = new DateTime('2018-12-01 00:00:00');

// Output transformed value.
echo $mymodel->updated;
/*
1543651200
*/
```

Support
=======

Visit https://github.com/flaviovs/yii2-data-transformation
