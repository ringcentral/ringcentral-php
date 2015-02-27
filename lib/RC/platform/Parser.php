<?php

namespace RC\platform;

use GuzzleHttp\Message\MessageFactory;
use GuzzleHttp\Message\MessageInterface;
use GuzzleHttp\Message\Response;

class Parser
{

    const BOUNDARY_REGEXP = '/boundary=([^;]+)/i';
    const BOUNDARY_SEPARATOR = '--';

    /** @var MessageFactory */
    protected $factory;

    public function __construct()
    {
        $this->factory = new MessageFactory();
    }

    /**
     * @param MessageInterface|Response $response
     * @return Response[]
     * @throws \Exception
     */
    public function parse(MessageInterface $response)
    {

        $contentType = $response->getHeader('content-type');

        if (!stristr($contentType, 'multipart/mixed')) {
            throw new \Exception('Response is not multipart/mixed');
        }

        $body = $response->getBody(); //TODO Read stream as stream

        $responses = [];

        // Step 1. Split boundaries

        preg_match(self::BOUNDARY_REGEXP, $contentType, $matches);
        $boundary = $matches[1];
        $parts = explode(self::BOUNDARY_SEPARATOR . $boundary, $body);

        // First empty part out
        if (empty(trim($parts[0]))) {
            array_shift($parts);
        }

        // Last "--" part out
        if (trim($parts[sizeof($parts) - 1]) == self::BOUNDARY_SEPARATOR) {
            array_pop($parts);
        }

        // Step 2. Create status info object

        $statusInfo = $this->createResponse(
            $response->getStatusCode(),
            $response->getReasonPhrase(),
            array_shift($parts)
        )->json()['response'];

        // Step 3. Parse all parts into Response objects

        foreach ($parts as $i => $part) {

            $partInfo = $statusInfo[$i];

            $responses[] = $this->createResponse($partInfo['status'], $partInfo['responseDescription'], $part);

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

}