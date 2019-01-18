<?php

namespace fv\test;

use yii\base\DynamicModel;
use fv\yii\behaviors\DataTransformation;

class DataTransformationTest extends \Codeception\Test\Unit
{
    protected function newMock($transform, $transformBack = null)
    {
        $mock = $this->getMockForAbstractClass(DataTransformation::class);

        $methods = ['transform'];
        if ($transformBack) {
            $methods[] = 'transformBack';
        }

        $mock = $this->getMockBuilder(DataTransformation::class)
            ->disableOriginalConstructor()
            ->setMethods($methods)
            ->getMockForAbstractClass();

        $mock->method('transform')
            ->will($this->returnCallback($transform));

        if ($transformBack) {
            $mock->method('transformBack')
                ->will($this->returnCallback($transformBack));
        }
        return $mock;
    }


    public function testGetSet()
    {
        $mock = $this->newMock(function($value) {
            return '@' . $value . '@';
        });

        $mock->attributes = [
            'foo_t' => 'foo',
            'zoo_t' => 'zoo',
        ];

        $model = new DynamicModel([
            'foo' => 'bar',
            'zoo' => null,
        ]);
        $model->attachBehavior('dt', $mock);

        $model->zoo = 'lee';

        $this->assertEquals('@bar@', $model->foo_t);
        $this->assertEquals('@lee@', $model->zoo_t);
    }


    public function testDefaultNullHandling()
    {
        $mock = $this->newMock(
            function($value) {
                throw new \RuntimeException('Got null in transform()');
            },
            function($value) {
                throw new \RuntimeException('Got null in transformBack()');
            }
        );

        $model = new DynamicModel([
            'foo' => null,
        ]);
        $mock->attributes = ['foo_t' => 'foo'];
        $model->attachBehavior('dt', $mock);

        $this->assertNull($model->foo_t);

        // This should not raise an exception.
        $model->foo = null;
    }

    public function testNullHandling()
    {
        $mock = $this->newMock(
            function($value) {
                return $value === null ? '@NULL@' : $value;
            },
            function($value) {
                return $value === '@NULL@' ? null : $value;
            }
        );

        $model = new DynamicModel([
            'foo' => 'bar',
        ]);
        $mock->attributes = ['foo_t' => 'foo'];
        $mock->autoConvertNull = false;
        $model->attachBehavior('dt', $mock);


        $model->foo = null;
        $this->assertEquals('@NULL@', $model->foo_t);

        $model->foo = 'bar';
        $this->assertEquals('bar', $model->foo_t);

        $model->foo_t = 'zoo';
        $this->assertEquals('zoo', $model->foo);

        $model->foo_t = '@NULL@';
        $this->assertNull($model->foo);

    }


    public function testInvalidConfiguration()
    {
        $mock = $this->newMock(function($value) {
            return 'foo';
        });

        $model = new DynamicModel();

        $this->expectException(\yii\base\InvalidConfigException::class);
        $model->attachBehavior('dt', $mock);
    }
}
