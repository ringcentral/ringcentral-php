<?php

namespace RC\core\ajax;

trait Headers
{

    private $headers = [];

    static $contentTypeHeader = 'content-type';
    static $authorizationHeader = 'authorization';
    static $acceptHeader = 'accept';
    static $urlEncodedContentType = 'application/x-www-form-urlencoded';
    static $jsonContentType = 'application/json';
    static $multipartContentType = 'multipart/mixed';

    public function setHeader($name, $value)
    {
        $this->headers[strtolower($name)] = $value;
        return $this;
    }

    public function getHeader($name)
    {
        return $this->headers[strtolower($name)];
    }

    public function isContentType($type)
    {
        return stristr($this->getContentType(), $type);
    }

    public function getContentType()
    {
        return $this->getHeader(self::$contentTypeHeader);
    }

    public function setContentType($contentType)
    {
        return $this->setHeader(self::$contentTypeHeader, $contentType);
    }

    public function setHeaders($headers = [])
    {

        if (!empty($headers)) {
            foreach ($headers as $name => $header) {
                $this->setHeader($name, $header);
            }
        }

        return $this;

    }
    public function getHeaders()
    {
        return $this->headers;
    }

}