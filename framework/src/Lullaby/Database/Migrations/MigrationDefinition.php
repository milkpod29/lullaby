<?php

namespace Lullaby\Database\Migrations;

use Lullaby\Database\Schema\Mappers\PostgresMapper;
use PHPExcel_IOFactory;

class MigrationDefinition
{
    // model name.
    const MODEL_NAME = 'A2';

    const COLUMN_NAME        = 'B'; // column name.
    const COLUMN_DATA_TYPE   = 'C'; // data type.
    const COLUMN_SIZE        = 'D'; // size.
    const COLUMN_DEFAULT     = 'E'; // default.
    const COLUMN_NOT_NULL    = 'F'; // not null.
//    const COLUMN_CONSTRAINT  = 'G'; // unsigned.
    const COLUMN_CONSTRAINT  = 'H'; // constraint.

    // column data start line.
    const START_LINE = 4;

    // space.
    private $space = "\r\n            ";

    /**
     * The PHPExcel instance.
     *
     * @var PHPExcel_IOFactory
     */
    protected $phpExcel;

    /**
     * MigrationDefinition constructor.
     *
     * @param string $filepath
     */
    public function __construct($filepath)
    {
        $this->phpExcel = PHPExcel_IOFactory::load($filepath);
    }

    /**
     *
     * @return int
     */
    public function count()
    {
        return $this->phpExcel->getSheetCount();
    }

    /**
     *
     * @param $index
     *
     * @return \PHPExcel_Worksheet
     * @throws \PHPExcel_Exception
     */
    public function getActiveSheet($index)
    {
        $this->phpExcel->setActiveSheetIndex($index);
        return $this->phpExcel->getActiveSheet();
    }

    /**
     *
     * @param $index
     * @param $cell
     *
     * @return mixed
     * @throws \PHPExcel_Exception
     */
    public function getCellValue($index, $cell)
    {
        $sheet =  $this->getActiveSheet($index);
        return $sheet->getCell($cell)->getValue();
    }

    /**
     *
     * @param $index
     *
     * @return array|string
     */
    public function getContent($index)
    {
        $sheet = $this->getActiveSheet($index);
        return $this->createColumns($sheet, self::START_LINE);
    }

    /**
     * Create columns.
     *
     * @param object $sheet
     * @param int    $line
     * @param string $columns
     *
     * @return array|string
     */
    protected function createColumns($sheet, $line, $columns = "")
    {
        $type  = $sheet->getCell(self::COLUMN_DATA_TYPE . $line)->getValue();

        // if type column data empty, program will end.
        if (empty($type)) {
            return $columns;
        } else {
            if (!empty($columns)) $columns .= $this->space;
        }

        $name       = $sheet->getCell(self::COLUMN_NAME       . $line)->getValue();
        $size       = $sheet->getCell(self::COLUMN_SIZE       . $line)->getValue();
        $default    = $sheet->getCell(self::COLUMN_DEFAULT    . $line)->getValue();
        $constraint = $sheet->getCell(self::COLUMN_CONSTRAINT . $line)->getValue();
        $notNull    = $sheet->getCell(self::COLUMN_NOT_NULL   . $line)->getValue();

        $mapper = new PostgresMapper();

        $attributes = [
            'type'       => $type,
            'size'       => $size,
            'default'    => $default,
            'constraint' => $constraint,
            'notnull'    => $notNull,
        ];

        $columns .= $mapper->column($name, $attributes);

        // counts up the line number.
        $line++;

        return $this->createColumns($sheet, $line, $columns);
    }
}
