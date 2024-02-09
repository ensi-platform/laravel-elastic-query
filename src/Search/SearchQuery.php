<?php

namespace Ensi\LaravelElasticQuery\Search;

use Closure;
use Ensi\LaravelElasticQuery\Aggregating\AggregationCollection;
use Ensi\LaravelElasticQuery\Concerns\DecoratesBoolQuery;
use Ensi\LaravelElasticQuery\Concerns\ExtendsSort;
use Ensi\LaravelElasticQuery\Contracts\Aggregation;
use Ensi\LaravelElasticQuery\Contracts\CollapsibleQuery;
use Ensi\LaravelElasticQuery\Contracts\SearchIndex;
use Ensi\LaravelElasticQuery\Contracts\SortableQuery;
use Ensi\LaravelElasticQuery\Contracts\SortOrder;
use Ensi\LaravelElasticQuery\Filtering\BoolQueryBuilder;
use Ensi\LaravelElasticQuery\Search\Collapsing\Collapse;
use Ensi\LaravelElasticQuery\Search\Sorting\SortBuilder;
use Ensi\LaravelElasticQuery\Search\Sorting\SortCollection;
use Illuminate\Support\Collection;
use InvalidArgumentException;
use Webmozart\Assert\Assert;

class SearchQuery implements SortableQuery, CollapsibleQuery
{
    use DecoratesBoolQuery;
    use ExtendsSort;

    protected BoolQueryBuilder $boolQuery;
    protected SortCollection $sorts;
    protected ?Collapse $collapse = null;
    protected ?AggregationCollection $aggregations = null;
    protected ?int $size = null;
    protected ?int $from = null;
    protected array $fields = [];
    protected array $include = [];
    protected array $exclude = [];

    public function __construct(protected SearchIndex $index)
    {
        $this->boolQuery = $this->createBoolQuery();
        $this->sorts = new SortCollection();
    }

    //region Executing
    public function get(): Collection
    {
        if ($this->size === 0) {
            return new Collection();
        }

        $response = $this->execute(size: $this->size, from: $this->from);

        return $this->parseHits($response);
    }

    public function paginate(int $size, int $offset = 0): Page
    {
        Assert::greaterThan($size, 0);
        Assert::greaterThanEq($offset, 0);

        $response = $this->execute(size: $size, from: $offset, totals: true);
        $hits = $this->parseHits($response);

        return new Page(
            $size,
            $hits,
            aggs: $this->aggregations?->parseResults($response['aggregations'] ?? []),
            offset: $offset,
            total: data_get($response, 'hits.total.value', 0)
        );
    }

    public function cursorPaginate(int $size, ?string $cursor = null): CursorPage
    {
        Assert::greaterThan($size, 0);

        $sorts = $this->sorts->withTiebreaker($this->index->tiebreaker());
        $current = Cursor::decode($cursor) ?? Cursor::BOF();

        if (!$sorts->matchCursor($current)) {
            throw new InvalidArgumentException('Cursor is not suitable for current sort');
        }

        $response = $this->execute($sorts, $size, cursor: $current);
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

    private function findNextCursor(SortCollection $sorts, int $size, Collection $hits): ?string
    {
        return $hits->count() < $size
            ? null
            : $sorts->createCursor($hits->last())?->encode();
    }

    private function findPreviousCursor(SortCollection $sorts, int $size, Cursor $current): ?string
    {
        if ($current->isBOF()) {
            return null;
        }

        $response = $this->execute($sorts->invert(), $size, source: false, cursor: $current);
        $hits = $this->parseHits($response);

        return $hits->count() < $size
            ? Cursor::BOF()->encode()
            : $sorts->createCursor($hits->last())?->encode();
    }

    protected function execute(
        ?SortCollection $sorts = null,
        ?int $size = null,
        ?int $from = null,
        bool $totals = false,
        bool $source = true,
        ?Cursor $cursor = null
    ): array {
        $dsl = [
            'size' => $size,
            'from' => $from,
            'query' => $this->boolQuery->toDSL(),
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

        if ($cursor !== null && !$cursor->isBOF()) {
            $dsl['search_after'] = $cursor->toDSL();
        }

        return $this->index->search(array_filter($dsl));
    }

    protected function sourceToDSL(bool $source): array | bool
    {
        return  $source && !$this->fields ?
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
    public function sortBy(string $field, string $order = SortOrder::ASC, ?string $mode = null, ?string $missingValues = null): static
    {
        (new SortBuilder($this->sorts))
            ->sortBy($field, $order, $mode, $missingValues);

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
