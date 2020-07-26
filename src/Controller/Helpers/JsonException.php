<?php

namespace App\Controller\Helpers;

use Exception;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * An exception that carry a JsonResponse to avoid some test.
 * The carried JsonResponse should be sent to the client who ask.
 */
class JsonException extends Exception
{
    /**
     * The response to sent to the client.
     *
     * @var JsonResponse
     */
    private $error;

    /**
     * Constructor
     *
     * @param JsonResponse $err
     */
    public function __construct(JsonResponse $err)
    {
        $this->error = $err;
        parent::__construct("JsonException", 1, null);
    }

    /**
     * Display the exception
     *
     * @return string
     */
    public function __toString()
    {
        return __CLASS__ . ": [{$this->code}]: {$this->message}\n";
    }

    /**
     * Return the error to sent to the client.
     * 
     * @return JsonResponse
     */
    public function jsonResponse(): JsonResponse
    {
        return $this->error;
    }
}
