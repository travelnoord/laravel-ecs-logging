<?php

namespace Travelnoord\Logging\Fields;

use Hamidrezaniazi\Pecs\EcsFieldsCollection;
use Hamidrezaniazi\Pecs\Fields\AbstractEcsField;
use Hamidrezaniazi\Pecs\Fields\Base;
use Hamidrezaniazi\Pecs\Properties\PairList;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

use function collect;

class Label extends AbstractEcsField
{
    public readonly string $key;

    public function __construct(
        string $key,
        public readonly string|int|float $value,
    ) {
        $this->key = Label::normalizeKey($key);
        parent::__construct(false);
    }

    protected function key(): ?string
    {
        return null;
    }

    protected function body(): Collection
    {
        return collect();
    }

    public function wrapper(): EcsFieldsCollection
    {
        return parent::wrapper()->push(
            new Base(
                labels: (new PairList())->put(
                    $this->key,
                    $this->value,
                ),
            ),
        );
    }

    public static function normalizeKey(string $key): string
    {
        return str_replace('.', '_', Str::snake($key));
    }
}
