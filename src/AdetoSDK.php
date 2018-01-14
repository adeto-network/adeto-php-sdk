<?php
/**
 * Adeto PHP SDK allows developers to verify a captcha using adeto.ir REST API
 *
 * Copyright (c) 2018 Atikhosh E-Commerce Co.
 * www.Adeto.ir
 *
 * MIT License
 *
 * Copyright (c) 2018 Adeto
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 *
 *
 * @package    Adeto PHP SDK
 * @author     Behzad Khoshdouz <b.khoshdouz@adeto.ir>
 * @copyright  2014-2018 Atikhosh E-Commerce Co.
 * @link       https://github.com/adeto-network/adeto-php-sdk
 * @see        https://adeto.ir/developers
 * @license    http://opensource.org/licenses/mit-license.php The MIT License
 * @version    1.0.0
 */

class Adeto
{

    public $publisherKey;
    public $secretKey;
    public $userInputValue;
    public $imageName;
    public $hash;
    public $customId = "";
    public $method;

    private $useSSL = true;
    private $requestAddress;
    private $variables = array();
    private $userIP;

    /**
     * API address for verification request
     */
    const API_ADDRESS = 'https://api.adeto.ir/v1/verify';

    /**
     * Constructor
     * @param string $request_method
     */
    public function __construct($request_method)
    {
        if ($request_method === 'GET')
            $this->method = $_GET;
        else
            $this->method = $_POST;

        $this->userIP = $this->getUserIP();
    }

    /**
     * Checks if we have all the needed variables
     * @param array $values posted values
     */
    public function checkIfReceivedAllVariables($values)
    {
        $needle = array(
            'adetoImageName',
            'adetoHash',
            'adetoUserInputValue'
        );
        foreach ($needle as $value) {
            if (! array_key_exists($value, $values))
                throw new Exception($value . ' value is not present.');
        }
    }

    /**
     * Sending our request via fsockopen
     * @return array results
     */
    public function post()
    {
        $data = http_build_query($this->variables);
        $url  = parse_url($this->getRequestAddress());
        $host = $url['host'];
        $path = isset($url['path']) ? $url['path'] : '';
        if ($this->useSSL)
            $fp = fsockopen("ssl://{$host}", 443, $errno, $errstr, 30);
        else
            $fp = fsockopen($host, 80, $errno, $errstr, 30);
        if ($fp) {
            fputs($fp, "POST $path HTTP/1.1\r\n");
            fputs($fp, "Host: $host\r\n");

            if (isset($referer) && $referer != '')
                fputs($fp, "Referer: $referer\r\n");
            fputs($fp, "Content-type: application/x-www-form-urlencoded\r\n");
            fputs($fp, "Content-length: " . strlen($data) . "\r\n");
            fputs($fp, "Connection: close\r\n\r\n");
            fputs($fp, $data);
            $result = '';
            while (!feof($fp)) {
                $result .= fgets($fp, 128);
            }
        } else {
            throw new Exception($errstr . ' #' . $errno);
        }
        fclose($fp);
        $result  = explode("\r\n\r\n", $result, 2);
        $content = isset($result[1]) ? $result[1] : '';
        return $content;
    }

    /**
     * Sending our request via cURL
     * @return array results
     */
    public function curl()
    {
        $this->variables = http_build_query($this->variables);
        $ch              = curl_init($this->getRequestAddress());
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $this->variables);
        $response = curl_exec($ch);
        if (curl_errno($ch)) {
            throw new Exception(curl_error($ch));
        }
        curl_close($ch);
        return $response;
    }

    /**
     * Set variables for a verification request
     * @return object self
     */
    public function verify()
    {
        $this->requestAddress = self::API_ADDRESS;
        $this->variables      = array(
            'publisherKey' => $this->publisherKey,
            'secretKey' => $this->secretKey,
            'imageName' => $this->imageName,
            'hash' => $this->hash,
            'userInputValue' => $this->userInputValue,
            'customId' => $this->customId,
            'userIP' => $this->userIP
        );
        return $this;
    }

    /**
     * Get user IP
     * @return string user ip
     */
    private function getUserIP()
    {
        return $_SERVER['REMOTE_ADDR'];
    }

    /**
     * Check SSL status for getting request address
     * @return string address
     */
    private function getRequestAddress()
    {
        return $this->useSSL ? $this->requestAddress : str_replace('https', 'http', $this->requestAddress);
    }
}
