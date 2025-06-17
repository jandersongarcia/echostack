<?php

function out($label, $message, $color = 'default')
{
    $colors = [
        'green' => "\033[32m",
        'yellow' => "\033[33m",
        'red' => "\033[31m",
        'default' => "\033[36m"
    ];
    $reset = "\033[0m";
    echo "{$colors[$color]}[$label]$reset $message\n";
}