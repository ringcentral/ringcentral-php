<?php

namespace RC\http;

use Exception;
use GuzzleHttp\Stream\StreamInterface;
use stdClass;

class Response extends \GuzzleHttp\Message\Response
{

    const BOUNDARY_REGEXP = '/boundary=([^;]+)/i';
    const BOUNDARY_SEPARATOR = '--';

    /** @var MessageFactory */
    private $factory;

    /**
     * @inheritdoc
     */
    public function __construct(
        MessageFactory $factory,
        $statusCode,
        array $headers = [],
        StreamInterface $body = null,
        array $options = []
    ) {

        parent::__construct($statusCode, $headers, $body, $options);

        $this->factory = $factory;

    }

    public function isJson()
    {
        return stristr($this->getHeader('content-type'), 'application/json');
    }

    public function isMultipart()
    {
        return stristr($this->getHeader('content-type'), 'multipart/mixed');
    }

    /**
     * Returns:
     * - array if JSON
     * - Response[] if Multipart
     * - string if else
     *
     * @return StreamInterface|array|string|Response[]
     * @throws Exception
     */
    public function getData()
    {
        if ($this->isJson()) {
            return $this->json(['object' => false]);
        } elseif ($this->isMultipart()) {
            return $this->getResponses();
        } else {
            return $this->getBody();
        }
    }

    /**
     * @return stdClass
     */
    public function getJson()
    {
        return $this->json(['object' => true]);
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

        $statusInfo = $this->createResponse($this->getStatusCode(), $this->getReasonPhrase(), array_shift($parts))
                           ->getJson()->response;

        // Step 3. Parse all parts into Response objects

        $responses = [];

        foreach ($parts as $i => $part) {

            $partInfo = $statusInfo[$i];

            $responses[] = $this->createResponse($partInfo->status, $partInfo->responseDescription, $part);

        }

        return $responses;

    }

    /**
     * @param int    $status
     * @param string $statusText
     * @param string $raw
     * @return Response
     */
    protected function createResponse($status, $statusText, $raw)
    {

        return $this->factory->fromMessage('HTTP/1.1 ' . $status . ' ' . $statusText . PHP_EOL . ltrim($raw));

    }

    /**
     * @return string
     */
    public function getError()
    {

        $message = $this->getStatusCode() . ' ' . $this->getReasonPhrase();

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