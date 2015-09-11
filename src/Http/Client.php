<?php

namespace RingCentral\SDK\Http;

use Exception;
use GuzzleHttp\Psr7\Request;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamInterface;
use RingCentral\SDK\SDK;

class Client
{

    /**
     * @param RequestInterface $request
     * @return $this
     * @throws ApiException
     */
    public function send(RequestInterface $request)
    {

        $response = null;

        try {

            $response = $this->loadResponse($request);

            if ($response->ok()) {

                return $response;

            } else {

                throw new Exception('Response has unsuccessful status');

            }

        } catch (Exception $e) {

            // The following means that request failed completely
            if (empty($response)) {
                $response = new ApiResponse($request);
            }

            throw new ApiException($response, $e);

        }

    }

    /**
     * @param RequestInterface $request
     * @return ApiResponse
     * @throws Exception
     */
    protected function loadResponse(RequestInterface $request)
    {

        $ch = null;

        try {

            $ch = curl_init();

            if (!$ch) {
                throw new Exception('Cannot initialize a cURL handle');
            }

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

            curl_close($ch);

            return new ApiResponse($request, $body, $status);

        } catch (Exception $e) {

            if ($ch) {
                curl_close($ch);
            }

            throw $e;

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
    public function createRequest($method, $url, $queryParams = array(), $body = null, $headers = array())
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