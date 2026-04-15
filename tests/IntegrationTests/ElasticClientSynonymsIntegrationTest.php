<?php

use Ensi\LaravelElasticQuery\ElasticClient;
use Ensi\LaravelElasticQuery\Tests\IntegrationTestCase;
use Illuminate\Support\Str;

uses(IntegrationTestCase::class);

function makeSynonymSetId(): string
{
    return 'leq-synonyms-' . Str::lower(Str::random(12));
}

test('elastic client can manage synonym set lifecycle', function () {
    /** @var ElasticClient $client */
    $client = resolve(ElasticClient::class);

    $setId = makeSynonymSetId();

    try {
        $createResponse = $client->putSynonymSet($setId, [
            ['id' => 'rule-1', 'synonyms' => 'iphone, i-phone'],
            ['id' => 'rule-2', 'synonyms' => 'tv, television'],
        ]);

        expect($createResponse)->toBeArray()
            ->and($createResponse)->toHaveKey('result');

        $setResponse = $client->getSynonymSet($setId);

        expect($setResponse)->toBeArray()
            ->and($setResponse)->toHaveKey('count')
            ->and($setResponse)->toHaveKey('synonyms_set')
            ->and($setResponse['count'])->toBe(2)
            ->and($setResponse['synonyms_set'])->toHaveCount(2);

        assertArrayFragment(
            ['id' => 'rule-1', 'synonyms' => 'iphone, i-phone'],
            $setResponse['synonyms_set']
        );

        $listResponse = $client->getSynonymsSets();

        expect($listResponse)->toBeArray()
            ->and($listResponse)->toHaveKey('count')
            ->and($listResponse)->toHaveKey('results')
            ->and(collect($listResponse['results'])->contains(
                fn(array $item) => ($item['synonyms_set'] ?? null) === $setId
            ))->toBeTrue();

    } finally {
        $client->deleteSynonymSet($setId);
    }
});

test('elastic client can manage synonym rule lifecycle', function () {
    /** @var ElasticClient $client */
    $client = resolve(ElasticClient::class);

    $setId = makeSynonymSetId();

    try {
        $client->putSynonymSet($setId, [
            ['id' => 'rule-1', 'synonyms' => 'iphone, i-phone'],
        ]);

        $ruleResponse = $client->getSynonymRule($setId, 'rule-1');

        expect($ruleResponse)->toBeArray()
            ->and($ruleResponse)->toMatchArray([
                'id' => 'rule-1',
                'synonyms' => 'iphone, i-phone',
            ]);

        $updateResponse = $client->putSynonymRule($setId, 'rule-1', 'iphone, i-phone, ios-phone');

        expect($updateResponse)->toBeArray()
            ->and($updateResponse)->toHaveKey('result');

        $updatedRuleResponse = $client->getSynonymRule($setId, 'rule-1');

        expect($updatedRuleResponse)->toMatchArray([
            'id' => 'rule-1',
            'synonyms' => 'iphone, i-phone, ios-phone',
        ]);

        $deleteResponse = $client->deleteSynonymRule($setId, 'rule-1');

        expect($deleteResponse)->toBeArray()
            ->and($deleteResponse)->toHaveKey('result');

        $setResponse = $client->getSynonymSet($setId);

        expect($setResponse)->toHaveKey('count')
            ->and($setResponse['count'])->toBe(0);
    } finally {
        $client->deleteSynonymSet($setId);
    }
});
