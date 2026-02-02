<?php

namespace Ensi\LaravelElasticQuery\Search;

use Closure;
use Ensi\LaravelElasticQuery\Aggregating\AggregationCollection;
use Ensi\LaravelElasticQuery\Concerns\DecoratesBoolQuery;
use Ensi\LaravelElasticQuery\Concerns\ExtendsSort;
use Ensi\LaravelElasticQuery\Contracts\Aggregation;
use Ensi\LaravelElasticQuery\Contracts\CollapsibleQuery;
use Ensi\LaravelElasticQuery\Contracts\DSLAware;
use Ensi\LaravelElasticQuery\Contracts\HighlightingQuery;
use Ensi\LaravelElasticQuery\Contracts\ScriptSortType;
use Ensi\LaravelElasticQuery\Contracts\SearchIndex;
use Ensi\LaravelElasticQuery\Contracts\SortableQuery;
use Ensi\LaravelElasticQuery\Contracts\SortOrder;
use Ensi\LaravelElasticQuery\Filtering\BoolQueryBuilder;
use Ensi\LaravelElasticQuery\Filtering\Criterias\Boosting;
use Ensi\LaravelElasticQuery\Response;
use Ensi\LaravelElasticQuery\Scripts\Script;
use Ensi\LaravelElasticQuery\Search\Collapsing\Collapse;
use Ensi\LaravelElasticQuery\Search\Highlight\Highlight;
use Ensi\LaravelElasticQuery\Search\Sorting\SortBuilder;
use Ensi\LaravelElasticQuery\Search\Sorting\SortCollection;
use Http\Promise\Promise;
use Illuminate\Support\Collection;
use InvalidArgumentException;
use Webmozart\Assert\Assert;

class SearchQuery implements SortableQuery, CollapsibleQuery, HighlightingQuery
{
    use DecoratesBoolQuery;
    use ExtendsSort;

    protected BoolQueryBuilder $boolQuery;
    protected DSLAware $rootQuery;
    protected ?BoolQueryBuilder $postFilter = null;
    protected SortCollection $sorts;
    protected ?Collapse $collapse = null;
    protected ?Highlight $highlight = null;
    protected ?AggregationCollection $aggregations = null;
    protected ?int $size = null;
    protected ?int $from = null;
    protected array $fields = [];
    protected array $include = [];
    protected array $exclude = [];
    protected ?string $searchType = null;

    public function __construct(protected SearchIndex $index)
    {
        $this->boolQuery = $this->createBoolQuery();
        $this->rootQuery = $this->boolQuery;
        $this->sorts = new SortCollection();
    }

    //region Executing
    public function get(): Collection|Promise
    {
        if ($this->size === 0) {
            return new Collection();
        }

        return Response::fn(
            $this->execute(size: $this->size, from: $this->from),
            function (array $response) {
                return $this->parseHits($response);
            }
        );
    }

    public function boosting(DSLAware $negativeQuery, float $negativeBoost): static
    {
        $this->rootQuery = new Boosting($this->boolQuery, $negativeQuery, $negativeBoost);

        return $this;
    }

    public function disableBoosting(): static
    {
        $this->rootQuery = $this->boolQuery;

        return $this;
    }

    public function paginate(int $size, int $offset = 0): Page|Promise
    {
        Assert::greaterThanEq($size, 0);
        Assert::greaterThanEq($offset, 0);

        return Response::fn(
            $this->execute(size: $size, from: $offset, totals: true),
            function (array $response) use ($size, $offset) {
                $hits = $this->parseHits($response);

                return new Page(
                    $size,
                    $hits,
                    aggs: $this->aggregations?->parseResults($response['aggregations'] ?? []),
                    offset: $offset,
                    total: data_get($response, 'hits.total.value', 0)
                );
            }
        );
    }

    public function cursorPaginate(int $size, ?string $cursor = null): CursorPage|Promise
    {
        Assert::greaterThanEq($size, 0);

        $sorts = $this->sorts->withTiebreaker($this->index->tiebreaker());
        $current = Cursor::decode($cursor) ?? Cursor::BOF();

        if (!$sorts->matchCursor($current)) {
            throw new InvalidArgumentException('Cursor is not suitable for current sort');
        }

        return Response::fn(
            $this->execute($sorts, $size, cursor: $current),
            function (array $response) use ($size, $sorts, $current) {
                $hits = $this->parseHits($response);

                return new CursorPage(
                    $size,
                    $hits,
                    aggs: $this->aggregations?->parseResults($response['aggregations'] ?? []),
                    current: $current->encode(),
                    next: $this->findNextCursor($sorts, $size, $hits),
                    previous: $this->findPreviousCursor($sorts, $size, $current)
                );
            }
        );
    }

    private function findNextCursor(SortCollection $sorts, int $size, Collection $hits): ?string
    {
        return $hits->count() < $size
            ? null
            : $sorts->createCursor($hits->last())?->encode();
    }

    private function findPreviousCursor(SortCollection $sorts, int $size, Cursor $current): string|null|Promise
    {
        if ($current->isBOF()) {
            return null;
        }

        return Response::fn(
            $this->execute($sorts->invert(), $size, source: false, cursor: $current),
            function (array $response) use ($sorts, $size) {
                $hits = $this->parseHits($response);

                return $hits->count() < $size
                    ? Cursor::BOF()->encode()
                    : $sorts->createCursor($hits->last())?->encode();
            }
        );
    }

    protected function execute(
        ?SortCollection $sorts = null,
        ?int $size = null,
        ?int $from = null,
        bool $totals = false,
        bool $source = true,
        ?Cursor $cursor = null,
    ): array|Promise {
        $dsl = [
            'size' => $size,
            'from' => $from,
            'query' => $this->rootQuery->toDSL(),
            'track_total_hits' => $totals,
            '_source' => $this->sourceToDSL($source),
            'fields' => $source && $this->fields ? $this->fields : null,
        ];

        $sorts ??= $this->sorts;
        if (!$sorts->isEmpty()) {
            $dsl['sort'] = $sorts->toDSL();
        }

        if (!is_null($this->aggregations)) {
            $dsl['aggs'] = $this->aggregations->toDSL();
        }

        if (!is_null($this->collapse)) {
            $dsl['collapse'] = $this->collapse->toDSL();
        }

        if (!is_null($this->highlight)) {
            $dsl['highlight'] = $this->highlight->toDSL();
        }

        if (!is_null($this->postFilter)) {
            $dsl['post_filter'] = $this->postFilter->toDSL();
        }

        if ($cursor !== null && !$cursor->isBOF()) {
            $dsl['search_after'] = $cursor->toDSL();
        }

        return $this->index->search(array_filter($dsl, fn (mixed $v) => !is_null($v)), $this->searchType);
    }

    protected function sourceToDSL(bool $source): array | bool
    {
        return $source && !$this->fields ?
            [
                'include' => $this->include,
                'exclude' => $this->exclude,
            ] :
            false;
    }

    protected function parseHits(array $response): Collection
    {
        return collect(data_get($response, 'hits.hits') ?? []);
    }

    //endregion

    //region Customization
    public function sortBy(string $field, string $order = SortOrder::ASC, ?string $mode = null, ?string $missingValues = null, ?string $unmappedType = null): static
    {
        (new SortBuilder($this->sorts))
            ->sortBy($field, $order, $mode, $missingValues, $unmappedType);

        return $this;
    }

    public function sortByScript(Script $script, string $type = ScriptSortType::NUMBER, string $order = SortOrder::ASC): static
    {
        (new SortBuilder($this->sorts))
            ->sortByScript($script, $type, $order);

        return $this;
    }

    public function sortByNested(string $field, Closure $callback): static
    {
        (new SortBuilder($this->sorts))->sortByNested($field, $callback);

        return $this;
    }

    public function collapse(string $field, array $innerHits = []): static
    {
        $this->collapse = new Collapse($field, $innerHits);

        return $this;
    }

    public function highlight(Highlight $highlight): static
    {
        $this->highlight = $highlight;

        return $this;
    }

    public function setPostFilter(BoolQueryBuilder $boolQueryBuilder): static
    {
        $this->postFilter = $boolQueryBuilder;

        return $this;
    }

    public function addAggregations(Aggregation $aggregation): static
    {
        $this->aggregations ??= new AggregationCollection();
        $this->aggregations->add($aggregation);

        return $this;
    }

    public function take(int $count): static
    {
        Assert::greaterThanEq($count, 0);

        $this->size = $count;

        return $this;
    }

    public function select(array $include): static
    {
        array_map(Assert::stringNotEmpty(...), $include);

        $this->include = $include;

        return $this;
    }

    public function exclude(array $exclude): static
    {
        array_map(Assert::stringNotEmpty(...), $exclude);

        $this->exclude = $exclude;

        return $this;
    }

    public function skip(int $count): static
    {
        Assert::greaterThanEq($count, 0);

        $this->from = $count;

        return $this;
    }

    public function searchType(string $searchType): static
    {
        Assert::stringNotEmpty($searchType);

        $this->searchType = $searchType;

        return $this;
    }

    //endregion

    protected function boolQuery(): BoolQueryBuilder
    {
        return $this->boolQuery;
    }

    protected function createBoolQuery(): BoolQueryBuilder
    {
        return new BoolQueryBuilder();
    }
}
