<?php

namespace Monetivo\Helpers;

use Exception;

/**
 * CURL helper class
 * @author Dariusz Jastrzębski <jastrzebskidarek94@gmail.com>
 */
class Curl
{

    /**
     * @var resource
     */
    protected $curl;
    /**
     * @var
     */
    protected $headers;
    /**
     * @var
     */
    protected $response;
    /**
     * @var
     */
    protected $error;
    /**
     * @var
     */
    protected $httpCode;
    /**
     * @var array
     */
    private $options = [];

    /**
     * Curl constructor.
     * @throws Exception
     */
    public function __construct()
    {
        if (!extension_loaded('curl')) {
            throw new Exception('cURL library is not loaded');
        }

        $this->curl = curl_init();
    }

    /**
     * Set request headers
     * @param $name
     * @param $value
     */
    public function setHeader($name, $value)
    {
        $this->headers[$name] = $value;
    }

    /**
     * Set Opt
     * @param  $option
     * @param  $value
     * @return boolean
     */
    public function setOpt($option, $value)
    {
        $this->options[$option] = $value;

        return curl_setopt($this->curl, $option, $value);
    }

    /**
     * Set POST data
     *
     * @param array $data
     * @return bool
     */
    public function setPost(array $data)
    {
        if (empty($data)) {
            return true;
        }

        return $this->setOpt(CURLOPT_POST, 1) && $this->setOpt(CURLOPT_POSTFIELDS, $data);
    }

    /**
     * Get CURL response
     * @return string
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * Get CURL error
     * @return string
     */
    public function getError()
    {
        return $this->error;
    }

    /**
     * Get HTTP response code
     * @return string
     */
    public function getHttpCode()
    {
        return $this->httpCode;
    }

    /**
     * Request call
     * @return bool
     */
    public function call()
    {
        if (!empty($this->headers)) {
            $headers = [];
            foreach ($this->headers as $name => $value) {
                $headers[] = $name . ': ' . $value;
            }
            $this->setOpt(CURLOPT_HTTPHEADER, $headers);
        }
        $this->response = curl_exec($this->curl);
        $this->error = curl_error($this->curl);
        $this->httpCode = curl_getinfo($this->curl, CURLINFO_HTTP_CODE);

        return $this->response !== false;
    }

    /**
     * Close
     * @access public
     */
    public function close()
    {
        if (is_resource($this->curl)) {
            curl_close($this->curl);
        }
        $this->curl = null;
        $this->options = null;
        $this->response = null;
        $this->error = null;
    }
}