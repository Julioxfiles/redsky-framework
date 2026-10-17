<?php

declare(strict_types=1);

namespace RedSky\Framework\Http;

/**
 * Creates HTTP responses through a convenient instance-based API.
 *
 * @package RedSky\Framework\Http
 */
class ResponseFactory
{
    /**
     * Create a response.
     *
     * @param mixed $body Response body.
     * @param int $status HTTP status code.
     * @param array $headers Response headers.
     *
     * @return Response
     */
    public function make(
        mixed $body = null,
        int $status = 200,
        array $headers = []
    ): Response {
        return Response::make($body, $status, $headers);
    }

    /**
     * Create a JSON response.
     *
     * @param mixed $data Data to encode as JSON.
     * @param int $status HTTP status code.
     * @param array $headers Response headers.
     *
     * @return Response
     */
    public function json(
        mixed $data,
        int $status = 200,
        array $headers = []
    ): Response {
        return Response::json($data, $status, $headers);
    }

    /**
     * Create a text response.
     *
     * @param string $content Response content.
     * @param int $status HTTP status code.
     * @param array $headers Response headers.
     *
     * @return Response
     */
    public function text(
        string $content,
        int $status = 200,
        array $headers = []
    ): Response {
        return Response::text($content, $status, $headers);
    }

    /**
     * Create an empty response with HTTP 204 status.
     *
     * @return Response
     */
    public function noContent(): Response
    {
        return Response::noContent();
    }

    /**
     * Create a successful JSON response.
     *
     * @param mixed $data Response data.
     * @param string $message Success message.
     *
     * @return Response
     */
    public function ok(
        mixed $data = null,
        string $message = 'OK'
    ): Response {
        return Response::ok($data, $message);
    }

    /**
     * Create a JSON response with HTTP 201 status.
     *
     * @param mixed $data Response data.
     * @param string $message Success message.
     *
     * @return Response
     */
    public function created(
        mixed $data = null,
        string $message = 'Created'
    ): Response {
        return Response::created($data, $message);
    }

    /**
     * Create an error response.
     *
     * @param string $message Error message.
     * @param int $status HTTP status code.
     * @param array $errors Error details.
     *
     * @return Response
     */
    public function error(
        string $message,
        int $status = 400,
        array $errors = []
    ): Response {
        return Response::error($message, $status, $errors);
    }

    /**
     * Create a validation error response.
     *
     * @param array $errors Validation errors.
     * @param string $message Error message.
     *
     * @return Response
     */
    public function validationError(
        array $errors,
        string $message = 'Validation failed'
    ): Response {
        return Response::validationError($errors, $message);
    }

    /**
     * Create an unauthorized response.
     *
     * @param string $message Error message.
     *
     * @return Response
     */
    public function unauthorized(
        string $message = 'Unauthorized'
    ): Response {
        return Response::unauthorized($message);
    }

    /**
     * Create a redirect response.
     *
     * @param string $to Redirect destination.
     * @param int $status HTTP status code.
     *
     * @return Response
     */
    public function redirect(
        string $to,
        int $status = 302
    ): Response {
        return Response::redirect($to, $status);
    }
    
}