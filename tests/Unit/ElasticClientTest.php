<?php

namespace Ensi\LaravelElasticQuery\Tests\Unit;

use Ensi\LaravelElasticQuery\ElasticClient;

class ElasticClientTest extends UnitTestCase
{
    /**
     * @dataProvider provideBasicAuthData
     */
    public function testResolveBasicAuthData(array $config, array $expected): void
    {
        $this->assertEquals($expected, ElasticClient::resolveBasicAuthData($config));
    }

    public function provideBasicAuthData(): array
    {
        return [
            'separate username and password' => [
                [
                    'hosts' => ['https://elastic.domain.io:9200'],
                    'username' => 'foo',
                    'password' => 'bar',
                ],
                ['foo', 'bar'],
            ],
            'separate username without password' => [
                [
                    'hosts' => ['https://elastic.domain.io:9200'],
                    'username' => 'foo',
                ],
                ['foo', ''],
            ],
            'username and password in the host' => [
                ['hosts' => ['https://elastic1.domain.io:9200', 'https://foo:bar@elastic2.domain.io:9200']],
                ['foo', 'bar'],
            ],
            'only username in the host' => [
                ['hosts' => ['https://foo@elastic1.domain.io:9200', 'https://elastic2.domain.io:9200']],
                ['foo', ''],
            ],
            'missing auth data' => [
                ['hosts' => ['https://elastic.domain.io:9200']],
                ['', ''],
            ],
        ];
    }
}
