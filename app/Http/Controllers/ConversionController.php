<?php

namespace App\Http\Controllers;

use App\Services\ConvertServiceFactory;
use Illuminate\Http\Request;

class ConversionController extends Controller
{
    /**
     * @throws \Exception
     */
    public function convert(Request $request): \Symfony\Component\HttpFoundation\BinaryFileResponse|\Illuminate\Http\JsonResponse
    {
        // Проверяем наличие файла в запросе
        if (!$request->hasFile('file')) {
            return response()->json(['error' => 'No file uploaded'], 400);
        }

        // Получаем файл из запроса
        $file = $request->file('file');
        // Получаем расширение файла
        $extension = $file->getClientOriginalExtension();

        // Генерируем уникальное имя для файла
        $filename = uniqid() . '.' . $extension;

        // Сохраняем файл в папку storage/files с подпапкой по расширению
        $extension = $file->getClientOriginalExtension();
        $filename = uniqid() . '.' . $extension;
        $path = '/var/www/storage/files/' . $extension;
        $file->move($path, $filename);

        // Получаем данные о типе конвертации
        $from = $request->input('from');
        $to = $request->input('to');

        // Выбираем сервис для извлечения данных из файла
        $extractService = ConvertServiceFactory::create($from);

        // Получаем массив ключевых данных из файла (передаем только имя файла без пути)
        $data = $extractService->convertFrom($filename);

        // Выбираем сервис для конвертации файла
        $convertService = ConvertServiceFactory::create($to);

        // Формируем новый файл с необходимым расширением
        $newFilePath = $convertService->convertTo($data);

        // Возвращаем итоговый файл для скачивания
        return response()->download($newFilePath);
    }
}
