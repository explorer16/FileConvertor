<?php

namespace App\Services;

use App\Contracts\ConvertFileInteface;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;
use SimpleXMLElement;

class ConvertXMLService implements ConvertFileInteface
{
    public static function convertFrom(string $filename): array
    {
        // Путь к XML файлу
        $filePath = storage_path("files/xml/{$filename}");

        // Проверяем существует ли файл
        if (!File::exists($filePath)) {
            throw new \Exception("File {$filename} not found.");
        }

        // Чтение XML файла
        $xmlString = File::get($filePath);

        // Преобразование XML строки в объект SimpleXMLElement
        $xml = simplexml_load_string($xmlString);

        // Проверка на успешное чтение XML
        if ($xml === false) {
            throw new \Exception("Failed to parse XML file.");
        }

        // Получение названия таблицы
        $tableName = $xml->getName();

        // Получение атрибутов и данных строк
        $attributes = [];
        $rows = [];

        foreach ($xml->children() as $employee) {
            $rowData = [];
            foreach ($employee->children() as $child) {
                // Добавляем дочерние элементы в данные строки
                $rowData[$child->getName()] = (string) $child;
            }
            $rows[] = $rowData;
        }

        return [
            'table_name' => $tableName,
            'data' => $rows,
        ];
    }

    public static function convertTo(array $file_data): string
    {
        // Генерируем уникальное имя файла
        $filename = uniqid($file_data['table_name']) . '.xml';

        // Создаем новый экземпляр SimpleXMLElement
        $xml = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><' . $file_data['table_name'] . '></' . $file_data['table_name'] . '>');

        // Добавляем данные из итогового массива в XML
        foreach ($file_data['data'] as $row) {
            $employee = $xml->addChild('employee');
            foreach ($row as $key => $value) {
                $employee->addChild($key, htmlspecialchars($value));
            }
        }

        // Сохраняем XML в файл в папке storage/files/xml
        $filePath = storage_path("files/xml/{$filename}");

        $dom = dom_import_simplexml($xml)->ownerDocument;
        $dom->formatOutput = true;
        $dom->save($filePath);

        // Возвращаем путь к сохраненному файлу
        return $filePath;
    }
}
