<?php

namespace RC\ajax;

use Exception;

class Request extends Headers
{

    const POST = 'POST';
    const GET = 'GET';
    const DELETE = 'DELETE';
    const PUT = 'PUT';

    private static $allowedMethods = [self::GET, self::POST, self::PUT, self::DELETE];

    private $method = self::GET;
    private $url = '';
    private $queryParams = [];
    private $body = null;

    public function __construct($method = '', $url = '', $queryParams = [], $body = null, $headers = [])
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
        return $this->url . (stristr($this->url, '?') ? '&' : '?') . http_build_query($this->queryParams);
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

}