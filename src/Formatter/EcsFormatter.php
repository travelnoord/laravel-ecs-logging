<?php

namespace Travelnoord\Logging\Formatter;

use Hamidrezaniazi\Pecs\EcsFieldsCollection;
use Hamidrezaniazi\Pecs\Fields\AbstractEcsField;
use Hamidrezaniazi\Pecs\Fields\Base;
use Hamidrezaniazi\Pecs\LogRecord as PecsLogRecord;
use Hamidrezaniazi\Pecs\Monolog\EcsFormatter as PecsFormatter;
use Hamidrezaniazi\Pecs\Properties\PairList;
use Hamidrezaniazi\Pecs\Properties\ValueList;
use Illuminate\Support\Collection;
use Monolog\LogRecord;
use Travelnoord\Logging\ContextManager;
use Travelnoord\Logging\Fields\Label;
use Travelnoord\Logging\Fields\Tag;

class EcsFormatter extends PecsFormatter
{
    protected function prepare(LogRecord $record): EcsFieldsCollection
    {
        $pecsLogRecord = PecsLogRecord::parse($record->toArray());

        $fields = $this->mergeBase(
            $this->fetchContextFields($record),
        );

        return (new EcsFieldsCollection($fields->values()->all()))
            ->loadInitialFields($pecsLogRecord)
            ->loadWrappers();
    }

    /**
     * @param  Collection<int|string, scalar|AbstractEcsField>  $fields
     * @return Collection<int|string, AbstractEcsField>
     */
    protected function mergeBase(Collection $fields): Collection
    {
        $fields = $this->mapLabelsAndTagsToBase(
            $this->mapScalarTagsAndLabels($fields),
        );

        $labels = $this->pluckLabelsFormBase($fields);
        $tags = $this->pluckTagsFromBase($fields);

        /** @var Collection<int|string, AbstractEcsField> $fields */
        $fields = $fields->filter(static fn(AbstractEcsField $field) => ! $field instanceof Base);

        if ($labels->isEmpty() && $tags->isEmpty()) {
            return $fields;
        }

        $pairList = new PairList();
        $valueList = new ValueList();

        foreach ($labels as $key => $value) {
            $pairList->put($key, $value);
        }

        foreach ($tags as $value) {
            $valueList->push($value);
        }

        return $fields->push(new Base(
            labels: $pairList,
            tags: $valueList,
        ));
    }

    /**
     * @param  Collection<int|string, scalar|AbstractEcsField>  $context
     * @return Collection<int|string, AbstractEcsField>
     */
    protected function mapScalarTagsAndLabels(Collection $context): Collection
    {
        return $context->map(static function ($value, string|int $key) {
            if (! is_scalar($value)) {
                return $value;
            }

            $value = is_bool($value) ? (int)$value : $value;

            if (is_int($key)) {
                return new Base(tags: (new ValueList())->push($value));
            }

            return new Base(labels: (new PairList())->put(Label::normalizeKey($key), $value));
        });
    }

    /**
     * @param  Collection<int|string, AbstractEcsField>  $context
     * @return Collection<int|string, AbstractEcsField>
     */
    protected function mapLabelsAndTagsToBase(Collection $context): Collection
    {
        return $context->map(static function (AbstractEcsField $field) {
            if ($field instanceof Label) {
                return new Base(labels: (new PairList())->put($field->key, $field->value));
            }

            if ($field instanceof Tag) {
                return new Base(tags: (new ValueList())->push($field->value));
            }

            return $field;
        });
    }

    /**
     * @param  Collection<int|string, AbstractEcsField>  $context
     * @return Collection<string, string|int|float>
     */
    protected function pluckLabelsFormBase(Collection $context): Collection
    {
        /** @var Collection<string, string|int|float> $labels */
        $labels = $context->whereInstanceOf(Base::class)
            ->pluck('labels')
            ->filter()
            /** @phpstan-ignore-next-line */
            ->flatMap(static fn(PairList $list) => $list->toArray());

        return $labels;
    }

    /**
     * @param  Collection<int|string, AbstractEcsField>  $context
     * @return Collection<int, string|int|float>
     */
    protected function pluckTagsFromBase(Collection $context): Collection
    {
        /** @var Collection<int, string|int|float> $tags */
        $tags = $context->whereInstanceOf(Base::class)
            ->pluck('tags')
            ->filter()
            /** @phpstan-ignore-next-line */
            ->flatMap(static fn(ValueList $list) => $list->toArray())
            ->unique();

        return $tags;
    }

    /**
     * @return Collection<int|string, scalar|AbstractEcsField>
     */
    protected function fetchContextFields(LogRecord $record): Collection
    {
        /** @var Collection<int|string, scalar|AbstractEcsField> $context */
        $context = (new Collection($record->context))
            ->merge($record->extra)
            ->flatMap(static function ($value, string|int $key) {
                if ($key === ContextManager::ContextKey && is_array($value)) {
                    return $value;
                }

                return [$key => $value];
            });

        return $context;
    }
}
