<?php

namespace RC\http;

use Exception;

class HttpException extends Exception
{

    protected $request = null;

    public function __construct(Request $request = null, Exception $previous = null)
    {

        $message = 'Unknown error';
        $status = 0;

        $this->request = $request;

        if ($request && $request->isLoaded()) {

            $data = $request->getResponse()->getData();
            $status = $request->getResponse()->getStatus();

            if (!empty($data->message)) {
                $message = $data->message;
            }

            if (!empty($data->error_description)) {
                $message = $data->error_description;
            }

            if (!empty($data->description)) {
                $message = $data->description;
            }

            //TODO Add status text

        }

        if ($previous) {
            $message = $previous->getMessage();
        }

        parent::__construct($message, $status, $previous);

    }

    public function getRequest()
    {
        return $this->request;
    }

}