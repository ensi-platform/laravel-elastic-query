<?php

namespace Ensi\LaravelElasticQuery\Scripts;

use Ensi\LaravelElasticQuery\Contracts\DSLAware;
use Ensi\LaravelElasticQuery\Contracts\ScriptLang;
use Webmozart\Assert\Assert;

class Script implements DSLAware
{
    public function __construct(
        private string $lang = ScriptLang::PAINLESS,
        private array $params = [],
        private ?string $source = null,
        private ?string $id = null,
    ) {
        Assert::oneOf($lang, ScriptLang::cases());
        Assert::notNull($source ?? $id);
    }

    public function addParam(string $name, mixed $value): self
    {
        $this->params[$name] = $value;

        return $this;
    }

    public function toDSL(): array
    {
        $dsl = [
            'lang' => $this->lang,
            'params' => $this->params,
        ];

        if ($this->source) {
            $dsl['source'] = $this->source;
        } elseif ($this->id) {
            $dsl['id'] = $this->id;
        }

        return $dsl;
    }
}
