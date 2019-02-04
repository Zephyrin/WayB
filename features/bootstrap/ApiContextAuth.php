<?php

use Imbo\BehatApiExtension\Context;

class ApiContextAuth extends Context\ApiContext
{
    protected $token;
    public function getTokenFromLogin()
    {
        $this->requireResponse();
        $body = $this->getResponseBody();
        $this->token = $body->token;
    }

    public function requestPath($path, $method = null)
    {
        $this->setRequestHeader("Authorization", "Bearer {$this->token}");

        return parent::requestPath($path, $method);
    }

    public function removeAuthorization()
    {
        $this->setRequestHeader('Authorization', '');
    }
}