# Laravel Elastic Query

Working with Elasticsearch in an Eloquent-like fashion.

## Installation

You can install the package via composer:

1. `composer require ensi/laravel-elastic-query`
2. Set `ELASTICSEARCH_HOSTS` in your `.env` file. `,` can be used as a delimeter.

## Basic usage

Let's create and index class. It's someting like Eloquent model.

```php
use Ensi\LaravelElasticQuery\ElasticIndex;

class ProductsIndex extends ElasticIndex
{
    protected string $name = 'test_products';
    protected string $tiebreaker = 'product_id';
}
```

You should set a unique in document attribute name in `$tiebreaker`. It is used as an additional sort in `search_after`

Now we can get some documents

```php
$searchQuery = ProductsIndex::query();

$hits = $searchQuery
             ->where('rating', '>=', 5)
             ->whereDoesntHave('offers', fn(BoolQuery $query) => $query->where('seller_id', 10)->where('active', false))
             ->sortBy('rating', 'desc')
             ->sortByNested('offers', fn(SortableQuery $query) => $query->where('active', true)->sortBy('price', mode: 'min'))
             ->take(25)
             ->get();
```

### Filtering

```php
$searchQuery->where('field', 'value');
$searchQuery->where('field', '>', 'value'); // supported operators: `=` `!=` `>` `<` `>=` `<=`
$searchQuery->whereNot('field', 'value'); // equals `where('field', '!=', 'value')`
```

```php
$searchQuery->whereIn('field', ['value1', 'value2']);
$searchQuery->whereNotIn('field', ['value1', 'value2']);
```

```php
$searchQuery->whereNull('field');
$searchQuery->whereNotNull('field');
```

```php
$searchQuery->whereHas('nested_field', fn(BoolQuery $subQuery) => $subQuery->where('field_in_nested', 'value'));
$searchQuery->whereDoesntHave(
    'nested_field',
    function (BoolQuery $subQuery) {
        $subQuery->whereHas('nested_field', fn(BoolQuery $subQuery2) => $subQuery2->whereNot('field', 'value'));
    }
);
```

`nested_field` must have `nested` type.
Subqueries cannot use fields of main document only subdocument.

### Full text search

```php
$searchQuery->whereMatch('field_one', 'query string');
$searchQuery->whereMultiMatch(['field_one^3', 'field_two'], 'query string', MatchType::MOST_FIELDS);
$searchQuery->whereMultiMatch([], 'query string');  // search by all text fields
```

`field_one` and `field_two` must be of text type. If no type is given, the `MatchType::BEST_FIELDS` is used.

### Sorting

```php
$searchQuery->sortBy('field', SortOrder::DESC, SortMode::MAX, MissingValuesMode::FIRST); // field is from main document
$searchQuery->sortByNested(
    'nested_field',
    fn(SortableQuery $subQuery) => $subQuery->where('field_in_nested', 'value')->sortBy('field')
);
```

Second attribute is a direction. It supports `asc` and `desc` values. Defaults to `asc`.  
Third attribute - sorting type. List of supporting types: `min, max, avg, sum, median`. Defaults to `min`.

There are also dedicated sort methods for each sort type.

```php
$searchQuery->minSortBy('field', 'asc');
$searchQuery->maxSortBy('field', 'asc');
$searchQuery->avgSortBy('field', 'asc');
$searchQuery->sumSortBy('field', 'asc');
$searchQuery->medianSortBy('field', 'asc');
```

### Pagination

#### Offset Pagination

```php
$page = $searchQuery->paginate(15, 45);
```

Offset pagination returns total documents count as `total` and current position as `size/offset`.

#### Cursor pagination

```php
$page = $searchQuery->cursorPaginate(10);
$pageNext = $searchQuery->cursorPaginate(10, $page->next);
```

 `current`, `next`, `previous` is returned in this case instead of `total`, `size` and `offset`.
 You can check Laravel docs for more info about cursor pagination.

## Aggregation

Aggregaction queries can be created like this

```php
$aggQuery = ProductsIndex::aggregate();

/** @var \Illuminate\Support\Collection $aggs */
$aggs = $aggQuery
            ->where('active', true)
            ->terms('codes', 'code')
            ->count('product_count', 'product_id')
            ->nested(
                'offers',
                fn(AggregationsBuilder $builder) => $builder->where('seller_id', 10)->minmax('price', 'price')
            );
            
```

Type of `$aggs->price` is `MinMax`.
Type of `$aggs->codes` is `BucketCollection`.
Aggregate names must be unique for whole query.


### Aggregate types

Get all variants of attribute values:

```php
$aggQuery->terms('agg_name', 'field', 25);
```

Get min and max attribute values. E.g for date:

```php
$aggQuery->minmax('agg_name', 'field');
```

Get count unique attribute values:

```php
$aggQuery->count('agg_name', 'field');
```


Aggregation plays nice with nested documents.

```php
$aggQuery->nested('nested_field', function (AggregationsBuilder $builder) {
    $builder->terms('name', 'field_in_nested');
});
```

There is also a special virtual `composite` aggregate on the root level. You can set special conditions using it.

```php
$aggQuery->composite(function (AggregationsBuilder $builder) {
    $builder->where('field', 'value')
        ->whereHas('nested_field', fn(BoolQuery $query) => $query->where('field_in_nested', 'value2'))
        ->terms('field1', 'agg_name1')
        ->minmax('field2', 'agg_name2');
});
```

## Suggesting

Suggest queries can be created like this

```php
$sugQuery = ProductsIndex::suggest();

/** @var \Illuminate\Support\Collection $suggests */
$suggests = $sugQuery->phrase('suggestName', 'name.trigram')
    ->text('glves')
    ->size(1)
    ->shardSize(3)
    ->get();
            
```

### Global suggest text

User can set global text like this

```php
$sugQuery = ProductsIndex::suggest()->text('glves');

$sugQuery->phrase('suggestName1', 'name.trigram')->size(1)->shardSize(3);
    
$sugQuery->phrase('suggestName2', 'name.trigram');
    
/** @var \Illuminate\Support\Collection $suggests */
$suggests = $sugQuery->get();
            
```


### Suggester types

Term suggester:

```php
$aggQuery->term('suggestName', 'name.trigram')->text('glves')->...->get();
```

Phrase Suggester:

```php
$aggQuery->phrase('suggestName', 'name.trigram')->text('glves')->...->get();
```

## Additional methods

```php
$index = new ProductsIndex();

$index->isCreated(); // Check if index are created 
$index->create(); // Create index with structure from settings() method
$index->bulk(); // Send bulk request
$index->get(); // Send get request
$index->documentDelete(); // Send documentDelete request
$index->deleteByQuery(); // Send deleteByQuery request

$index->catIndices();
$index->indicesDelete();
$index->indicesRefresh();
```

## Query Log

Just like Eloquent ElasticQuery has its own query log, but you need to enable it manually
Each message contains `indexName`, `query` and `timestamp`

```php
ElasticQuery::enableQueryLog();

/** @var \Illuminate\Support\Collection|Ensi\LaravelElasticQuery\Debug\QueryLogRecord[] $records */
$records = ElasticQuery::getQueryLog();

ElasticQuery::disableQueryLog();
```

## Contributing

Please see [CONTRIBUTING](.github/CONTRIBUTING.md) for details.

### Testing

1. composer install
2. npm i
3. Start Elasticsearch in your preferred way.
4. Copy `phpunit.xml.dist` to `phpunit.xml` and set correct env variables there
6. composer test

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
