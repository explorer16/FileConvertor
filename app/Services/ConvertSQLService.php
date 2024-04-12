<?php

namespace App\Services;

use App\Contracts\ConvertFileInteface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class ConvertSQLService implements ConvertFileInteface
{

    public static function convertFrom(string $filename): array
    {
        // Путь к SQL файлу
        $filePath = storage_path("files/sql/{$filename}");

        // Проверяем существует ли файл
        if (!File::exists($filePath)) {
            throw new \Exception("File {$filename} not found.");
        }

        // Получаем содержимое SQL файла
        $sqlContent = File::get($filePath);

        // Выполняем SQL-запросы
        DB::transaction(function () use ($sqlContent) {
            // Выполняем все запросы из файла SQL
            DB::unprepared($sqlContent);
        });

        // Получаем название таблицы
        preg_match('/CREATE\s+TABLE\s+IF\s+NOT\s+EXISTS\s+`?(\w+)`?/i', $sqlContent, $matches);
        $table_name = $matches[1] ?? null;

        // Получаем строки из таблицы
        $rows = DB::table($table_name)->get();

        // Преобразуем строки в массив
        $data = [];
        foreach ($rows as $row) {
            $data[] = (array) $row;
        }

        // Удаляем таблицу
        DB::statement("DROP TABLE IF EXISTS $table_name");

        return [
            'table_name' => $table_name,
            'data' => $data,
        ];
    }

    public static function convertTo(array $file_data): string
    {
        $table_name = $file_data['table_name'];
        $tableData = $file_data['data'];

        // Если нет данных, просто возвращаем запрос на создание пустой таблицы
        if (empty($tableData)) {
            return "CREATE TABLE IF NOT EXISTS \"table_name\" ();\n";
        }

        // Создаем запрос для создания таблицы
        $sql = "CREATE TABLE IF NOT EXISTS \"$table_name\" (";
        $columns = array_keys($tableData[0]);
        foreach ($columns as $column) {
            $sql .= "\"$column\" VARCHAR(255), ";
        }
        $sql = rtrim($sql, ', ') . ");\n\n";

        // Добавляем запросы для вставки данных
        foreach ($tableData as $row) {
            $sql .= "INSERT INTO \"$table_name\" (";
            $sql .= implode(', ', array_keys($row)) . ") VALUES (";
            $values = [];
            foreach ($row as $value) {
                // Если значение - строка, заключаем его в одинарные кавычки
                if (is_string($value)) {
                    $values[] = "'" . addslashes($value) . "'";
                } else {
                    $values[] = $value; // Иначе оставляем как есть
                }
            }
            $sql .= implode(', ', $values) . ");\n";
        }

        // Записываем SQL в файл
        $directory = storage_path('files/sql');
        if (!file_exists($directory)) {
            mkdir($directory, 0777, true);
        }
        $filename = uniqid($table_name) . '.sql';
        $filePath = $directory . '/' . $filename;
        file_put_contents($filePath, $sql);

        return $filePath;
    }
}
