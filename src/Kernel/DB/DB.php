<?php

namespace Wyra\Kernel\DB;

use PDO;
use PDOStatement;
use PDOException;
use Exception;
use Wyra\Kernel\Exception\AppException;
use Wyra\Kernel\Kernel;
use Wyra\Kernel\MVC\Model;
use Wyra\Kernel\MVC\ModelHolder;

class DB
{

    /** @var null|PDO  */
    private $dbh = null;

    /** @var array  */
    private $queryStore = [];

    public function connect($dbconf = array())
    {
        try {
            $this->dbh = new PDO(
                'mysql:host='.$dbconf['host'].';dbname='.$dbconf['database'].'',
                $dbconf['user'],
                $dbconf['password']
            );
            $this->dbh->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
            $this->dbh->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            throw new AppException("Base.DATENBENKVERBINDUNGFEHLGESCHLAGEN");
        }

    }

    public function query($sql, $data = array())
    {
        $debug = Kernel::$config->get('debug');
        if (!isset($this->queryStore[$sql])) {
            $this->queryStore[$sql] = $this->dbh->prepare($sql);
        }
        /** @var PDOStatement $stm */
        $stm = $this->queryStore[$sql];

        if ($stm === false) {
            $errorInfo = $this->dbh->errorInfo();
            $message = '';
            if (count($errorInfo) > 2) {
                $message = $errorInfo[0]."\n".$errorInfo[2];
            }
            $message .= "\n".$sql."\n";
            if (!$debug) {
                $exceptionmessage =  Kernel::$language->getText('VERARBEITUNGSFEHLER');
            } else {
                $exceptionmessage = Kernel::$language->getText('PDOERROR', [$message]);
                $exceptionmessage .= '<br><br>'.print_r($data, 1).' <br>';
            }

            throw  new PDOException($exceptionmessage);

        }

        if (!$stm->execute($data)) {
            if ($this->dbh->inTransaction()) {
                $this->dbh->rollBack();
            }

            if (!$debug) {
                $exceptionmessage =  Kernel::$language->getText('VERARBEITUNGSFEHLER');
            } else {
                $message = $stm->errorInfo()[2] . ": \n  $sql \n";
                if (isset($stm->errorInfo()[2])) {
                    array_walk($data, function ($item, $index) use (&$message) {
                        $message .= ':' . $index . ' = ' . $item . "\n";
                    });
                }
                $exceptionmessage = Kernel::$language->getText('PDOERROR', [$message]);
                $exceptionmessage .= '<br><br>'.print_r($data, 1).' <br>';

            }
            throw  new PDOException($exceptionmessage);
        }


        return $stm;

    }

    public function updateForeignKeys()
    {
        $pluginPath = Kernel::$config->get('rootPath').'/Plugins';

        $plugins = scandir($pluginPath);
        foreach ($plugins as $plugin) {
            if ($plugin != '.' and $plugin != '..' and $plugin != '.empty') {
                $modelHolderName = "Wyra\\Plugins\\".$plugin."\\Model\\ModelHolder";
                /** @var ModelHolder $modelHolder */
                $modelHolder = new $modelHolderName();
                /** @var Model $model */
                foreach ($modelHolder->models as $model) {
                    $model->getDBTable()->updateForeignKeys();
                }

            }
        }


    }


}