<?php

namespace RingCentral\http;

use Exception;

class Request extends Headers
{

    const POST = 'POST';
    const GET = 'GET';
    const DELETE = 'DELETE';
    const PUT = 'PUT';
    const PATCH = 'PATCH';

    protected static $allowedMethods = array(self::GET, self::POST, self::PUT, self::DELETE);

    protected $method;
    protected $url;
    protected $queryParams;
    protected $body;

    /** @var Response */
    protected $response = null;

    /**
     * @param string       $method
     * @param string       $url
     * @param array|null   $queryParams
     * @param array|string $body
     * @param array        $headers
     * @throws Exception
     */
    public function __construct($method, $url, $queryParams = array(), $body = null, $headers = array())
    {

        $this
            ->setMethod($method)
            ->setUrl($url)
            ->setQueryParams($queryParams)
            ->setBody($body)
            ->setHeaders(array(
                self::ACCEPT       => self::JSON_CONTENT_TYPE,
                self::CONTENT_TYPE => self::JSON_CONTENT_TYPE
            ))
            ->setHeaders($headers);

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
        if (!in_array($method, self::$allowedMethods)) {
            throw new Exception('Unknown method');
        }
        $this->method = $method;
        return $this;
    }

    public function getUrl()
    {
        return $this->url;
    }

    public function getUrlWithQueryString()
    {
        return $this->url . (!empty($this->queryParams) ? (stristr($this->url,
                '?') ? '&' : '?') . http_build_query($this->queryParams) : '');
    }

    public function setUrl($url)
    {
        $this->url = $url;
        return $this;
    }

    public function getQueryParams()
    {
        return $this->queryParams;
    }

    /**
     * @param array $queryParams
     * @return $this
     */
    public function setQueryParams($queryParams = array())
    {
        $this->queryParams = $queryParams;
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

    /**
     * @return $this
     * @throws HttpException
     * @codeCoverageIgnore
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

            if (!$response->checkStatus()) {
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