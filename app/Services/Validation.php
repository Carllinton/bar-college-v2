<?php
declare(strict_types=1);
namespace BarCollege\Services;
use InvalidArgumentException;
final class Validation
{
    public static function text(
        mixed $value,
        string $label,
        int $max,
        bool $required = false,
    ): string {
        if (!is_string($value)) {
            throw new InvalidArgumentException("$label must be text.");
        }
        $value = trim($value);
        if ($required && $value === "") {
            throw new InvalidArgumentException("$label is required.");
        }
        if (mb_strlen($value) > $max) {
            throw new InvalidArgumentException(
                "$label allows at most $max characters in the original database.",
            );
        }
        // latin1 tables cannot faithfully store emoji / most non-Latin scripts. Never silently replace them.
        if (preg_match('/[^\x{0020}-\x{007E}\x{00A0}-\x{00FF}\r\n\t]/u', $value)) {
            throw new InvalidArgumentException(
                "$label contains characters the legacy database cannot store. Use Latin characters.",
            );
        }
        return $value;
    }
    public static function fields(array $meta, array $input, bool $editing): array
    {
        $values = [];
        foreach ($meta["fields"] as $name => $field) {
            [$label, $max] = $field;
            $type = $field[3] ?? "text";
            $raw = $input[$name] ?? "";
            if ($type === "password" && $editing && $raw === "") {
                continue;
            }
            $value = self::text($raw, $label, $max, (bool) ($field[2] ?? false));
            if (
                in_array($type, ["number", "units", "account", "section", "subject", "bool"], true)
            ) {
                if (!preg_match('/^\d+$/D', $value)) {
                    throw new InvalidArgumentException("$label must be a whole number.");
                }
                $number = (int) $value;
                if (
                    $number > 2147483647 ||
                    ($type !== "bool" && $type !== "number" && $number < 1)
                ) {
                    throw new InvalidArgumentException("$label is outside the supported range.");
                }
                if ($type === "units" && $number > 12) {
                    throw new InvalidArgumentException("Units must be between 1 and 12.");
                }
                if ($type === "bool" && $number > 1) {
                    throw new InvalidArgumentException("Choose Yes or No.");
                }
            }
            if ($type === "role" && !in_array($value, ["admin", "student"], true)) {
                throw new InvalidArgumentException("Choose a valid role.");
            }
            if ($type === "password" && strlen($value) < 8) {
                throw new InvalidArgumentException(
                    "New passwords must contain 8 to 11 characters.",
                );
            }
            if ($type === "time" && !preg_match('/^(?:[01]\d|2[0-3]):[0-5]\d$/D', $value)) {
                throw new InvalidArgumentException("$label must be a valid time.");
            }
            if ($type === "date" && $value !== "") {
                $date = \DateTimeImmutable::createFromFormat("!Y-m-d", $value);
                if (!$date || $date->format("Y-m-d") !== $value || $value > date("Y-m-d")) {
                    throw new InvalidArgumentException("Choose a valid birth date in the past.");
                }
            }
            $values[$name] = $value;
        }
        return $values;
    }
}
