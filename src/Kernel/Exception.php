<?php

namespace Wyra\Kernel;

use Wyra\Kernel\MVC\View;

use PDOException;

/**
 * Exception-Handler of WyRa
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
class Exception
{
    /**
     * @param \Exception|PDOException $exception
     */
    public function handler($exception)
    {
        $data = array();
        $data['message'] = Kernel::$language->get($exception->getMessage());
        $data['code'] = $exception->getCode();

        if ($data['code'] === 0 and get_class($exception) === 'Wyra\Kernel\Exception\UserException') {
            $data['code'] = 991;
        } elseif ($data['code'] === 0 and get_class($exception) === 'Wyra\Kernel\Exception\AppException') {
            $data['code'] = 993;
        } elseif ($data['code'] === 0 and get_class($exception) === 'Wyra\Kernel\Exception\FatalException') {
            $data['code'] = 995;
        } else {
            $data['code'] = 990;
        }

        if (Kernel::$config->get('debug')) {
            $data['file'] = $exception->getFile();
            $data['line'] = $exception->getLine();
            if (Kernel::$config->get('exceptionTracing')) {
                $data['trace'] = $exception->getTrace();
            }
            $data['message'] .= '<br>';
            $data['message'] .= 'File: '.$data['file'].'<br>';
            $data['message'] .= 'Line: '.$data['line'].'<br>';
            $data['message'] .= '<pre>'.print_r($data['trace'], 1).'</pre>';
        } elseif ($data['code'] === 900 or $data['code'] === 990) {
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
            $args['errortemplate'] = 'exception.tpl';
            $args['error'] = true;
            $view = new View();
            $view->show($data, Kernel::$get->get('Api'), $args);
        }
    }
}