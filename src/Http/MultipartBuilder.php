<?php

namespace RingCentral\SDK\Http;

use GuzzleHttp\Psr7\MultipartStream;
use GuzzleHttp\Psr7\Request;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamInterface;

class MultipartBuilder
{

    protected $body = array();
    protected $elements = array();
    protected $boundary = null;

    public function setBoundary($boundary = '')
    {
        $this->boundary = $boundary;
        return $this;
    }

    public function getBoundary()
    {
        return $this->boundary;
    }

    public function setBody(array $body = array())
    {
        $this->body = $body;
        return $this;
    }

    public function getBody()
    {
        return $this->body;
    }

    /**
     * Function always use provided $filename. In cases when it's empty, for string content or when name cannot be
     * automatically discovered the $filename will be set to attachment name.
     * If attachment name is not provided, it will be randomly generated.
     * @param resource|string|StreamInterface $content  StreamInterface/resource/string to send
     * @param string                          $filename Optional. Filename of attachment, can't be empty if content is string
     * @param array                           $headers  Optional. Associative array of custom headers
     * @param string                          $name     Optional. Form field name
     * @return $this
     */
    public function addAttachment($content, $filename = '', array $headers = array(), $name = '')
    {

        $uri = null;

        if (!empty($filename)) {

            $uri = $filename;

        } elseif ($content instanceof StreamInterface) {

            $meta = $content->getMetadata('uri');

            if (substr($meta, 0, 6) !== 'php://') {
                $uri = $meta;
            }

        } elseif (is_resource($content)) {

            $meta = stream_get_meta_data($content);
            $uri = $meta['uri'];

        }

        $basename = basename($uri);

        if (empty($basename)) {
            throw new \InvalidArgumentException('File name was not provided and cannot be auto-discovered');
        }

        $name = !empty($name) ? $name : $basename;

        $element = array(
            'contents' => $content,
            'name'     => $name
        );

        // always set as defined or else it will be auto-discovered by Guzzle
        if (!empty($filename)) {
            $element['filename'] = $filename;
        }

        if (!empty($headers)) {
            $element['headers'] = $headers;
        }

        $contentKey = null;

        foreach ($headers as $k => $v) {
            if (strtolower($k) == 'content-type') {
                $contentKey = $k;
            }
        }

        if (empty($contentKey)) {

            if (is_string($content)) {

                // Automatically set
                $element['headers']['Content-Type'] = 'application/octet-stream';

            } elseif ($content instanceof StreamInterface) {

                $type = \GuzzleHttp\Psr7\mimetype_from_filename($basename);

                if (!$type) {
                    throw new \InvalidArgumentException('Content-Type header was not provided and cannot be auto-discovered');
                }

            }

        }

        $this->elements[] = $element;

        return $this;

    }

    public function getAttachments()
    {
        return $this->elements;
    }

    /**
     * @param string $uri
     * @param string $method
     * @throws \InvalidArgumentException
     * @return RequestInterface
     */
    public function getRequest($uri, $method = 'POST')
    {

        $stream = $this->getRequestBody();
        $headers = array('Content-Type' => 'multipart/form-data; boundary=' . $stream->getBoundary());

        return new Request($method, $uri, $headers, $stream);

    }

    /**
     * @return StreamInterface|MultipartStream
     */
    protected function getRequestBody()
    {

        $bodyAttachment = array(
            array(
                'name'     => 'json',
                'contents' => json_encode($this->body),
                'headers'  => array(
                    'Content-Type' => 'application/json'
                ),
                'filename' => 'request.json',
            )
        );

        $stream = new MultipartStream(array_merge($bodyAttachment, $this->elements), $this->boundary);

        return $stream;

    }

}