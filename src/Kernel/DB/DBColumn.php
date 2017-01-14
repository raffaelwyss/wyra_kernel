<?php
/**
 * Description
 *
 * @author   silentx
 * @since    1.0.0
 * @date     21.12.16
 */

namespace Wyra\Kernel\DB;


class DBColumn
{
    public $name = '';
    public $type = 'VARCHAR';
    public $size = '';
    public $null = false;
    public $default = false;
    public $autoincrement = false;
    public $unique = false;
    public $index = false;
    public $primaryKey = false;
    public $foreignKey = false;

    public function getCreateStatement()
    {
        $statement = "`".$this->name."` ";
        $statement .= " ".$this->type;
        if ($this->size != '') {
            $statement .= "(".$this->size.") ";
        }
        $statement .= ($this->null) ? " NULL " : " NOT NULL";
        $statement .= ($this->default !== false) ? " DEFAULT '".$this->default."'" : " ";
        if ($this->type === 'TIMESTAMP') {
            $statement .= " ON UPDATE CURRENT_TIMESTAMP";
        }
        $statement .= ($this->autoincrement) ? " AUTO_INCREMENT" : "";
        return $statement;
    }

}