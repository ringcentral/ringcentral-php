<?php

namespace RC\ajax;

use Exception;

class Ajax
{

    /** @var Request */
    protected $request = null;

    /** @var Response */
    protected $response = null;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * @return $this
     * @throws AjaxException
     */
    public function send()
    {

        $ch = curl_init();

        try {

            curl_setopt($ch, CURLOPT_URL, $this->request->getUrlWithQueryString());

            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $this->request->getMethod());
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HEADER, true);

            curl_setopt($ch, CURLOPT_HTTPHEADER, $this->request->getHeadersArray());

            if ($this->request->isPut() || $this->request->isPost()) {
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $this->request->getEncodedBody());
            }

            $response = curl_exec($ch);
            $this->response = new Response(curl_getinfo($ch, CURLINFO_HTTP_CODE), $response);

        } catch (Exception $e) {

            curl_close($ch);
            throw new AjaxException($this, $e);

        }

        curl_close($ch);

        if (!$this->response->checkStatus()) {
            throw new AjaxException($this);
        }

        return $this;

    }

    public function isLoaded()
    {
        return !!$this->response;
    }

    public function getResponse()
    {
        return $this->response;
    }

    public function getRequest()
    {
        return $this->request;
    }

}