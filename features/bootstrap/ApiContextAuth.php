<?php

use Imbo\BehatApiExtension\Context;

class ApiContextAuth extends Context\ApiContext
{
    protected $token;
    public function getTokenFromLogin()
    {
        $this->token = '';
        $this->requireResponse();
        $body = $this->getResponseBody();
        if(isset($body->token))
        $this->token = "Bearer $body->token";
    }

    public function requestPath($path, $method = null)
    {
        $this->setRequestHeader("Authorization", "{$this->token}");

        return parent::requestPath($path, $method);
    }

    public function logout()
    {
        $this->setRequestHeader('Authorization', '');
    }
}