<?php


/**
 * Crypt of WyRa
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


class Crypt
{
    private $key = 'thisismykeydkehg';
    private $cypher = MCRYPT_RIJNDAEL_256;
    private $mode = MCRYPT_MODE_CBC;
    private $rand = MCRYPT_RAND;

    /**
     * Crypting Data
     *
     * @param string $data
     *
     * @return string
     */
    public function crypt($data)
    {
        $iv_size = mcrypt_get_iv_size($this->cypher, $this->mode);
        $iv = mcrypt_create_iv($iv_size, $this->rand);
        $ciphertext = mcrypt_encrypt($this->cypher, $this->key, $data, $this->mode, $iv);
        $ciphertext = $iv . $ciphertext;
        return base64_encode($ciphertext);
    }

    /**
     * Decripting Data
     *
     * @param string $data
     *
     * @return string
     */
    public function decrypt($data)
    {
        $iv_size = mcrypt_get_iv_size($this->cypher, $this->mode);
        $ciphertext = base64_decode($data);
        $iv = substr($ciphertext, 0, $iv_size);
        $ciphertext = substr($ciphertext, $iv_size);
        return trim(mcrypt_decrypt($this->cypher, $this->key, $ciphertext, $this->mode, $iv));
    }
}