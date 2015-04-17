<?php

namespace RC\http;

use Exception;
use stdClass;

class Response extends Headers
{

    const BOUNDARY_REGEXP = '/boundary=([^;]+)/i';
    const BODY_SEPARATOR = "\n\n";
    const BOUNDARY_SEPARATOR = '--';

    private $body = '';
    private $raw = '';
    private $rawHeaders = '';
    private $status = 0;

    /**
     * @inheritdoc
     */
    public function __construct($status, $raw)
    {

        $this->status = $status;

        $this->raw = str_replace("\r", '', $raw);

        if (stristr($this->raw, self::BODY_SEPARATOR)) {
            list($this->rawHeaders, $this->body) = explode(self::BODY_SEPARATOR, $this->raw, 2);
        } else {
            $this->body = $this->raw;
        }

        $this->parseHeaders();

        //if (empty($status)) {
        //    throw new Exception('Empty status was received');
        //}

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

    /**
     * @param bool $asObject
     * @return stdClass|array
     * @throws Exception
     */
    public function getJson($asObject = true)
    {
        if (!$this->isJson()) {
            throw new Exception('Response is not JSON');
        }
        return json_decode($this->body, !$asObject);
    }

    /**
     * @return Response[]
     * @throws Exception
     */
    public function getResponses()
    {

        if (!$this->isMultipart()) {
            throw new Exception('Response is not multipart');
        }

        $contentType = $this->getHeader('content-type');

        if (!stristr($contentType, 'multipart/mixed')) {
            throw new Exception('Response is not multipart/mixed');
        }

        $body = $this->getBody(); //TODO Read stream as stream

        // Step 1. Split boundaries

        preg_match(self::BOUNDARY_REGEXP, $contentType, $matches);
        $boundary = $matches[1];
        $parts = explode(self::BOUNDARY_SEPARATOR . $boundary, $body);

        // First empty part out
        if (!$parts[0] || !trim($parts[0])) {
            array_shift($parts);
        }

        // Last "--" part out
        if (trim($parts[sizeof($parts) - 1]) == self::BOUNDARY_SEPARATOR) {
            array_pop($parts);
        }

        // Step 2. Create status info object

        $statusInfoObj = new Response($this->getStatus(), array_shift($parts));
        $statusInfo = $statusInfoObj->getJson()->response;

        // Step 3. Parse all parts into Response objects

        $responses = array();

        foreach ($parts as $i => $part) {

            $partInfo = $statusInfo[$i];

            $responses[] = new Response($partInfo->status, $part);

        }

        return $responses;

    }

    public function getBody()
    {
        return $this->body;
    }

    public function getRaw()
    {
        return $this->raw;
    }

    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @return string
     */
    public function getError()
    {

        if ($this->checkStatus()) return null;

        $message = $this->getStatus() . ' Unknown response error';

        $data = $this->getJson();

        if (!empty($data->message)) {
            $message = $data->message;
        }

        if (!empty($data->error_description)) {
            $message = $data->error_description;
        }

        if (!empty($data->description)) {
            $message = $data->description;
        }

        return $message;

    }

}