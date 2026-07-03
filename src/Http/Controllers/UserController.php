<?php

namespace RedSky\Http\Controllers;

use RedSky\Framework\Http\Request;
use RedSky\Framework\Http\Response;
use RedSky\Framework\Support\Validator;
use RedSky\Framework\Support\JwtService;
use App\Models\User;

class UserController
{
    public function index()
    {
        try {

            $users = User::all();

            return Response::json([
                "status" => "success",
                "data" => $users
            ], 200);

        } catch (\Throwable $e) {

            return Response::json([
                "status" => "error",
                "message" => "Error al obtener usuarios"
            ], 500);
        }
    }

    public function show($id)
    {
        try {

            $user = User::find($id);

            if (!$user) {
                return Response::json([
                    "status" => "error",
                    "message" => "Usuario no encontrado"
                ], 404);
            }

            return Response::json([
                "status" => "success",
                "data" => $user
            ], 200);

        } catch (\Throwable $e) {

            return Response::json([
                "status" => "error",
                "message" => "Error al obtener usuario"
            ], 500);
        }
    }

    public function store()
    {
        try {

            $request = new Request();
            $data = $request->all();

            $errors = Validator::user($data);

            if (!empty($errors)) {
                return Response::json([
                    "status" => "error",
                    "message" => "Errores de validación",
                    "errors" => $errors
                ], 422);
            }

            $id = User::create($data);

            return Response::json([
                "status" => "success",
                "message" => "Usuario creado",
                "id" => $id
            ], 201);

        } catch (\Throwable $e) {

            return Response::json([
                "status" => "error",
                "message" => "Error al crear usuario"
            ], 500);
        }
    }

    public function update($id)
    {
        try {

            $request = new Request();
            $data = $request->all();

            $user = User::find($id);

            if (!$user) {
                return Response::json([
                    "status" => "error",
                    "message" => "Usuario no encontrado"
                ], 404);
            }

            $errors = Validator::user($data);

            if (!empty($errors)) {
                return Response::json([
                    "status" => "error",
                    "message" => "Errores de validación",
                    "errors" => $errors
                ], 422);
            }

            User::update($id, $data);

            return Response::json([
                "status" => "success",
                "message" => "Usuario actualizado"
            ], 200);

        } catch (\Throwable $e) {

            return Response::json([
                "status" => "error",
                "message" => "Error al actualizar usuario"
            ], 500);
        }
    }

    public function delete($id)
    {
        try {

            $user = User::find($id);

            if (!$user) {
                return Response::json([
                    "status" => "error",
                    "message" => "Usuario no encontrado"
                ], 404);
            }

            User::delete($id);

            return Response::json([
                "status" => "success",
                "message" => "Usuario eliminado"
            ], 200);

        } catch (\Throwable $e) {

            return Response::json([
                "status" => "error",
                "message" => "Error al eliminar usuario"
            ], 500);
        }
    }

    public function login()
    {
        try {

            $data = (new Request())->all();

            if (
                empty($data["email"]) ||
                empty($data["password"])
            ) {
                return Response::json([
                    "status" => "error",
                    "message" => "Errores de validación",
                    "errors" => [
                        "email" => ["El email es obligatorio"],
                        "password" => ["El password es obligatorio"]
                    ]
                ], 422);
            }

            $users = User::where("email", $data["email"]);
            $user = $users[0] ?? null;

            if (!$user) {
                return Response::json([
                    "status" => "error",
                    "message" => "Usuario no encontrado"
                ], 404);
            }

            if ($data["password"] !== $user["password"]) {
                return Response::json([
                    "status" => "error",
                    "message" => "Credenciales incorrectas"
                ], 401);
            }

            $token = (new JwtService())->generate($user);

            return Response::json([
                "status" => "success",
                "message" => "Login exitoso",
                "data" => [
                    "token" => $token,
                    "user" => [
                        "id" => $user["id"],
                        "nombre" => $user["nombre"],
                        "email" => $user["email"]
                    ]
                ]
            ], 200);

        } catch (\Throwable $e) {

            return Response::json([
                "status" => "error",
                "message" => "Error interno del servidor"
            ], 500);
        }
    }

}