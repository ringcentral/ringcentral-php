<?php

namespace RingCentral\http;

use Exception;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use stdClass;

/**
 * FIXME Support streams
 * @package RingCentral\http
 * @see     http://www.opensource.apple.com/source/apache_mod_php/apache_mod_php-7/php/pear/Mail/mimeDecode.php
 * @see     https://github.com/php-mime-mail-parser/php-mime-mail-parser
 */
class Transaction
{

    /** @var array */
    protected $jsonAsArray;

    /** @var stdClass */
    protected $jsonAsObject;

    /** @var Transaction[] */
    protected $multipartTransactions;

    /** @var ResponseInterface */
    protected $response;

    /** @var RequestInterface */
    protected $request;

    protected $raw;

    /**
     * TODO Support strams
     * @param RequestInterface $request Reqeuest used to get the response
     * @param mixed            $body    Stream body
     * @param int              $status  Status code for the response, if any
     */
    public function __construct(RequestInterface $request = null, $body = null, $status = 200)
    {

        $this->request = $request;
        $this->body = $body;

        if (is_string($body)) {

            // Make the HTTP message complete
            if (substr($body, 0, 5) !== 'HTTP/') {
                $body = "HTTP/1.1 " . $status . "\r\n" . $body;
            }

            $r = $this->parseResponse($body);

            if (!$r) {
                throw new \InvalidArgumentException('Message was empty');
            }

            $this->response = new Response($r['code'], $r['headers'], $r['body'], $r['version'], $r['reason_phrase']);

        }

    }

    public function getText()
    {

        return (string)$this->response->getBody();

    }

    public function getRaw()
    {

        return $this->raw;

    }

    /**
     * Parses response body as JSON
     * Result is cached internally
     * @param bool $asObject
     * @return stdClass|array
     * @throws Exception
     */
    public function getJson($asObject = true)
    {

        if (!$this->isContentType('application/json')) {
            throw new Exception('Response is not JSON');
        }

        if (($asObject && empty($this->jsonAsObject)) || (!$asObject && empty($this->jsonAsArray))) {

            $json = json_decode((string)$this->response->getBody(), !$asObject);

            if ($asObject) {
                $this->jsonAsObject = $json;
            } else {
                $this->jsonAsArray = $json;
            }

            $error = json_last_error();

            switch ($error) {
                case JSON_ERROR_NONE:
                    break;
                case JSON_ERROR_DEPTH:
                    throw new Exception('JSON Error: Maximum stack depth exceeded');
                    break;
                case JSON_ERROR_CTRL_CHAR:
                    throw new Exception('JSON Error: Unexpected control character found');
                    break;
                case JSON_ERROR_SYNTAX:
                    throw new Exception('JSON Error: Syntax error, malformed JSON');
                    break;
                default:
                    throw new Exception('JSON Error: Unknown error');
                    break;
            }

            // This is a courtesy by PHP JSON parser to parse "null" into null, but this is an error situation
            if (empty($json)) {
                throw new Exception('JSON Error: Result is empty after parsing');
            }

        }

        return $asObject ? $this->jsonAsObject : $this->jsonAsArray;

    }

    /**
     * Parses multipart response body as an array of Transaction objects
     * @return Transaction[]
     * @throws Exception
     */
    public function getMultipart()
    {

        if (empty($this->multipartTransactions)) {

            $this->multipartTransactions = array();

            if (!$this->isContentType('multipart/mixed')) {
                throw new Exception('Response is not multipart');
            }

            // Step 1. Get boundary

            preg_match('/boundary=([^";]+)/i', $this->getContentType(), $matches);

            if (empty($matches[1])) {
                throw new Exception('Boundary not found');
            }

            $boundary = $matches[1];

            // Step 2. Split by boundary and remove first and last parts if needed

            $parts = explode('--' . $boundary . '', (string)$this->response->getBody());

            if (empty($parts[0])) {
                array_shift($parts);
            }

            if (trim($parts[count($parts) - 1]) == '--') {
                array_pop($parts);
            }

            if (count($parts) == 0) {
                throw new Exception('No parts found');
            }

            // Step 3. Create status info object

            $statusInfoPart = array_shift($parts);
            $statusInfoObj = new self(null, trim($statusInfoPart), $this->response->getStatusCode());
            $statusInfo = $statusInfoObj->getJson()->response;

            // Step 4. Parse all parts into Response objects

            foreach ($parts as $i => $part) {

                $partInfo = $statusInfo[$i];

                $this->multipartTransactions[] = new self(null, trim($part), $partInfo->status);

            }

        }

        return $this->multipartTransactions;

    }

    public function checkStatus()
    {
        $status = $this->response->getStatusCode();
        return $status >= 200 && $status < 300;
    }

    /**
     * Convenience method on top of PSR-7 spec
     * Returns a meaningful error message
     * @return string
     */
    public function getError()
    {

        if (!$this->getResponse()) {
            return null;
        }

        if ($this->checkStatus()) {
            return null;
        }

        $message = ($this->getResponse()->getStatusCode() ? $this->getResponse()->getStatusCode() . ' ' : '') .
                   ($this->getResponse()->getReasonPhrase() ? $this->getResponse()->getReasonPhrase() : 'Unknown response reason phrase');

        try {

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

        } catch (Exception $e) {
        }

        return $message;

    }

    public function getRequest()
    {
        return $this->request;
    }

    public function getResponse()
    {
        return $this->response;
    }

    protected function isContentType($type)
    {
        return !!stristr(strtolower($this->getContentType()), strtolower($type));
    }

    protected function getContentType()
    {
        return $this->response->getHeaderLine('content-type');
    }

    /**
     * Ported from guzzle/guzzle
     * @param $message
     * @return array
     */
    protected function parseMessage($message)
    {
        $startLine = null;
        $headers = array();
        $body = '';

        // Iterate over each line in the message, accounting for line endings
        $lines = preg_split('/(\\r?\\n)/', $message, -1, PREG_SPLIT_DELIM_CAPTURE);
        for ($i = 0, $totalLines = count($lines); $i < $totalLines; $i += 2) {

            $line = $lines[$i];

            // If two line breaks were encountered, then this is the end of body
            if (empty($line)) {
                if ($i < $totalLines - 1) {
                    $body = implode('', array_slice($lines, $i + 2));
                }
                break;
            }

            // Parse message headers
            if (!$startLine) {
                $startLine = explode(' ', $line, 3);
            } elseif (strpos($line, ':')) {
                $parts = explode(':', $line, 2);
                $key = trim($parts[0]);
                $value = isset($parts[1]) ? trim($parts[1]) : '';
                if (!isset($headers[$key])) {
                    $headers[$key] = $value;
                } elseif (!is_array($headers[$key])) {
                    $headers[$key] = array($headers[$key], $value);
                } else {
                    $headers[$key][] = $value;
                }
            }
        }

        return array(
            'start_line' => $startLine,
            'headers'    => $headers,
            'body'       => $body
        );
    }

    /**
     * Ported from guzzle/guzzle
     * @param $message
     * @return array|bool
     */
    protected function parseResponse($message)
    {
        if (!$message) {
            return false;
        }

        $parts = $this->parseMessage($message);
        list($protocol, $version) = explode('/', trim($parts['start_line'][0]));

        return array(
            'protocol'      => $protocol,
            'version'       => $version,
            'code'          => $parts['start_line'][1],
            'reason_phrase' => isset($parts['start_line'][2]) ? $parts['start_line'][2] : '',
            'headers'       => $parts['headers'],
            'body'          => $parts['body']
        );
    }

}