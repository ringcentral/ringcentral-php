<?php

namespace RingCentral\http;

use Exception;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use stdClass;
use Zend\Mail\Headers;
use Zend\Mime\Decode;

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

    /**
     * TODO Support strams
     * @param RequestInterface $request Reqeuest used to get the response
     * @param mixed            $body    Stream body
     * @param int              $status  Status code for the response, if any
     */
    public function __construct(RequestInterface $request = null, $body = null, $status = 200)
    {

        $this->request = $request;

        $reason = null;
        $headers = array();

        if (is_string($body)) {

            preg_match('#^HTTP/1.(?:0|1) ([\d]{3})(.*)$#m', $body, $match);

            if (!empty($match[2])) {
                $reason = trim($match[2]);
            }

            if (!empty($match[1])) {
                $status = trim($match[1]);
            }

            if (!empty($match[0])) {
                $body = substr($body, strlen($match[0]) + 1);
            }

        }

        /** @var Headers $zendHeaders */
        $zendHeaders = null;
        $zendContent = null;
        Decode::splitMessage($body, $zendHeaders, $zendContent);


        foreach ($zendHeaders as $header) {
            $headers[$header->getFieldName()] = $header->getFieldValue();
        }

        $this->response = new Response($status, $headers, $zendContent, null, $reason);

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

            $json = json_decode($this->response->getBody()->__toString(), !$asObject);

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

            $contentType = $this->getContentType();

            if (!stristr($contentType, 'multipart/mixed')) {
                throw new Exception('Response is not multipart/mixed');
            }

            // Step 1. Split boundaries

            preg_match('/boundary="([^;]+)"/i', $contentType, $matches); // Zend Mime Decoder adds quotes

            if (empty($matches[1])) {
                throw new Exception('Boundary not found');
            }

            $boundary = $matches[1];

            $parts = Decode::splitMime($this->response->getBody()->__toString(), $boundary);

            if (count($parts) == 0) {
                throw new Exception('No parts found');
            }

            // Step 2. Create status info object

            $statusInfoObj = new self(null, array_shift($parts), $this->response->getStatusCode());
            $statusInfo = $statusInfoObj->getJson()->response;

            // Step 3. Parse all parts into Response objects

            foreach ($parts as $i => $part) {

                $partInfo = $statusInfo[$i];

                $this->multipartTransactions[] = new self(null, $part, $partInfo->status);

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
        $contentType = $this->response->getHeader('content-type');
        return $contentType[0];
    }

}