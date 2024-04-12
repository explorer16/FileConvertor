<?php

// app/Http/Controllers/ConversionController.php

namespace App\Http\Controllers;

use App\Services\ConvertXMLService;
use Illuminate\Http\Request;

class ConversionController extends Controller
{
    /**
     * @throws \Exception
     */
    public function convert(Request $request)
    {

        $array = ConvertXMLService::convertFrom('file.xml');
        dd(ConvertXMLService::convertTo($array));
    }
}
