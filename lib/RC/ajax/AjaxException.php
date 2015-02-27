<?php

namespace RC\ajax;

use Exception;

class AjaxException extends Exception
{

    protected $ajax = null;

    public function __construct(Ajax $ajax, Exception $previous = null)
    {

        $message = 'Unknown error';
        $status = 0;

        $this->ajax = $ajax;

        if ($ajax->isLoaded()) {

            $data = $ajax->getResponse()->getData();
            $status = $ajax->getResponse()->getStatus();

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

    public function getAjax()
    {
        return $this->ajax;
    }

}