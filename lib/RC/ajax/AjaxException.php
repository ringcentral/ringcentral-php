<?php

namespace RC\ajax;

use Exception;

class AjaxException extends Exception
{

    protected $ajax = null;

    public function __construct(Ajax $ajax, Exception $previous = null)
    {

        $this->ajax = $ajax;

        $data = $ajax->getResponse()->getData();

        $message = 'Unknown error';

        if (!empty($data['message'])) {
            $message = $data['message'];
        }

        if (!empty($data['error_description'])) {
            $message = $data['error_description'];
        }

        if (!empty($data['description'])) {
            $message = $data['description'];
        }

        if ($previous) {
            $message = $previous->getMessage();
        }

        parent::__construct($message, $ajax->getResponse()->getStatus(), $previous);

    }

    public function getAjax()
    {
        return $this->ajax;
    }

}