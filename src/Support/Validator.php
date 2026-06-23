<?php

namespace Redsky\Framework\Support;

class Validator
{
    public static function user(array $data): array
    {
        $errors = [];

        if (empty(trim($data["nombre"] ?? ""))) {
            $errors["nombre"][] = "El nombre es obligatorio";
        }

        if (empty(trim($data["email"] ?? ""))) {
            $errors["email"][] = "El email es obligatorio";
        }

        if (
            !empty($data["email"]) &&
            !filter_var($data["email"], FILTER_VALIDATE_EMAIL)
        ) {
            $errors["email"][] = "El email no es válido";
        }

        if (
            isset($data["edad"]) &&
            (!is_numeric($data["edad"]) || $data["edad"] < 0)
        ) {
            $errors["edad"][] = "La edad debe ser un número válido";
        }

        if (
            isset($data["ciudad"]) &&
            trim($data["ciudad"]) === ""
        ) {
            $errors["ciudad"][] = "La ciudad no puede estar vacía";
        }

        return $errors;
    }
}
