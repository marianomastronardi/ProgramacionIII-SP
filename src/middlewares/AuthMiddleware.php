<?php

namespace App\Middlewares;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response;
use Seguridad\iToken;

class AuthMiddleware {

    public function __invoke (Request $request, RequestHandler $handler) {

        $arr = $request->getHeader('token');
         if(count($arr) > 0) $token = $arr[0];
        $jwt = isset($token) ? iToken::decodeUserToken($token) : false; // VALIDAR EL TOKEN

        //$jwt = true; // VALIDAR EL TOKEN

        if (!$jwt) {
            $response = new Response();

            $rta = array("rta" => "Not logged user or signature invalid");

            $response->getBody()->write(json_encode($rta));

            return $response->withStatus(403);
        } else {
            $response = $handler->handle($request);
            $existingContent = (string) $response->getBody();

            $resp = new Response();
            $resp->getBody()->write($existingContent);

            return $resp;
        }

    }
}