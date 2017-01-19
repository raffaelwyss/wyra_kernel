<?php

namespace Wyra\Kernel\DB;


use Wyra\Kernel\Kernel;
use Wyra\Kernel\MVC\Model;

class DBTable
{
    public $name = '';
    public $engine = 'InnoDB';
    public $columns = [];
    public $primarykey = '';
    public $unique = [];
    public $index = [];
    public $trans = false;
    private $defaultColums = ['dentrydate', 'dtimestamp', 'ncreator', 'neditor'];

    public function setName($name)
    {
        $this->name = Kernel::$config->get('db.prefix').$name;
    }

    public function setPrimaryKey($columnnames)
    {
        $this->primarykey = $columnnames;
    }

    public function addUnique($name, $columnnames)
    {
        if (!isset($this->unique[$name])) {
            $this->unique[$name] = $columnnames;
        }
    }

    public function addIndex($name, $columnnames)
    {
        if (!isset($this->index[$name])) {
            $this->index[$name] = $columnnames;
        }
    }

    public function addColumn($name, $properties = [])
    {
        $dbcolumn = new DBColumn();
        $dbcolumn->name = $name;
        if (isset($properties['type'])) {
            $dbcolumn->type = $properties['type'];
        }
        if (isset($properties['size'])) {
            $dbcolumn->size = (integer) $properties['size'];
        }
        if (isset($properties['null'])) {
            $dbcolumn->null = $properties['null'];
        }
        if (isset($properties['default'])) {
            $dbcolumn->default = $properties['default'];
        }
        if (isset($properties['autoincrement'])) {
            $dbcolumn->autoincrement = $properties['autoincrement'];
        }
        if (isset($properties['unique'])) {
            $dbcolumn->unique = $properties['unique'];
        }
        if (isset($properties['index'])) {
            $dbcolumn->index = $properties['index'];
        }
        if (isset($properties['primarykey'])) {
            $dbcolumn->primaryKey = $properties['primarykey'];
        }
        if (isset($properties['foreignkey'])) {
            $dbcolumn->foreignKey = $properties['foreignkey'];
        }
        $this->columns[$name] = $dbcolumn;
    }

    /**
     * @param bool $returnMode 0 = All, 1 = Only Names in an Array (not default)
     *
     * @return array
     */
    public function getColumns($returnMode = 0)
    {
        if ($returnMode === 1) {
            $columns = array();
            /** @var DBColumn $column */
            foreach ($this->columns AS $column) {
                if (!in_array($column->name, $this->defaultColums)) {
                    $columns[] = $column->name;
                }
            }
            return $columns;
        } else {
            return $this->columns;
        }

    }

    public function getCreateStatement($whitoutDetails = true)
    {
        $statement = "CREATE TABLE IF NOT EXISTS 
                          `".$this->name."` (";
        $statementcolumns = array();
        $uniqueColumns = array();
        $indexColumns = array();
        $primaryKey = array();
        /** @var DBColumn $column */
        foreach ($this->columns as $column) {
            $statementcolumns[] = $column->getCreateStatement();
            if ($column->unique) {
                $uniqueColumns[$column->name] = $column->name;
            }
            if ($column->index) {
                $indexColumns[$column->name] = $column->name;
            }
            if ($column->primaryKey) {
                $primaryKey[] = $column->name;
            }
        }
        $statement .= implode(',', $statementcolumns);

        // Primary Key
        if ($this->primarykey === '') {
            $this->primarykey = implode(',', $primaryKey);
        }
        $statement .= ", PRIMARY KEY ($this->primarykey)";

        if (!$whitoutDetails) {
            // Unique
            $uniqueArr = [];
            foreach ($uniqueColumns as $uniquekey => $unique) {
                $uniqueArr[] = " UNIQUE INDEX `U_".$uniquekey."` (".$unique.") ";
            }
            if (count($uniqueArr) > 0) {
                $statement .= ', '.implode(",", $uniqueArr);
            }

            // Index
            $indexArr = [];
            foreach ($indexColumns as $indexkey => $index) {
                $indexArr[] = " INDEX `I_".$indexkey."` (".$index.") ";
            }
            if (count($indexArr) > 0) {
                $statement .= ', '.implode(",", $indexArr);
            }
        }

        $statement .= ") ENGINE = ".$this->engine;
        return $statement;

    }

    public function updateColumnIndexes()
    {
        /** @var DBColumn $column */
        foreach ($this->columns as $column) {
            if ($column->index) {
                $this->updateIndex('I', $column->name);
            }

            if ($column->unique) {
                $this->updateIndex('U', $column->name);
            }
        }
    }

    public function updateIndex($type = 'I', $columnName = '')
    {
        $key = $type.'_'.$columnName;
        $sql = "SHOW INDEX
                FROM ".$this->name."
                WHERE Key_name = :keyname ";
        $dbobj = Kernel::$db->query($sql, ['keyname' => $key]);
        $result = $dbobj->fetchAll();

        // Delete the Key
        if (!empty($result)) {
            foreach ($result as $key => $value) {
                $sql = "ALTER TABLE ".$this->name."
                        DROP INDEX `".$value['Key_name']."`";
                Kernel::$db->query($sql);
            }
        }

        // Unique oder Index setzen
        if ($type === 'U') {
            $sql = "ALTER TABLE ".$this->name."
                    ADD CONSTRAINT `".$key."` UNIQUE (`".$columnName."`) ";
            Kernel::$db->query($sql);
        } elseif ($type === 'I') {
            $sql = "ALTER TABLE ".$this->name."
                ADD INDEX `".$key."` (`".$columnName."`) ";
            Kernel::$db->query($sql);
        }

    }

    public function updateForeignKeys()
    {

        /** @var DBColumn $column */
        foreach ($this->columns as $column) {
            if ($column->foreignKey !== false) {
                if (!isset($column->foreignKey['column'])) {
                    $foreignkeyname = $column->name;
                } else {
                    $foreignkeyname = $column->foreignKey['column'];
                }
                $plugin = $column->foreignKey['plugin'];
                $modelname = $column->foreignKey['model'];
                $modelnamespace = "Wyra\\Plugins\\".$plugin."\\Model\\".$modelname;
                /** @var Model $model */
                $model = new $modelnamespace;

                // Find Foreign-Key
                $foreignkeyid = 'F_'.$this->name."_".$foreignkeyname;
                $sql = "SELECT concat(table_name, '.', column_name) as 'foreign key',  
                              concat(referenced_table_name, '.', referenced_column_name) as 'references',
                              constraint_name, x.*
                        FROM information_schema.key_column_usage AS x
                        WHERE referenced_table_name is not null
                         AND table_schema = '".Kernel::$config->get('db.database')."' 
                         AND table_name = '".$this->name."' 
                         AND constraint_name = '".$foreignkeyid."'";
                $dbobj = Kernel::$db->query($sql);
                $result = $dbobj->fetchAll();

                // Delete the Foreign-Key
                if (!empty($result)) {
                    foreach ($result as $key => $value) {
                        $sql = "ALTER TABLE ".$this->name." 
                                DROP FOREIGN KEY ".$value['constraint_name']." ";
                        Kernel::$db->query($sql);
                    }
                }

                // Add the Constraint
                $sql  = "ALTER TABLE ".$this->name." ";
                $sql .= " ADD CONSTRAINT ".$foreignkeyid." ";
                $sql .= " FOREIGN KEY (".$column->name.") ";
                $sql .= " REFERENCES ".$model->getDBTable()->name." (".$foreignkeyname.");";
                Kernel::$db->query($sql);


            }
        }

    }


}