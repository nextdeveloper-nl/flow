<?php

namespace NextDeveloper\Flow\Helpers;

use Carbon\Carbon;
use NextDeveloper\Commons\Exceptions\NotAllowedException;
use NextDeveloper\Flow\Database\Models\Columns;

/**
 * Checks and normalizes a card field value against its column (leo4#1243). Values are stored
 * as text in flow_item_values.value; this is the one place that decides what is valid.
 *
 *  text         any string
 *  number       numeric, stored as given (comma decimal accepted)
 *  date         any parseable date, stored as Y-m-d
 *  boolean      true/false/1/0/yes/no, stored as "true" or "false"
 *  select       one of the column's options
 *  multi_select JSON list (or comma-separated) of the column's options, stored as a JSON list
 */
class ColumnValues
{
    public static function normalize(Columns $column, mixed $value): ?string
    {
        if (self::isEmpty($value)) {
            return null;
        }

        $label = $column->label ?: $column->name;

        switch ($column->field_type) {
            case 'number':
                $number = is_string($value) ? str_replace(',', '.', trim($value)) : $value;
                if (!is_numeric($number)) {
                    throw new NotAllowedException("\"{$label}\" must be a number.");
                }

                return (string) $number;

            case 'date':
                try {
                    return Carbon::parse((string) $value)->toDateString();
                } catch (\Throwable) {
                    throw new NotAllowedException("\"{$label}\" must be a date.");
                }

            case 'boolean':
                $bool = filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
                if ($bool === null) {
                    throw new NotAllowedException("\"{$label}\" must be yes or no.");
                }

                return $bool ? 'true' : 'false';

            case 'select':
                $choice = trim((string) $value);
                self::assertOptions($column, [$choice], $label);

                return $choice;

            case 'multi_select':
                $choices = self::list($value);
                self::assertOptions($column, $choices, $label);

                return json_encode(array_values(array_unique($choices)), JSON_UNESCAPED_UNICODE);

            default:
                return is_scalar($value) ? (string) $value : json_encode($value, JSON_UNESCAPED_UNICODE);
        }
    }

    public static function isEmpty(mixed $value): bool
    {
        return $value === null
            || (is_string($value) && trim($value) === '')
            || (is_array($value) && count($value) === 0)
            || $value === '[]';
    }

    /**
     * @return array<int, string>
     */
    private static function list(mixed $value): array
    {
        if (is_string($value)) {
            $decoded = json_decode($value, true);
            $value = is_array($decoded) ? $decoded : explode(',', $value);
        }

        return array_values(array_filter(array_map(fn ($v) => trim((string) $v), (array) $value), fn ($v) => $v !== ''));
    }

    /**
     * @param  array<int, string>  $choices
     */
    private static function assertOptions(Columns $column, array $choices, string $label): void
    {
        $options = array_map('strval', (array) ($column->options ?? []));

        // A select without options accepts anything, so old columns keep working.
        if ($options === []) {
            return;
        }

        foreach ($choices as $choice) {
            if (!in_array($choice, $options, true)) {
                throw new NotAllowedException("\"{$choice}\" is not an option of \"{$label}\".");
            }
        }
    }
}
