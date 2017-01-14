<?php

namespace Wyra\Kernel;

use Wyra\Kernel\Storage\BaseGetterSetter;

/**
 * Config of WyRa
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
class Config extends BaseGetterSetter
{

    /**
     * Config constructor.
     *
     * @param string $file
     */
    public function __construct($file = '../app/config/app.conf')
    {
        $this->data = json_decode(file_get_contents($file), true);
        $this->loadConfigData($this->data, dirname($file));
        $this->setDefaultConfig();
    }

    private function setDefaultConfig()
    {
        if ($this->get('errorReporting') !== '') {
            error_reporting($this->get('errorReporting'));
        }
    }

    /**
     * Load the Data from Config-File
     *
     * @param string $data
     * @param string $folder
     * @param string $baseString
     */
    private function loadConfigData($data = array(), $folder = '', $baseString = '')
    {
        foreach ($data AS $key => $value) {
            $startString = '';
            if ($baseString != '') {
                $startString = $baseString.'.';
            }
            if (strpos($value, '.conf')) {
                $installfile = $folder.'/installed/'.$value;
                if (file_exists($installfile)) {
                    $filecontent = json_decode(file_get_contents($installfile), true);
                } else {
                    $filecontent = json_decode(file_get_contents($folder.'/'.$value), true);
                }
                $this->data[$key] = $filecontent;
                $this->loadConfigData($filecontent, $folder, $key);
            } else {
                $this->data[$startString.$key] = $value;
            }
        }
        $this->data['rootPath'] = dirname(__DIR__);
        $this->data['wyraPath'] = realpath('../');
        $this->data['installed'] = false;
        if (file_exists($this->data['rootPath'].'/.installed')) {
            $this->data['installed'] = true;
        }
        $this->data['loggedin'] = false;
        if (Kernel::$session->get('wyra.loggedin') === true) {
            $this->data['loggedin'] = true;
        }
        $this->data['baseUrl'] = Kernel::$server->get('baseUrl');
    }

}