<?php

namespace App\Lote\Excel;

use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

class Finder
{
    public static function searchByLabel($sheet, $needle, $direction = 'next_col'): ?string
    {

        $highestRow = $sheet->getHighestDataRow();
        $highestColIndex = Coordinate::columnIndexFromString(
            $sheet->getHighestDataColumn()
        );

        $found = null;

        for ($row = 1; $row <= $highestRow; $row++) {
            for ($col = 1; $col <= $highestColIndex; $col++) {
                $address = Coordinate::stringFromColumnIndex($col).$row; // напр. J3
                $cell = $sheet->getCell($address);

                $value = trim((string) $cell->getValue());
                if ($value === $needle) {
                    $found = [$col, $row];
                    break 2;
                }
            }
        }

        if ($found) {
            [$col, $row] = $found;

            $valueAddress = '';
            if ($direction === 'next_col') {
                $valueAddress = Coordinate::stringFromColumnIndex($col + 1).$row;
            }
            if ($direction === 'next_row') {
                $valueAddress = Coordinate::stringFromColumnIndex($col).($row + 1);
            }

            return $sheet->getCell($valueAddress)->getValue();
        }

        return null;

    }
}
