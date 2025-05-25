<?php

$functions = glob(__DIR__ . '/*.php');
foreach ($functions as $function) {
    if ($function !== __FILE__) {
        require_once $function;
    }
} 
