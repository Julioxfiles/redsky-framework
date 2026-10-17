<?php

use RedSky\Framework\Http\Response;

// Create a generic response.
return Response::make('Hello World', 200);

// Create a JSON response.
return Response::json([
    'success' => true,
    'message' => 'Operation completed successfully.',
]);

// Create a JSON response with a custom status code and headers.
return Response::json(
    ['message' => 'Resource created.'],
    201,
    ['X-Custom-Header' => 'RedSky']
);

// Create a text response.
return Response::text('Plain text response');

// Create an empty response with HTTP 204.
return Response::noContent();

// Create a successful response with data.
return Response::ok([
    'id' => 1,
    'name' => 'John Doe',
]);

// Create a successful response with a custom message.
return Response::ok(
    ['id' => 1],
    'User retrieved successfully.'
);

// Create a resource creation response with HTTP 201.
return Response::created([
    'id' => 10,
    'name' => 'Jane Doe',
]);

// Create an error response.
return Response::error('Resource not found', 404);

// Create an error response with additional details.
return Response::error(
    'The request could not be processed.',
    400,
    ['field' => 'Invalid value.']
);

// Create a validation error response with HTTP 422.
return Response::validationError([
    'email' => ['The email field is required.'],
    'password' => ['The password must contain at least 8 characters.'],
]);

// Create an unauthorized response with HTTP 401.
return Response::unauthorized('Authentication is required.');

// Create a redirect response.
return Response::redirect('/users');

// Create a redirect response with a custom status code.
return Response::redirect('/login', 303);

// Modify a response instance.
$response = Response::make('Original content')
    ->setStatus(202)
    ->setHeader('X-Custom-Header', 'RedSky');

// Add headers using fluent methods.
$response = Response::json(['success' => true])
    ->withHeader('X-Application', 'RedSky')
    ->withCors();

return $response;