<?php

namespace Wyra\Kernel;
use Wyra\Kernel\MVC\View;


/**
 * Error-Handling of WyRa
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
class Error
{

    /**
     * Register the the class for the handling for fatal errors
     */
    public function __construct()
    {
        register_shutdown_function(array($this, "handleFatalError"));
    }

    /**
     * Return the error in the JSON-Format
     *
     * @param        $errno
     * @param        $errstr
     * @param string $errfile
     * @param int    $errline
     * @param array  $errcontext
     */
    public function handler($errno, $errstr, $errfile = '', $errline = 0, $errcontext = array())
    {
        $data = array();
        $data['errno'] = $errno;
        $data['errstr'] = $errstr;
        $data['errfile'] = $errfile;
        $data['errline'] = $errline;
        $data['errcontext'] = print_r($errcontext, 1);
        $data['code'] = 999;
        if (Kernel::$config->get('debug')) {
            $data['message'] = "ErrorNo: ".$errno."<br>";
            $data['message'] .= "ErrorString: ".$errstr."<br>";
            $data['message'] .= "ErrorFile: ".$errfile."<br>";
            $data['message'] .= "ErrorLine: ".$errline."<br>";
        } else {
            $data['message'] = Kernel::$language->get('Base.VERARBEITUNGSFEHLER');
        }

        // Wenn leer soll es in diesem Fallw ie Smarty behandelt werden
        if (empty(Kernel::$get->get('Api'))) {
            Kernel::$get->set('Api', 'smarty');
        }


        if (Kernel::$get->get('Api') === 'json') {
            echo json_encode($data);
        } else {
            $args = [];
            $args['errortemplate'] = 'error.tpl';
            $args['error'] = true;
            $view = new View();
            $view->show($data, Kernel::$get->get('Api'), $args);
        }
    }

    /**
     * Handle the fatal errors
     */
    public function handleFatalError()
    {
        $error = error_get_last();
        if ($error["type"] === E_ERROR) {
            $this->handler(
                $error["type"],
                $error["message"],
                $error["file"],
                $error["line"]
            );

        }
    }

}