<?php

namespace Greensight\LaravelElasticQuery\Search;

use Closure;
use Greensight\LaravelElasticQuery\Concerns\DecoratesBoolQuery;
use Greensight\LaravelElasticQuery\Concerns\ExtendsSort;
use Greensight\LaravelElasticQuery\Contracts\SearchIndex;
use Greensight\LaravelElasticQuery\Contracts\SortableQuery;
use Greensight\LaravelElasticQuery\Contracts\SortOrder;
use Greensight\LaravelElasticQuery\Filtering\BoolQueryBuilder;
use Greensight\LaravelElasticQuery\Search\Sorting\SortBuilder;
use Greensight\LaravelElasticQuery\Search\Sorting\SortCollection;
use Illuminate\Support\Collection;
use InvalidArgumentException;
use Webmozart\Assert\Assert;

class SearchQuery implements SortableQuery
{
    use DecoratesBoolQuery;
    use ExtendsSort;

    protected BoolQueryBuilder $boolQuery;
    protected SortCollection $sorts;
    protected ?int $size = null;
    protected ?int $from = null;
    protected array $fields = [];

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
            '_source' => $source && !$this->fields,
            'fields' => $source && $this->fields ? $this->fields : null,
        ];

        $sorts ??= $this->sorts;
        if (!$sorts->isEmpty()) {
            $dsl['sort'] = $sorts->toDSL();
        }

        if ($cursor !== null && !$cursor->isBOF()) {
            $dsl['search_after'] = $cursor->toDSL();
        }

        return $this->index->search(array_filter($dsl));
    }

    protected function parseHits(array $response): Collection
    {
        return collect(data_get($response, 'hits.hits') ?? []);
    }

    //endregion

    //region Customization
    public function sortBy(string $field, string $order = SortOrder::ASC, ?string $mode = null): static
    {
        (new SortBuilder($this->sorts))->sortBy($field, $order, $mode);

        return $this;
    }

    public function sortByNested(string $field, Closure $callback): static
    {
        (new SortBuilder($this->sorts))->sortByNested($field, $callback);

        return $this;
    }

    public function take(int $count): static
    {
        Assert::greaterThanEq($count, 0);

        $this->size = $count;

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
