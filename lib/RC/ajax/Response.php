<?php

namespace RC\ajax;

use Exception;

class Response extends Headers
{

    const BOUNDARY_SEPARATOR = '--';
    const BODY_SEPARATOR = "\n\n";
    const UNAUTHORIZED_STATUS = 401;
    const BOUNDARY_REGEXP = '/boundary=([^;]+)/i';

    private $body = '';
    private $raw = '';
    private $rawHeaders = '';
    /** @var \StdClass */
    private $data = null;
    private $status = 0;
    /** @var Response[] */
    private $responses = [];

    /**
     * @param string $status
     * @param string $raw
     * @throws Exception
     */
    public function __construct($status, $raw)
    {

        $this->raw = str_replace("\r", '', $raw);

        if (stristr($this->raw, self::BODY_SEPARATOR)) {
            list($this->rawHeaders, $this->body) = explode(self::BODY_SEPARATOR, $this->raw, 2);
        } else {
            $this->body = $this->raw;
        }

        $this->parseHeaders();

        if (empty($status)) {
            throw new Exception('Empty status was received');
        } else {
            $this->status = $status;
            $this->parseBody();
        }

    }

    protected function parseHeaders()
    {

        $headers = explode("\n", $this->rawHeaders);

        foreach ($headers as $header) {

            if (strlen($header) == 0) {
                continue;
            }

            $headerParts = explode(self::HEADER_SEPARATOR, $header);
            $name = trim(array_shift($headerParts));

            $this->setHeader($name, trim(implode(self::HEADER_SEPARATOR, $headerParts)));

        }

        return $this;

    }

    public function checkStatus()
    {
        return $this->status >= 200 && $this->status < 300;
    }

    protected function parseBody()
    {

        //switch ($this->getContentType()) {
        if ($this->isJson()) {

            $this->data = json_decode($this->body);

        } elseif ($this->isMultipart()) {

            preg_match(self::BOUNDARY_REGEXP, $this->getContentType(), $matches);
            $boundary = $matches[1];
            $parts = explode(self::BOUNDARY_SEPARATOR . $boundary, $this->body);

            if (trim($parts[0]) == '') {
                array_shift($parts);
            }
            if (trim($parts[sizeof($parts) - 1]) == self::BOUNDARY_SEPARATOR) {
                array_pop($parts);
            }

            $statusInfo = null;

            // Step 1. Claim first part as statuses, assign status from this and parse the response
            /** @var Response $statusInfo */
            $statusInfo = new Response($this->status, array_shift($parts));

            // Step 2. Parse all parts into Response objects
            foreach ($parts as $i => $part) {
                $this->responses[] = new self($statusInfo->data->response[$i]->status, $part);
            }

        } else {

            $this->data = $this->body;

        }

        return $this;

    }

    public function getBody()
    {
        return $this->body;
    }

    public function getData()
    {
        return $this->data;
    }

    public function getRaw()
    {
        return $this->raw;
    }

    public function getStatus()
    {
        return $this->status;
    }

    public function getResponses()
    {
        return $this->responses;
    }

}