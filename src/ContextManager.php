<?php

namespace Travelnoord\Logging;

use Hamidrezaniazi\Pecs\Fields\AbstractEcsField;
use Illuminate\Support\Facades\Context;
use Travelnoord\Logging\Fields\Label;
use Travelnoord\Logging\Fields\Tag;

class ContextManager
{
    public const string ContextKey = 'ecs.context';

    /**
     * @var string[]
     */
    private array $secrets = [];

    public function add(AbstractEcsField ...$items): static
    {
        foreach ($items as $item) {
            Context::push('ecs.context', $item);
        }

        return $this;
    }

    public function label(string $key, string|int|float $value): static
    {
        return $this->add(new Label($key, $value));
    }

    public function tag(string|int|float $value): static
    {
        return $this->add(new Tag($value));
    }

    /**
     * @param  string|array<scalar>  $secrets
     */
    public function secret(array|string $secrets): static
    {
        if (! is_array($secrets)) {
            $secrets = [$secrets];
        }

        foreach ($secrets as $filter) {
            if (! is_string($filter)) {
                continue;
            }

            $this->secrets = array_unique(array_filter(
                array_merge($this->secrets, [
                    $filter,
                    urlencode($filter),
                    rawurlencode($filter),
                    htmlentities($filter),
                    htmlspecialchars($filter),
                    substr((string)json_encode($filter), 1, -1),
                ]),
            ));
        }

        return $this;
    }

    public function scrub(string $line, string $replacement = '*****'): string
    {
        return str_replace($this->secrets, $replacement, $line);
    }
}
