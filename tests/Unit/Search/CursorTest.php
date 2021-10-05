<?php

namespace Ensi\LaravelElasticQuery\Tests\Unit\Search;

use Ensi\LaravelElasticQuery\Search\Cursor;
use Ensi\LaravelElasticQuery\Tests\Unit\UnitTestCase;

class CursorTest extends UnitTestCase
{
    public function testBOF(): void
    {
        $this->assertTrue(Cursor::BOF()->isBOF());
    }

    public function testBOFToDSL(): void
    {
        $this->assertEquals([], Cursor::BOF()->toDSL());
    }

    public function testBOFEncode(): void
    {
        $result = Cursor::decode(Cursor::BOF()->encode());

        $this->assertNotNull($result);
        $this->assertTrue($result->isBOF());
    }

    public function testToDSL(): void
    {
        $testing = new Cursor(['+foo' => 1, '-bar' => 'string']);

        $this->assertEquals([1, 'string'], $testing->toDSL());
    }

    public function testKeys(): void
    {
        $testing = new Cursor(['+foo' => 1, '-bar' => 'string']);

        $this->assertEquals(['+foo', '-bar'], $testing->keys());
    }

    public function testEncode(): void
    {
        $testing = new Cursor(['+foo' => 1, '-bar' => 'string']);

        $result = Cursor::decode($testing->encode());

        $this->assertEquals($testing, $result);
    }
}
