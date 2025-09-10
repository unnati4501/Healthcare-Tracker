<?php

namespace App\Reader;

use PhpOffice\PhpSpreadsheet\Reader\IReadFilter;

class ExcelSheetReader implements IReadFilter
{
    public function readCell($column, $row, $worksheetName = '')
    {
        $skeepCell = [];
        $cellNumber = $column . $row;

        if (in_array(strtolower($cellNumber), $skeepCell)) {
            return false;
        }
        return true;
    }
}
