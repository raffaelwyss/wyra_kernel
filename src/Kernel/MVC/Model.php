<?php

namespace Wyra\Kernel\MVC;

use Wyra\Kernel\DB\DBTable;
use ErrorException;
use Wyra\Kernel\Kernel;


/**
 * Model of WyRa
 *
 * Copyright (c) 2017, Raffael Wyss <raffael.wyss@gmail.com>
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions
 * are met:
 *
 *   * Redistributions of source code must retain the above copyright
 *     notice, this list of conditions and the following disclaimer.
 *
 *   * Redistributions in binary form must reproduce the above copyright
 *     notice, this list of conditions and the following disclaimer in
 *     the documentation and/or other materials provided with the
 *     distribution.
 *
 *   * Neither the name of Raffael Wyss nor the names of his
 *     contributors may be used to endorse or promote products derived
 *     from this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS
 * FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE
 * COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT,
 * INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING,
 * BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT
 * LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN
 * ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 *
 * @autor       Raffael Wyss <raffael.wyss@gmail.com>
 * @copyright   2017 Raffael Wyss. All rights reserved.
 * @license     http://www.opensource.org/licenses/bsd-license.php BSD License
 */
class Model
{

    /** @var null|DBTable  */
    private $dbtable = null;

    public function __construct()
    {
        $this->dbtable = new DBTable();
        $this->loadDBStructure();
        $this->loadDBStructureDefault();

    }

    protected function loadDBStructure()
    {
        // need in Controller
    }


    protected function loadDBStructureDefault()
    {
        // Entry-Date
        $this->getDBTable()->addColumn('dentrydate', [
            'type'          => 'DATETIME',
            'default'       => date('Y-m-d H:i:s'),
            'null'          => false
        ]);

        // TimeStamp
        $this->getDBTable()->addColumn('dtimestamp', [
            'type'          => 'TIMESTAMP',
            'default'       => date('Y-m-d H:i:s'),
            'null'          => false
        ]);

        // Creator
        $this->getDBTable()->addColumn('ncreator', [
            'type'          => 'INT',
            'size'          => 11,
            'default'       => 0,
            'null'          => false
        ]);

        // Editor
        $this->getDBTable()->addColumn('neditor', [
            'type'          => 'INT',
            'size'          => 11,
            'default'       => 0,
            'null'          => false
        ]);

    }

    public function getDBTable()
    {
        return $this->dbtable;
    }

    public function getCreateStatement()
    {
        return $this->dbtable->getCreateStatement();
    }

    public function getAll($fields = [], $options = [])
    {
        $this->getData();
    }

    public function getAllWhere($where, $data = [], $fields = [], $options = [])
    {
        if (empty($where)) {
            throw  new \RuntimeException(Kernel::$language->get('WHERELEER'));
        }
        return $this->getData($where, $data, $fields);
    }

    public function getOneWhere($where, $data = [], $fields = [], $options = [])
    {
        $options['limit'] = 1;
        $data = $this->getAllWhere($where, $data, $fields, $options);
        if ($data != false and count($data) > 0) {
            return $data[0];
        }
        return false;
    }

    private function getData($where = '', $data = [], $fields = [], $options = [])
    {
        // Options
        $limit = (isset($options['limit'])) ? (integer) $options['limit'] : false;

        // Fields set
        if (count($fields) === 0) {
            $fields = $this->getDBTable()->getColumns(1);
        }

        // Grund-Befehl
        $sql =  "SELECT ".implode(', ', $fields)." ";
        $sql .= "FROM ".$this->getDBTable()->name." ";

        // Where-Part
        if (!empty($where)) {
            $sql .= "WHERE ".$where." ";
        }

        // Limit-Part
        if ($limit !== false) {
            $sql .= "LIMIT ".$limit." ";
        }

        $stm = Kernel::$db->query($sql, $data);
        $returnData = $stm->fetchAll();

        return $returnData;

    }

}