<?php

namespace Lullaby\Database\Migrations;

use Lullaby\Database\Schema\Mappers\PostgresMapper;
use PHPExcel_IOFactory;

class MigrationDefinition
{
    // model name.
    const MODEL_NAME = 'A2';

    // start lines.
    const START_LINE_FIELD           = 'A6';
    const START_LINE_INDEX           = 'B6';
    const START_LINE_FOREIGN         = 'C6';
    const START_LINE_FOREIGN_PRIMARY = 'D6';

    // field column.
    const COLUMN_NAME      = 'B'; // column name.
    const COLUMN_DATA_TYPE = 'C'; // data type.
    const COLUMN_SIZE      = 'D'; // size.
    const COLUMN_DEFAULT   = 'E'; // default.
    const COLUMN_NOT_NULL  = 'F'; // not null.
    const COLUMN_UNSIGNED  = 'G'; // unsigned.

    // index column.
    const INDEX_NAME        = 'A';
    const INDEX_COLUMN_LIST = 'B';
    const INDEX_PRIMARY_KEY = 'C';
    const INDEX_UNIQUE      = 'D';

    // foreign key column.
    const FOREIGN_KEY_NAME                   = 'A';
    const FOREIGN_KEY_COLUMN_LIST            = 'B';
    const FOREIGN_KEY_REFERENCES_TABLE       = 'C';
    const FOREIGN_KEY_REFERENCES_COLUMN_LIST = 'D';

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
        $sheet   = $this->getActiveSheet($index);

        $column  = $this->createColumn($sheet,  self::START_LINE_FIELD);
        $index   = $this->createIndex($sheet,   self::START_LINE_INDEX);
        $foreign = $this->createForeign($sheet, self::START_LINE_FOREIGN);

        $contents   = [];
        $contents[] = $column;
        if (!empty($index))   $contents[] = $index;
        if (!empty($foreign)) $contents[] = $foreign;

        return implode($this->space, $contents);
    }

    /**
     * Create columns.
     *
     * @param object $sheet
     * @param string $cell
     * @param int    $line
     * @param string $columns
     *
     * @return array|string
     */
    protected function createColumn($sheet, $cell, $line = null, $columns = "")
    {
        if (empty($columns)) {
            $line = $sheet->getCell($cell)->getValue();
        }

        $type  = $sheet->getCell(self::COLUMN_DATA_TYPE . $line)->getValue();

        // if type column data empty, program will end.
        if (empty($type)) {
            return $columns;
        } else {
            if (!empty($columns)) $columns .= $this->space;
        }

        $name     = $sheet->getCell(self::COLUMN_NAME     . $line)->getValue();
        $size     = $sheet->getCell(self::COLUMN_SIZE     . $line)->getValue();
        $default  = $sheet->getCell(self::COLUMN_DEFAULT  . $line)->getValue();
        $notNull  = $sheet->getCell(self::COLUMN_NOT_NULL . $line)->getValue();

        $mapper = new PostgresMapper();

        $attributes = [
            'type'       => $type,
            'size'       => $size,
            'default'    => $default,
            'notnull'    => $notNull,
        ];

        $columns .= $mapper->column($name, $attributes);

        // counts up the line number.
        $line++;

        return $this->createColumn($sheet, $cell, $line, $columns);
    }

    /**
     * Create index.
     *
     * @param object $sheet
     * @param string $cell
     * @param int    $line
     * @param string $index
     *
     * @return array|string
     */
    protected function createIndex($sheet, $cell, $line = null, $index = "")
    {
        if (empty($index)) {
            $line = $sheet->getCell($cell)->getValue();
        }

        $name = $sheet->getCell(self::INDEX_NAME . $line)->getValue();

        // if name column data empty, program will end.
        if (empty($name)) {
            return $index;
        } else {
            if (!empty($index)) $index .= $this->space;
        }

        $columnList = $sheet->getCell(self::INDEX_COLUMN_LIST . $line)->getValue();
        $isPrimary  = $sheet->getCell(self::INDEX_PRIMARY_KEY . $line)->getValue();
        $isUnique   = $sheet->getCell(self::INDEX_UNIQUE      . $line)->getValue();

        $mapper = new PostgresMapper();

        $attributes = [
            'columns' => $columnList,
            'primary' => $isPrimary,
            'unique'  => $isUnique,
        ];

        $index .= $mapper->index($attributes);

        // counts up the line number.
        $line++;

        return $this->createIndex($sheet, $cell, $line, $index);
    }

    /**
     * Create foreign.
     *
     * @param object $sheet
     * @param string $cell
     * @param int    $line
     * @param string $foreign
     *
     * @return array|string
     */
    protected function createForeign($sheet, $cell, $line = null, $foreign = "")
    {
        if (empty($foreign)) {
            $line = $sheet->getCell($cell)->getValue();
        }

        $name = $sheet->getCell(self::FOREIGN_KEY_NAME . $line)->getValue();

        // if type column data empty, program will end.
        if (empty($name)) {
            return $foreign;
        } else {
            if (!empty($foreign)) $foreign .= $this->space;
        }

        $columns    = $sheet->getCell(self::FOREIGN_KEY_COLUMN_LIST            . $line)->getValue();
        $on         = $sheet->getCell(self::FOREIGN_KEY_REFERENCES_TABLE       . $line)->getValue();
        $references = $sheet->getCell(self::FOREIGN_KEY_REFERENCES_COLUMN_LIST . $line)->getValue();

        $mapper = new PostgresMapper();

        $attributes = [
            'columns'    => $columns,
            'on'         => $on,
            'references' => $references,
        ];

        $foreign .= $mapper->foreign($attributes);

        // counts up the line number.
        $line++;

        return $this->createForeign($sheet, $cell, $line, $foreign);
    }
}
