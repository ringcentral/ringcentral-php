<?php

namespace RC\http;

use Exception;

class Request extends Headers
{

    const POST = 'POST';
    const GET = 'GET';
    const DELETE = 'DELETE';
    const PUT = 'PUT';
    const PATCH = 'PATCH';

    protected static $allowedMethods = [self::GET, self::POST, self::PUT, self::DELETE];

    protected $method = self::GET;
    protected $url = '';
    protected $queryParams = array();
    protected $body = null;

    /** @var Response */
    protected $response = null;

    public function __construct($method = '', $url = '', $queryParams = array(), $body = null, $headers = array())
    {

        if (empty($method)) {
            throw new Exception('Method must be provided');
        }

        if (empty($url)) {
            throw new Exception('Url must be provided');
        }

        if (!in_array($method, self::$allowedMethods)) {
            throw new Exception('Unknown method');
        }

        $this->method = $method;

        $this->url = $url;

        if (!empty($queryParams)) {
            $this->queryParams = $queryParams;
        }

        if (!empty($body)) {
            $this->body = $body;
        }

        $this->setHeaders([
            self::ACCEPT       => self::JSON_CONTENT_TYPE,
            self::CONTENT_TYPE => self::JSON_CONTENT_TYPE
        ]);

        $this->setHeaders($headers);

    }

    public function getUrl()
    {
        return $this->url;
    }

    public function getUrlWithQueryString()
    {
        return $this->url . (!empty($this->queryParams) ? (stristr($this->url, '?') ? '&' : '?') . http_build_query($this->queryParams) : '');
    }

    public function getEncodedBody()
    {

        if ($this->isJson()) {
            return json_encode($this->body);
        } elseif ($this->isUrlEncoded()) {
            return http_build_query($this->body);
        } else {
            return $this->body;
        }

    }

    public function isPut()
    {
        return $this->method == self::PUT;
    }

    public function isGet()
    {
        return $this->method == self::GET;
    }

    public function isPost()
    {
        return $this->method == self::POST;
    }

    public function isDelete()
    {
        return $this->method == self::DELETE;
    }

    public function getMethod()
    {
        return $this->method;
    }

    public function setMethod($method)
    {
        $this->method = $method;
        return $this;
    }

    public function setUrl($url)
    {
        $this->url = $url;
        return $this;
    }

    public function setBody($body)
    {
        $this->body = $body;
        return $this;
    }

    public function getBody()
    {
        return $this->body;
    }

    public function getQueryParams()
    {
        return $this->queryParams;
    }

    /**
     * @return $this
     * @throws HttpException
     */
    public function send()
    {

        $ch = curl_init();
        $response = null;

        try {

            curl_setopt($ch, CURLOPT_URL, $this->getUrlWithQueryString());

            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $this->getMethod());
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HEADER, true);

            curl_setopt($ch, CURLOPT_HTTPHEADER, $this->getHeadersArray());

            if ($this->isPut() || $this->isPost()) {
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $this->getEncodedBody());
            }

            $res = curl_exec($ch);

            $response = new Response(curl_getinfo($ch, CURLINFO_HTTP_CODE), $res);

            if (!$response->isSuccess()) {
                throw new Exception('Response has unsuccessful status');
            }

        } catch (Exception $e) {

            curl_close($ch);

            throw new HttpException($this, $response, $e);

        }

        curl_close($ch);

        return $response;

    }

}