<?php


/**
 * Routing of WyRa
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

namespace Wyra\Kernel;

use Wyra\Kernel\MVC\Controller;

class Route
{
    /**
     * @var array
     */
    private $routingData = array();

    /**
     * Routuing
     */
    public function route()
    {
        // Lade die Routing-Daten
        $this->loadRoutingData();

        // Bestimmung der ganzen Informationen
        $route = $this->getRoute();

        // Klasse aufbauen und ausführen
        $className = '\\Wyra\\Plugins\\'.$route['Plugin'].'\\Controller\\'.$route['SubPlugin'];

        /** @var Controller $instance */
        $instance = new $className($route);
        $instance->route();
    }

    /**
     * returning for the routing path
     *
     * @return array
     */
    private function getRoute()
    {
        // Pfad auslesen
        $route = $this->getRouting(Kernel::$get->get('route'));
        if (!Kernel::$config->get('installed')
            && Kernel::$get->get('route') !== 'install'
            && Kernel::$get->get('route') !== 'install/install') {
            header('Location: /install');
            exit;
        } elseif (Kernel::$config->get('installed')
            && !Kernel::$config->get('loggedin')
            && Kernel::$get->get('route') !== 'login'
            && Kernel::$get->get('route') !== 'login/login') {
            header('Location: /login');
            exit;
        } elseif (empty(Kernel::$get->get('route'))) {
            header('Location: /home');
            exit;
        } elseif (empty($route)) {
            throw new \ErrorException(Kernel::$language->getText('ROUTINGNICHTGEFUNDEN'));
        }

        // Login when not loggedin
        if (Kernel::$config->get('loggedin')
            and (Kernel::$get->get('route') === 'login'
             or  Kernel::$get->get('route') === 'login/login')) {
            header('Location: /home');
        }

        // Pfad interpretieren und in Rückgabe abfüllen
        $return = array();
        $routeEx = explode('|', $route);
        foreach ($routeEx as $routeitem) {
            $routeitemEx = explode(':', $routeitem);
            if (strtolower($routeitemEx[0]) === 'plugin') {
                $return['Plugin'] = trim($routeitemEx['1']);
            } elseif (strtolower($routeitemEx[0]) === 'subplugin') {
                $return['SubPlugin'] = trim($routeitemEx['1']);
            } elseif (strtolower($routeitemEx[0]) === 'api') {
                $return['Api'] = trim($routeitemEx['1']);
            } elseif (strtolower($routeitemEx[0]) === 'action') {
                $return['Action'] = trim($routeitemEx['1']);
            } else {
                $return[strtolower($routeitemEx[0])] = $routeitemEx[1];
            }
        }
        if (!isset($return['Plugin']) or !isset($return['SubPlugin'])) {
            throw new \ErrorException(Kernel::$language->getText('ROUTINGNICHTGEFUNDEN'));
        }
        if (Kernel::$get->get('api') != '') {
            $return['Api'] = trim(Kernel::$get->get('api'));
        }
        if (!isset($return['Api'])) {
            $return['Api'] = 'smarty';
        }
        if (!isset($return['Action'])) {
            $return['Action'] = 'home';
        }
        Kernel::$get->set('Api', $return['Api']);

        return $return;
    }

    /**
     * Load the Data from Routing-Files
     *
     */
    private function loadRoutingData()
    {
        // Check the plugin language
        $plugins = scandir('../src/Plugins');
        foreach ($plugins as $plugin) {
            $routingFile = '../src/Plugins/'.$plugin.'/src/Plugins/'.$plugin.'/route.txt';
            if ($plugin != '.empty' and is_file($routingFile)) {
                $this->loadRoutingDataFromFile($routingFile, $plugin);
            } elseif ($plugin != '.' and $plugin != '..' and $plugin != '.empty') {
                throw new \RuntimeException('Routing-File not found (Plugin: '.$plugin.')');
            }
        }
    }

    /**
     * Load the language data from the file
     *
     * @param string $file
     * @param string $plugin
     */
    private function loadRoutingDataFromFile($file, $plugin = '')
    {
        $file = file($file);
        foreach ($file as $row) {
            $columns = explode('=', $row);
            if (count($columns) === 2 and strpos($row, '#') !== 0) {
                $key = trim($columns[0]);
                if ($plugin != '') {
                    // ??
                }
                $this->setRouting($key, $columns[1]);
            }
        }
    }

    /**
     * Setzt Routing-Data
     *
     * @param $name
     * @param $value
     */
    private function setRouting($name, $value)
    {
        $this->routingData[$name] = $value;
    }

    /**
     * Gibt Routing-Data zurück
     *
     * @param $name
     *
     * @return mixed|null
     */
    public function getRouting($name)
    {
        if (isset($this->routingData[$name])) {
            return $this->routingData[$name];
        }
        return null;
    }


}