<?php

namespace App\OpenApi;

/**
 * @OA\Info(
 *     title="Cloud Storage API",
 *     version="1.0.0",
 *     description="OpenAPI documentation for the Cloud Storage backend."
 * )
 *
 * @OA\Server(
 *     url=L5_SWAGGER_CONST_HOST,
 *     description="API server"
 * )
 *
 * @OA\SecurityScheme(
 *     securityScheme="bearerAuth",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT"
 * )
 *
 * @OA\Get(
 *     path="/api/health",
 *     summary="Health check",
 *     tags={"Health"},
 *     @OA\Response(response=200, description="OK")
 * )
 */
class OpenApi
{
}


