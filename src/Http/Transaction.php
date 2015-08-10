<?php

namespace RingCentral\SDK\Http;

use Exception;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use RingCentral\SDK\Core\Utils;
use stdClass;

/**
 * FIXME Support streams
 * @package RingCentral\SDK\Http
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
        $this->raw = $body;

        $body = (string)$body;

        // Make the HTTP message complete
        if (substr($body, 0, 5) !== 'HTTP/') {
            $body = "HTTP/1.1 " . $status . " OK\r\n" . $body;
        }

        $this->response = \GuzzleHttp\Psr7\parse_response($body);

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

            $json = Utils::json_parse((string)$this->response->getBody(), !$asObject);

            if ($asObject) {
                $this->jsonAsObject = $json;
            } else {
                $this->jsonAsArray = $json;
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

            $parts = explode('--' . $boundary . '', (string)$this->response->getBody()); //TODO Handle as stream

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

    public function isOK()
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

        if ($this->isOK()) {
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

}