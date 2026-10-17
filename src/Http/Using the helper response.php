<?php

use RedSky\Framework\Http\Response;

/*
|--------------------------------------------------------------------------
| Response Helper Examples
|--------------------------------------------------------------------------
*/

// Create a generic response.
return response()->make('Hello World', 200);

// Create a JSON response.
return response()->json([
    'success' => true,
    'message' => 'Operation completed successfully.',
]);

// Create a JSON response with a custom status code and headers.
return response()->json(
    ['message' => 'Resource created.'],
    201,
    ['X-Custom-Header' => 'RedSky']
);

// Create a text response.
return response()->text('Plain text response');

// Create an empty response with HTTP 204.
return response()->noContent();

// Create a successful response with data.
return response()->ok([
    'id' => 1,
    'name' => 'John Doe',
]);

// Create a successful response with a custom message.
return response()->ok(
    ['id' => 1],
    'User retrieved successfully.'
);

// Create a resource creation response with HTTP 201.
return response()->created([
    'id' => 10,
    'name' => 'Jane Doe',
]);

// Create an error response.
return response()->error('Resource not found', 404);

// Create an error response with additional details.
return response()->error(
    'The request could not be processed.',
    400,
    ['field' => 'Invalid value.']
);

// Create a validation error response with HTTP 422.
return response()->validationError([
    'email' => ['The email field is required.'],
    'password' => ['The password must contain at least 8 characters.'],
]);

// Create an unauthorized response with HTTP 401.
return response()->unauthorized('Authentication is required.');

// Create a redirect response.
return response()->redirect('/users');

// Create a redirect response with a custom status code.
return response()->redirect('/login', 303);

// The original static Response API remains available.
return Response::json(['message' => 'Static response example']);