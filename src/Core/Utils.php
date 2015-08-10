<?php

namespace RingCentral\SDK\Core;

use InvalidArgumentException;

class Utils
{

    static function json_parse($json, $assoc = false, $depth = 512)
    {

        $parsed = \json_decode($json, $assoc, $depth);

        $error = json_last_error();

        switch ($error) {
            case JSON_ERROR_NONE:
                break;
            case JSON_ERROR_DEPTH:
                throw new InvalidArgumentException('JSON Error: Maximum stack depth exceeded');
                break;
            case JSON_ERROR_CTRL_CHAR:
                throw new InvalidArgumentException('JSON Error: Unexpected control character found');
                break;
            case JSON_ERROR_SYNTAX:
                throw new InvalidArgumentException('JSON Error: Syntax error, malformed JSON');
                break;
            default:
                throw new InvalidArgumentException('JSON Error: Unknown error');
                break;
        }

        // This is a courtesy by PHP JSON parser to parse "null" into null, but this is an error situation
        if (empty($parsed)) {
            throw new InvalidArgumentException('JSON Error: Result is empty after parsing');
        }

        return $parsed;

    }

}

