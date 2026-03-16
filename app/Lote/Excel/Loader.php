<?php

namespace App\Lote\Excel;

use PhpOffice\PhpSpreadsheet\IOFactory;

class Loader
{
    public static function load($path): \PhpOffice\PhpSpreadsheet\Spreadsheet
    {
        $reader = IOFactory::createReaderForFile($path);
        $reader->setReadDataOnly(false);

        return $reader->load($path);
    }
}
