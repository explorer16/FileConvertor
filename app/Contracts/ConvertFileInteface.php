<?php

namespace App\Contracts;

interface ConvertFileInteface
{
    public static function convertFrom(string $filename);
    public static function convertTo(array $file_data);
}
