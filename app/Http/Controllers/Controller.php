<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use OpenApi\Annotations as OA;

/**
 * @OA\Info(
 * version="1.0.0",
 * title="API de Tasks Backend",
 * description="Descripción de la API"
 * )
 * @OA\Server(
 * url=L5_SWAGGER_CONST_HOST,
 * description="Servidor API Principal"
 * )
 *
 * @OA\SecurityScheme(
 * type="http",
 * description="Token Bearer de Sanctum",
 * name="Bearer Token",
 * in="header",
 * scheme="bearer",
 * bearerFormat="JWT",
 * securityScheme="bearerAuth"
 * )
 */
class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;
}
