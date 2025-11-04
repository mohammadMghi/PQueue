<?php

function parseCommandArgs(array $argv): array {
    array_shift($argv);  
    $args = [];

    foreach ($argv as $arg) { 
        if (preg_match('/^--([^=]+)(=(.*))?$/', $arg, $matches)) {
            $key = $matches[1];
            $value = $matches[3] ?? true; 
            $args[$key] = $value;
        }
     
        elseif (preg_match('/^-([a-zA-Z])$/', $arg, $matches)) {
            $key = $matches[1];
            $value = true; 
            $args[$key] = $value;
        }
    }

    return $args;
}