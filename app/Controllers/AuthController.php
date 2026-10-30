<?php

namespace App\Controllers;

/**
 * Controlador Auth (Alias alternativo a LoginController)
 * Permite usar rutas como /auth/login y /auth/logout
 */
class AuthController extends LoginController
{
    public function login(): void
    {
        $this->index();
    }
}
