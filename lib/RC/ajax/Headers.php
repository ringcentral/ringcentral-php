<?php

namespace RC\ajax;

abstract class Headers
{

    private $headers = [];

    const HEADER_SEPARATOR = ':';
    const CONTENT_TYPE = 'content-type';
    const AUTHORIZATION = 'authorization';
    const ACCEPT = 'accept';
    const URL_ENCODED_CONTENT_TYPE = 'application/x-www-form-urlencoded';
    const JSON_CONTENT_TYPE = 'application/json';
    const MULTIPART_CONTENT_TYPE = 'multipart/mixed';

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
        return $this->getHeader(self::CONTENT_TYPE);
    }

    public function setContentType($contentType)
    {
        return $this->setHeader(self::CONTENT_TYPE, $contentType);
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

    public function getHeadersArray()
    {
        $curlHeaders = [];
        foreach ($this->getHeaders() as $name => $header) {
            $curlHeaders[] = strtolower($name) . self::HEADER_SEPARATOR . $header;
        }
        return $curlHeaders;
    }

    public function isJson()
    {
        return $this->isContentType(self::JSON_CONTENT_TYPE);
    }

    public function isMultipart()
    {
        return $this->isContentType(self::MULTIPART_CONTENT_TYPE);
    }

    public function isUrlEncoded()
    {
        return $this->isContentType(self::URL_ENCODED_CONTENT_TYPE);
    }

}