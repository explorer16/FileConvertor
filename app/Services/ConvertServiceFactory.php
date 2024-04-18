<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class ConvertServiceFactory
{
    public static function create($format)
    {
        // Преобразуем первую букву к верхнему регистру
        $formattedFormat = strtoupper($format);

        // Формируем имя класса с учетом преобразованного формата
        $className = "App\Services\Convert{$formattedFormat}Service";

        // Проверяем существование класса для указанного формата
        if (class_exists($className)) {
            // Создаем экземпляр сервиса и возвращаем его
            return new $className();
        } else {
            // Если класс не найден, можно вернуть ошибку или другой дефолтный сервис
            throw new \Exception("Unsupported format: $format");
        }
    }

}
