<?php

namespace Greensight\LaravelElasticQuery\Tests\Unit\Search\Sorting;

use Greensight\LaravelElasticQuery\Search\Cursor;
use Greensight\LaravelElasticQuery\Search\Sorting\Sort;
use Greensight\LaravelElasticQuery\Search\Sorting\SortCollection;
use Greensight\LaravelElasticQuery\Tests\AssertsArray;
use Greensight\LaravelElasticQuery\Tests\Unit\UnitTestCase;
use InvalidArgumentException;

class SortCollectionTest extends UnitTestCase
{
    use AssertsArray;

    private SortCollection $testing;

    protected function setUp(): void
    {
        parent::setUp();

        $this->testing = new SortCollection();
    }

    public function testEmpty(): void
    {
        $this->assertTrue($this->testing->isEmpty());
        $this->assertEquals([], $this->testing->toDSL());
    }

    public function testToDSL(): void
    {
        $this->add('foo', 'bar');

        $this->assertEquals([['foo' => 'asc'], ['bar' => 'asc']], $this->testing->toDSL());
    }

    public function testAddExistingField(): void
    {
        $this->add('foo');
        $this->expectException(InvalidArgumentException::class);

        $this->testing->add(new Sort('foo'));
    }

    public function testKeys(): void
    {
        $this->add('foo', ['bar', 'desc']);

        $this->assertEquals(['+foo', '-bar'], $this->testing->keys());
    }

    public function testInvert(): void
    {
        $this->add('foo', ['bar', 'desc']);

        $this->assertEquals(['-foo', '+bar'], $this->testing->invert()->keys());
    }

    public function testWithTiebreaker(): void
    {
        $this->add('foo');

        $this->assertEquals(['+foo', '+bar'], $this->testing->withTiebreaker('bar')->keys());
    }

    public function testWithExistingTiebreaker(): void
    {
        $this->add('code', ['id', 'desc'], 'rating');

        $this->assertEquals(
            ['+code', '-id', '+rating'],
            $this->testing->withTiebreaker('id')->keys()
        );
    }

    public function testMatchBOFCursor(): void
    {
        $this->add('foo', 'bar');

        $this->assertTrue($this->testing->matchCursor(Cursor::BOF()));
    }

    public function testCreateCursor(): void
    {
        $this->add('foo', 'bar');

        $cursor = $this->testing->createCursor(['sort' => [1, 'value']]);

        $this->assertTrue($this->testing->matchCursor($cursor));
    }

    public function testCreateCursorInvalidSort(): void
    {
        $this->add('foo', 'bar');

        $this->expectException(InvalidArgumentException::class);

        $this->testing->createCursor(['sort' => [1]]);
    }

    private function add(string|array ...$fields): void
    {
        foreach ($fields as $field) {
            $sort = new Sort(...(array)$field);
            $this->testing->add($sort);
        }
    }
}
