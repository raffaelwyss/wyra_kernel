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
class Language extends BaseGetterSetter
{

    /**
     * Config constructor.
     *
     * @param string $file
     */
    public function __construct($language = 'de')
    {
        $this->loadLanguageData($language);
    }

    public function getText($label, $params = array())
    {
        $label = $this->get($label);
        return vsprintf($label, $params);
    }

    /**
     * Load the Data from Language-Files
     *
     * @param string $data
     * @param string $folder
     * @param string $baseString
     */
    private function loadLanguageData($language = '')
    {

        // Check the plugin language
        $plugins = scandir('../src/Plugin');
        foreach ($plugins AS $plugin) {
            $directory = '../src/Plugin/'.$plugin.'/Language';
            if (is_dir($directory)) {
                if ($plugin === '..') {
                    $plugin = '';
                }
                $languagefile = $directory.'/'.$language.'.txt';
                $languagefileDE = $directory.'/de.txt';
                if (is_file($languagefile)) {
                    $this->loadLanguageDataFromFile($languagefile, $plugin);
                } else if (is_file($languagefileDE)) {
                    $this->loadLanguageDataFromFile($languagefileDE, $plugin);
                } else {
                    throw new \RuntimeException('Language-File not found (Plugin: '.$plugin.')');
                }
            }
        }
    }

    /**
     * Load the language data from the file
     *
     * @param string $file
     * @param string $plugin
     */
    private function loadLanguageDataFromFile($file, $plugin = '')
    {
        $file = file($file);
        foreach ($file AS $row) {
            $columns = explode('=', $row);
            if (count($columns) === 2 AND strpos($row, '#') !== 0) {
                $key = trim($columns[0]);
                if ($plugin != '') {
                    $key = $plugin.'.'.$key;
                }
                $this->set($key, $columns[1]);
            }
        }
    }

}