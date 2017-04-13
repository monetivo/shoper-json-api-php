<?php

namespace Monetivo;

use Monetivo\Exceptions\MonetivoException;
use Monetivo\Helpers\Curl;

/**
 * Class ShoperJsonApi
 * @author Grzegorz Agaciński <gagacinski@monetivo.com>
 * @package Monetivo
 */
class ShoperJsonApi
{

    /**
     * @var string
     */
    private $shopApiEndpoint;
    /**
     * @var string
     */
    private $sessionId;
    /**
     * @var mixed
     */
    private $debugFile;

    /**
     * ShoperJsonApi constructor
     * @param $shopUrl
     * @param bool $debugFile
     */
    function __construct($shopUrl, $debugFile = false)
    {
        $this->setShopApiEndpoint($shopUrl);
        $this->debugFile = $debugFile;
    }

    /**
     * Set API endpoint based on shop url
     * @param $shopUrl
     * @throws MonetivoException
     */
    public function setShopApiEndpoint($shopUrl)
    {
        if(filter_var($shopUrl, FILTER_VALIDATE_URL) === false)
            throw new MonetivoException('Invalid shop address: '.$shopUrl);

        $this->shopApiEndpoint = rtrim($shopUrl, '/') . '/webapi/json/';
        // sessionId is invalid in case of endpoint change
        $this->sessionId = null;
    }

    /**
     * Make Shoper API request
     * protected for client extending purposes
     * @param $method
     * @param $params
     * @param int $timeout
     * @return mixed|string
     * @throws MonetivoException
     */
    protected function call($method, $params, $timeout = 60)
    {
        $postParams['json'] = json_encode(['method' => $method, 'params' => $params]);

        $curl = new Curl();
        $curl->setPost($postParams);
        $curl->setOpt(CURLOPT_RETURNTRANSFER, true);
        $curl->setOpt(CURLOPT_SSL_VERIFYPEER, true);
        $curl->setOpt(CURLOPT_CONNECTTIMEOUT, ($timeout - 5));
        $curl->setOpt(CURLOPT_TIMEOUT, $timeout);
        $curl->setOpt(CURLOPT_URL, $this->shopApiEndpoint);

        $resp = '';
        if ($curl->call()) {
            $resp = json_decode($curl->getResponse(), 1);
        } else {
            throw new MonetivoException('Webapi CURL error: '.$curl->getError(), $curl->getHttpCode());
        }
        $curl->close();

        return $resp;
    }

    /**
     * Login to webapi
     * @param $login
     * @param $password
     * @return bool
     * @throws MonetivoException
     */
    public function login($login, $password)
    {
        $loginResp = $this->call('login', [$login, $password]);
        if (is_array($loginResp)) {
            throw new MonetivoException('Unable to login: '.$loginResp['error'], $loginResp['code']);
        } else {
            $this->sessionId = $loginResp;

            return true;
        }
    }

    /**
     * Logout
     * @return bool
     * @throws MonetivoException
     */
    public function logout()
    {
        $logoutResp = $this->call('logout', [$this->sessionId]);
        if (is_array($logoutResp)) {
            throw new MonetivoException('Logout error: '.$logoutResp['error'], $logoutResp['code']);
        } else {
            $this->sessionId = null;
            return true;
        }
    }

    /**
     * Magic method to construct any Shoper API call
     * @see https://www.shoper.pl/api For Shoper API documentation and methods
     *
     * @example
     * to call product.categories.list you call
     * $client->productCategoriesList(array $list)
     *
     * @param $name
     * @param $arguments
     * @return mixed|string
     * @throws MonetivoException
     */
    public function __call($name, $arguments)
    {
        if (empty($this->sessionId)) {
            throw new MonetivoException('sessionId cannot be blank');
        }

        //make $name into call method
        $methodName = '';
        foreach (str_split(lcfirst($name)) as $c) {
            $methodName .= (ctype_upper($c) ? '.' : '') . $c;
        }
        $methodName = strtolower($methodName);

        $resp = $this->call('call', [$this->sessionId, $methodName, $arguments]);
        if (is_array($resp) && isset($resp['error'])) {
            throw new MonetivoException('Webapi call error: '.$resp['error'], $resp['code']);
        } else {
            return $resp;
        }
    }

    /**
     * Returns sessionId, protected for client extending purposes
     * @return string
     */
    protected function getSessionId()
    {
        return $this->sessionId;
    }
}