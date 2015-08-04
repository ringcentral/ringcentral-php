<?php

namespace RingCentral\http;

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
     *
     * If attachment name is not provided, it will be randomly generated.
     *
     * @param resource|string|StreamInterface $content  StreamInterface/resource/string to send
     * @param string                          $filename Filename of attachment, can't be empty if content is string
     * @param string                          $name     Form field name
     * @param array                           $headers  Associative array of custom headers
     * @return $this
     */
    public function addAttachment($content, $filename = '', $name = '', array $headers = array())
    {

        $element = array(
            'contents' => $content,
            'name'     => empty($name) ? uniqid() : $name
        );

        if (!empty($filename)) {

            $element['filename'] = $filename; // always set as defined

        } else {

            $uri = null;

            if ($content instanceof StreamInterface) {
                $uri = $content->getMetadata('uri');
            }

            if (is_resource($content)) {
                $meta = stream_get_meta_data($content);
                $uri = $meta['uri'];
            }

            if (empty($uri) || substr($uri, 0, 6) === 'php://') {
                $element['filename'] = $element['name'];
            }

        }

        if (!empty($headers)) {
            $element['headers'] = $headers;
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