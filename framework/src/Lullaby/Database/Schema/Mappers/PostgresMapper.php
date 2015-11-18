<?php

namespace Lullaby\Database\Schema\Mappers;

class PostgresMapper
{
    /**
     * @param string $columnName
     * @param array $attributes
     *
     * @return string
     */
    public function column($columnName, array $attributes)
    {
        $result = "";

        switch ($attributes['type']) {
            case 'smallint':
                $result = "\$table->smallInteger('{$columnName}')";
                break;
            case 'integer':
                $result = "\$table->integer('{$columnName}')";
                break;
            case 'bigint':
                $result = "\$table->bigInteger('{$columnName}')";
                break;
            case 'decimal':
                $result = "\$table->decimal('{$columnName}')";
                break;
            case 'numeric':
                break;
            case 'double precision':
                $result = "\$table->double('{$columnName}')";
//                $result = "\$table->float('{$columnName}')";
                break;
            case 'smallserial':
            case 'serial':
                $result = "\$table->increments('{$columnName}')";
                break;
            case 'bigserial':
                $result = "\$table->bigIncrements('{$columnName}')";
                break;
            case 'char':
                $result = "\$table->char('{$columnName}')";
                if (isset($attributes['size'])) {
                    $result = "\$table->char('{$columnName}', {$attributes['size']})";
                }
                break;
            case 'character varying':
            case 'varchar':
                $result = "\$table->string('{$columnName}')";
            if (isset($attributes['size'])) {
                    $result = "\$table->string('{$columnName}', {$attributes['size']})";
                }
                break;
            case 'text':
                $result = "\$table->mediumText('{$columnName}')";
//                $result = "\$table->longText('{$columnName}')";
                break;
            case 'timestamp':
                $result = "\$table->timestamp('{$columnName}')";
//                $result = "\$table->timestamp()";
                break;
            case 'time':
                $result = "\$table->time('{$columnName}')";
                break;
            case 'date':
                $result = "\$table->date('{$columnName}')";
//                $result = "\$table->dateTime('{$columnName}')";
                break;
            case 'boolean':
                $result = "\$table->boolean('{$columnName}')";
                break;
            case 'bytea':
                $result = "\$table->binary('{$columnName}')";
                break;
            default:
                // Laravel独自の場合
                $result = "\$table->{$attributes['type']}()";
                if (!empty($columnName)) {
                    $result = "\$table->{$attributes['type']}({$columnName})";
                }
        }

        // デフォルト値
        if (isset($attributes['default'])) {
            $result .= "->default({$attributes['default']})";
        }

        // 制約
        if (!empty($attributes['constraint'])) {
            // 小文字変換
            $string = strtolower($attributes['constraint']);
            $result .= "->{$string}()";
//            $result .= "->{$string}({$attributes['constraint']})";
        }

        // Not NULL許可
        if (empty($attributes['notnull'])) {
            $result .= "->nullable()";
        }

        return empty($result) ? $result : $result . ";";
    }
}