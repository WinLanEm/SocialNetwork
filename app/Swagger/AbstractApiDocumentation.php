<?php

namespace App\Swagger;

/**
 * @OA\Info(
 *     title="My API",
 *     version="1.0.0",
 *     description="Документация к API"
 * )
 *
 * @OA\SecurityScheme(
 * securityScheme="sanctum",
 * type="apiKey",
 * in="cookie",
 * name="XSRF-TOKEN",
 * description="Sanctum cookie-based auth. Требуется авторизация через web, после чего браузер отправляет куки: laravel_session (сессия) и XSRF-TOKEN (csrf)."
 * )
 */
class AbstractApiDocumentation
{

}
