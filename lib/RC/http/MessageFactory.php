<?php

namespace RC\http;

use GuzzleHttp\Stream\Stream;

class MessageFactory extends \GuzzleHttp\Message\MessageFactory
{

    public function createResponse(
        $statusCode,
        array $headers = [],
        $body = null,
        array $options = []
    ) {
        if (null !== $body) {
            $body = Stream::factory($body);
        }

        return new Response($this, $statusCode, $headers, $body, $options);

    }

}