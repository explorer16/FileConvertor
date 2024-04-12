<?php

namespace App\Services;

use App\Contracts\ConvertFileInteface;
use Illuminate\Support\Facades\File;

class ConvertCSVService implements ConvertFileInteface
{

    public static function convertFrom(string $filename): array
    {
        // Путь к CSV файлу
        $filePath = storage_path("files/csv/{$filename}");

        // Проверяем существует ли файл
        if (!File::exists($filePath)) {
            throw new \Exception("File {$filename} not found.");
        }

        // Получаем содержимое CSV файла
        $csvContent = File::get($filePath);

        // Разбиваем содержимое на строки
        $lines = explode("\n", $csvContent);

        // Удаляем пустые строки
        $lines = array_filter($lines);

        // Извлекаем атрибуты (первая строка)
        $attributes = str_getcsv(array_shift($lines));

        // Массив для данных
        $data = [];

        // Проходим по каждой строке данных
        foreach ($lines as $line) {
            $rowData = str_getcsv($line);
            $rowData = array_combine($attributes, $rowData);
            $data[] = $rowData;
        }


        $filename = explode('.', $filename)[0];

        // Возвращаем массив с атрибутами и данными
        return [
            'table_name' => $filename,
            'data' => $data
        ];
    }

    public static function convertTo(array $file_data): string
    {
        $file_name = $file_data['table_name'];
        $attributes = array_keys($file_data['data'][0]);
        $csvData = implode(';', $attributes) . "\n"; // Создаем строку с заголовками

        foreach ($file_data['data'] as $row) {
            $csvData .= implode(';', $row) . "\n"; // Добавляем данные в последующие строки
        }

        // Сохраняем в файл
        $file_name = uniqid($file_name) . '.csv';
        $directory = storage_path("files/csv");
        $file_path = $directory . '/' . $file_name;
        file_put_contents($file_path, $csvData);

        return $file_path;
    }
}
