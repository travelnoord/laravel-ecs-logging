<?php

namespace Travelnoord\Logging\Fields;

use Hamidrezaniazi\Pecs\EcsFieldsCollection;
use Hamidrezaniazi\Pecs\Fields\AbstractEcsField;
use Hamidrezaniazi\Pecs\Fields\Base;
use Hamidrezaniazi\Pecs\Properties\ValueList;
use Illuminate\Support\Collection;

class Tag extends AbstractEcsField
{
    public function __construct(
        public string|int|float $value,
    ) {
        parent::__construct();
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
        return parent::wrapper()->push(new Base(tags: (new ValueList())->push($this->value)));
    }
}
