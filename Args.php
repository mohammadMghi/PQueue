<?php
function parseCommandArgs(array $argv): array {
    array_shift($argv); // remove script name
    $args = [];

    foreach ($argv as $arg) {
        // Match long options like --key or --key=value
        if (preg_match('/^--([^=]+)(?:=(.*))?$/', $arg, $matches)) {
            $key = $matches[1];
            $value = array_key_exists(2, $matches) && $matches[2] !== '' ? $matches[2] : true;
            $args[$key] = normalizeValue($value);
        }

        // Match short flags like -a
        elseif (preg_match('/^-([a-zA-Z])$/', $arg, $matches)) {
            $args[$matches[1]] = true;
        }
    }

    return $args;
}

/**
 * Convert string values like "true", "false", "0", "1", numbers, etc.
 * into native PHP types.
 */
function normalizeValue($value) {
    if (is_bool($value)) return $value;

    $lower = strtolower(trim($value));

    if ($lower === 'true' || $lower === 'yes' || $lower === 'on') return true;
    if ($lower === 'false' || $lower === 'no' || $lower === 'off') return false;
    if (is_numeric($value)) return $value + 0; // cast to int or float

    return $value;
}