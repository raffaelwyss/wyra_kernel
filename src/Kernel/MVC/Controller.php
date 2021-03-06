<?php

namespace Wyra\Kernel\MVC;

use Wyra\Kernel\Kernel;
use Exception;
use RuntimeException;

/**
 * Controller of WyRa
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
class Controller
{
    /** @var View|null  */
    private $view = null;

    /** @var array */
    protected $arguments = array();

    /** @var array Daten */
    private $data = [];

    /**
     * Controller constructor.
     *
     * @param array $args
     */
    public function __construct($args = array())
    {
        $this->arguments = $args;
        $this->arguments['urllist'] = array();
        $this->setView();
        $this->getView();
        $this->arguments['elements'] = $this->getView()->getFormStructure();
    }



    public function route()
    {
        switch($this->arguments['Action']) {
            case 'home':
                $this->validateShowHome();
                $this->showHome();
                break;
            case 'insert':
                $this->validateDoInsert();
                $this->doInsert();
                break;
            default:
                throw new \ErrorException(Kernel::$language->getText('ROUTINGNICHTGEFUNDEN'));
                break;
        }
        $this->display();
    }

    /**
     * Anzeige der Daten
     */
    public function display($data = array())
    {
        if (!isset($this->arguments['Api'])) {
            throw new RuntimeException('ANZEIGENICHTIMPLEMENTIERT');
        }
        switch ($this->arguments['Api']) {
            case 'smarty':
                $this->getView()->show($this->getData(), 'smarty', $this->arguments);
                break;
            case 'json':
                $this->getView()->show($this->getData(), 'json');
                break;
            default:
                throw new RuntimeException('ANZEIGENICHTIMPLEMENTIERT');
                break;
        }
    }

    protected function showHome()
    {
        throw new \ErrorException(Kernel::$language->getText('FOLGENDEMETHODEINKLASSEERSTELLEN', ['showHome']));
    }

    protected function doInsert()
    {
        throw new \ErrorException(Kernel::$language->getText('FOLGENDEMETHODEINKLASSEERSTELLEN', ['doInsert']));
    }

    protected function setData($data)
    {
        $this->data = $data;
    }


    protected function getData()
    {
        return $this->data;
    }

    protected function addArguments($arguments)
    {
        $this->arguments = array_merge($this->arguments, $arguments);
    }

    protected function addArgument($name, $value)
    {
        $this->arguments[$name] = $value;
    }

    protected function addURL($name, $url)
    {
        $this->arguments['urllist'][$name] = $url;
    }

    /**
     * return the View-Instance
     *
     * @return null|View
     */
    private function getView()
    {
        if ($this->view) {
            return $this->view;
        }
        throw new Exception(Kernel::$language->getText('FOLGENDEVIEWFEHLT', get_class($this)));
    }

    /**
     * Set the view-instance
     */
    private function setView()
    {
        $className = '\\Wyra\\Plugins\\'.$this->arguments['Plugin'].'\\View\\'.$this->arguments['SubPlugin'];
        $this->view = new $className();
    }

    protected function validateShowHome()
    {
        throw new \ErrorException(Kernel::$language->getText('ZUGRIFFVERWEIGERT'));
    }

    protected function validateDoInsert()
    {
        throw new \ErrorException(Kernel::$language->getText('ZUGRIFFVERWEIGERT'));
    }



}