<?php

namespace BinarySearch;

class ConsoleLog extends \Psr\Log\AbstractLogger
{
    public function log($level, $message, array $context = [])
    {
        print '[' . $level . '] ' . $message . "\n";
    }
}
