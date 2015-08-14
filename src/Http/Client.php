<?php

namespace RingCentral\SDK\Http;

use Exception;
use GuzzleHttp\Psr7\Request;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamInterface;
use RingCentral\SDK\Mocks\Registry;
use RingCentral\SDK\SDK;

class Client
{

    protected $_useMock = false;
    protected $appVersion;
    protected $appName;

    /** @var Registry */
    protected $mockRegistry;

    public function __construct($appName = '', $appVersion = '')
    {
        $this->mockRegistry = new Registry();
        $this->appVersion = $appVersion;
        $this->appName = $appName;
    }

    public function getMockRegistry()
    {
        return $this->mockRegistry;
    }

    public function useMock($flag = false)
    {
        $this->_useMock = $flag;
        return $this;
    }

    /**
     * @param RequestInterface $request
     * @return $this
     * @throws HttpException
     */
    public function send(RequestInterface $request)
    {

        return ($this->_useMock)
            ? $this->sendViaMock($request)
            : $this->sendViaCurl($request);

    }

    /**
     * TODO Use sockets
     * @param RequestInterface $request
     * @return Transaction
     * @throws HttpException
     * @codeCoverageIgnore
     */
    protected function sendViaCurl(RequestInterface $request)
    {

        $ch = null;
        $transaction = null;

        try {

            $ch = curl_init();

            if (!$ch) {
                throw new Exception('Couldn\'t initialize a cURL handle');
            }

            $ua = (!empty($this->appName) ? ($this->appName . (!empty($this->appVersion) ? '/' . $this->appVersion : '') . ' ') : '') .
                  php_uname('s') . '/' . php_uname('r') . ' ' .
                  'PHP/' . phpversion() . ' ' .
                  'RCPHPSDK/' . SDK::VERSION;

            /** @var Request $request */
            $request = $request->withAddedHeader('User-Agent', $ua)
                               ->withAddedHeader('RC-User-Agent', $ua);

            curl_setopt($ch, CURLOPT_URL, $request->getUri());

            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $request->getMethod());
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HEADER, true);

            if (($request->getMethod() == 'PUT' || $request->getMethod() == 'POST')) {

                if (stristr($request->getHeaderLine('Content-Type'), 'multipart/form-data')) {
                    $request = $request->withHeader('Expect', '');
                }

                if ($request->getBody()->isSeekable()) {
                    $request->getBody()->rewind();
                }

            }

            curl_setopt($ch, CURLOPT_HTTPHEADER, $this->getRequestHeaders($request));

            if ($request->getMethod() == 'PUT' || $request->getMethod() == 'POST') {
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, (string)$request->getBody()); //TODO Handle streams
            }

            $body = curl_exec($ch);

            $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);

            $transaction = new Transaction($request, $body, $status);

            if ($transaction->isOK()) {

                curl_close($ch);

                return $transaction;

            } else {

                throw new Exception('Response has unsuccessful status');

            }

        } catch (Exception $e) {

            if ($ch) {
                curl_close($ch);
            }

            // The following means that request failed completely
            if (empty($transaction)) {
                $transaction = new Transaction($request);
            }

            throw new HttpException($transaction, $e);

        }

    }

    /**
     * @param RequestInterface $request
     * @return $this
     * @throws HttpException
     */
    protected function sendViaMock($request)
    {

        $transaction = null;

        try {

            $responseMock = $this->mockRegistry->find($request);

            if (empty($responseMock)) {
                throw new Exception(sprintf('Mock for "%s" has not been found in registry', $request->getUri()));
            }

            $responseBody = $responseMock->getResponse($request);

            $transaction = new Transaction($request, $responseBody);

            if ($transaction->isOK()) {

                return $transaction;

            } else {

                throw new Exception('Response has unsuccessful status');

            }

        } catch (Exception $e) {

            // The following means that request failed completely
            if (empty($transaction)) {
                $transaction = new Transaction($request);
            }

            throw new HttpException($transaction, $e);

        }

    }

    /**
     * @param null|string                                $method
     * @param null|string                                $url
     * @param null|string|array                          $queryParams
     * @param null|string|array|resource|StreamInterface $body Message body.
     * @param null|array                                 $headers
     * @throws Exception
     * @return RequestInterface
     */
    public function requestFactory($method, $url, $queryParams = array(), $body = null, $headers = array())
    {

        $properties = $this->parseProperties($method, $url, $queryParams, $body, $headers);

        return new Request($properties['method'], $properties['url'], $properties['headers'], $properties['body']);

    }

    /**
     * @param RequestInterface $request
     * @return string[]
     */
    protected function getRequestHeaders(RequestInterface $request)
    {

        $headers = array();

        foreach (array_keys($request->getHeaders()) as $name) {
            $headers[] = $name . ': ' . $request->getHeaderLine($name);
        }

        return $headers;

    }

    /**
     * @param null|string                                $method
     * @param null|string                                $url
     * @param null|string|array                          $queryParams
     * @param null|string|array|resource|StreamInterface $body Message body.
     * @param null|array                                 $headers
     * @throws Exception
     * @return array
     */
    protected function parseProperties($method, $url, $queryParams = array(), $body = null, $headers = array())
    {

        // URL

        if (!empty($queryParams) && is_array($queryParams)) {
            $queryParams = http_build_query($queryParams);
        }

        if (!empty($queryParams)) {
            $url = $url . (stristr($url, '?') ? '&' : '?') . $queryParams;
        }

        // Headers

        $contentType = null;
        $accept = null;

        foreach ($headers as $k => $v) {

            if (strtolower($k) == 'content-type') {
                $contentType = $v;
            }

            if (strtolower($k) == 'accept') {
                $accept = $v;
            }

        }

        if (!$contentType) {
            $contentType = 'application/json';
            $headers['content-type'] = $contentType;
        }

        if (!$accept) {
            $accept = 'application/json';
            $headers['accept'] = $accept;
        }

        // Body

        if ($contentType) {

            switch (strtolower($contentType)) {
                case 'application/json':
                    $body = json_encode($body);
                    break;

                case 'application/x-www-form-urlencoded';
                    $body = http_build_query($body);
                    break;

                default:
                    break;
            }

        }

        // Create request

        return array(
            'method'  => $method,
            'url'     => $url,
            'headers' => $headers,
            'body'    => $body,
        );

    }

}