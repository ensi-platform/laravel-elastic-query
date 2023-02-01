<?php

namespace Ensi\LaravelElasticQuery\Tests\Unit\Suggesting;

use Ensi\LaravelElasticQuery\Suggesting\Enums\SuggestMode;
use Ensi\LaravelElasticQuery\Suggesting\Enums\SuggestSort;
use Ensi\LaravelElasticQuery\Suggesting\Enums\SuggestStringDistance;
use Ensi\LaravelElasticQuery\Suggesting\Request\PhraseSuggester;
use Ensi\LaravelElasticQuery\Suggesting\Request\Suggester;
use Ensi\LaravelElasticQuery\Suggesting\Request\TermSuggester;
use Ensi\LaravelElasticQuery\Suggesting\SuggesterCollection;
use Ensi\LaravelElasticQuery\Tests\AssertsArray;
use Ensi\LaravelElasticQuery\Tests\Unit\UnitTestCase;
use InvalidArgumentException;
use Mockery;

class SuggesterCollectionTest extends UnitTestCase
{
    use AssertsArray;

    public function testEmptyToDSL(): void
    {
        $testing = new SuggesterCollection();

        $this->assertEquals([], $testing->toDSL());
    }

    public function testAdd(): void
    {
        $expected = ['foo' => 'body'];
        $testing = new SuggesterCollection();
        $testing->add($this->mockSuggester('suggest', $expected));

        $this->assertEquals(['suggest' => $expected], $testing->toDSL());
    }

    public function testAddAlreadyExistingName(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $testing = new SuggesterCollection();

        $testing->add($this->mockSuggester('sug'));
        $testing->add($this->mockSuggester('sug'));
    }

    public function testToDSL(): void
    {
        $testing = new SuggesterCollection();

        $testing->add((new PhraseSuggester('phrase', 'b'))->text('text')
            ->gramSize(1)
            ->realWordErrorLikelihood(1)
            ->confidence(0.1)
            ->maxErrors(2)
            ->separator('-')
            ->size(1)
            ->analyzer('foo')
            ->shardSize(1)
            ->highlight('tag1', 'tag2'));

        $testing->add((new TermSuggester('term', 'c'))->text('text2')
            ->analyzer('foo')
            ->size(1)
            ->sortScore()
            ->sortFrequency()
            ->suggestModeMissing()
            ->suggestModePopular()
            ->suggestModeAlways()
            ->maxEdits(2)
            ->prefixLength(1)
            ->minWordLength(1)
            ->shardSize(1)
            ->maxInspections(1)
            ->minDocFreq(1)
            ->stringDistanceInternal()
            ->stringDistanceDamerauLevenshtein()
            ->stringDistanceLevenshtein()
            ->stringDistanceJaroWinkler()
            ->stringDistanceNgram());


        $this->assertEquals([
            'phrase' => [
                "text" => 'text',
                "phrase" => [
                    "field" => 'b',

                    "gram_size" => 1,
                    "real_word_error_likelihood" => 1,
                    "confidence" => 0.1,
                    "max_errors" => 2,
                    "separator" => '-',
                    "size" => 1,
                    "analyzer" => 'foo',
                    "shard_size" => 1,
                    "highlight" => [
                        "pre_tag" => 'tag1',
                        "post_tag" => 'tag2',
                    ],
                ],
            ],
            'term' => [
                "text" => 'text2',
                "term" => [
                    "field" => 'c',

                    "analyzer" => 'foo',
                    "size" => 1,
                    "sort" => SuggestSort::FREQUENCY,
                    "suggest_mode" => SuggestMode::ALWAYS,

                    "max_edits" => 2,
                    "prefix_length" => 1,
                    "min_word_length" => 1,
                    "shard_size" => 1,
                    "max_inspections" => 1,
                    "min_doc_freq" => 1,
                    "string_distance" => SuggestStringDistance::NGRAM,
                ],
            ],
        ], $testing->toDSL());
    }

    public function testParseResults(): void
    {
        $testing = new SuggesterCollection();
        $testing->add($this->mockSuggester('sug1'));
        $testing->add($this->mockSuggester('sug2'));

        $parseResults = $testing->parseResults([
            'sug1' => [
                [
                    'text' => 'text',
                    'offset' => 0,
                    'length' => 14,
                    'options' => [],
                ],
            ],
            'sug2' => [
                [
                    'text' => 'text2',
                    'offset' => 0,
                    'length' => 15,
                    'options' => [
                        [
                            'text' => 't',
                        ],
                    ],
                ],
            ],
        ]);
        $this->assertCount(2, $parseResults);
        $this->assertArrayStructure(['sug1', 'sug2'], $parseResults->all());
        $this->assertCount(1, $parseResults->get('sug2')->first()->options);
    }

    private function mockSuggester(string $name, ?array $dsl = null): Suggester
    {
        $s = Mockery::mock(Suggester::class);
        $s->allows('name')->andReturn($name);

        if ($dsl !== null) {
            $s->allows('toDSL')->andReturn($dsl);
        }

        return $s;
    }
}
