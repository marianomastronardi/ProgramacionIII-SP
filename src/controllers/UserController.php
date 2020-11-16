<?php

namespace App\Controllers;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UploadedFileInterface;
use DI\ContainerBuilder;
use Seguridad\iToken;
use App\Models\User;


class UserController
{

    public function signup(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {

        try {
            $body = $request->getParsedBody();
            $count = User::where('email', $body["email"])->count();

            if ($count > 0) {
                $response->getBody()->write(json_encode(array("rta" => "User is already exists")));
            } else {
                $user = new User;
                $user->email = $body["email"];
                $user->tipo_usuario = $body["tipo_usuario"];
                $user->password = password_hash($body["password"], PASSWORD_BCRYPT);
                $user->save();
                $response->getBody()->write(json_encode(array("rta" => "User has been saved successfuly")));
            }
            return $response;
        } catch (\Illuminate\Database\QueryException $e) {
            $error_code = $e->errorInfo[1];
            $response->getBody()->write((string)$error_code);
            return $response;
        } catch (\Throwable $th) {
            $response->getBody()->write($th->getMessage());
            return $response;
        }
    }

    public function LogIn(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        try {
            $body = $request->getParsedBody();
            $email = $body["email"] ?? '';
            if (strlen($email) > 0) {
                $user = User::where('email',$email)->get();
                //var_dump($user[0]['tipo_usuario']);
                if ($user == null) {
                    $response->getBody()->write(json_encode(array('message' => "Wrong email")));
                } else {
                    $password = $body["password"] ?? '';
                    if (strlen($password) > 0) {
                        $token = iToken::encodeUserToken($email, $password, $user[0]['tipo_usuario']);
                        if (isset($token)){
                            $response->getBody()->write(json_encode($token));
                        }else{
                            $response->getBody()->write(json_encode(array("error" => "Something goes wrong. Please, check your credentials")));
                        }
                    }else{
                        $response->getBody()->write(json_encode(array("error" => "You must set a password")));
                    }
                }
            } else {
                $response->getBody()->write(json_encode(array("error" => "You must set an email")));
            }
            
            return $response;
        } catch (\Throwable $th) {
            $response->getBody()->write($th->getMessage());
            return $response;
        }
    }

    public function add(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        try {
            $body = $request->getParsedBody();
            $email = $body["email"] ?? '';

            if (strlen($email) > 0) {
                $user = User::find($email);
                if (!$user) {
                    $password = $body["password"] ?? '';
                    if (strlen($password)) {
                        $password = password_hash($password, PASSWORD_BCRYPT);
                        $tipo = $body["tipo"] ?? '';
                        if (strlen($tipo) == 0) {
                            $response->getBody()->write(json_encode(array('message' => "Debe ingresar el tipo de usuario")));
                            return $response;
                        }
                    } else {
                        $response->getBody()->write(json_encode(array("error" => "Debe ingresar la contraseña")));
                        return $response;
                    }
                } else {
                    $response->getBody()->write(json_encode(array("error" => "Usuario ya existente")));
                    return $response;
                }
            } else {
                $response->getBody()->write(json_encode(array("error" => "Debe ingresar el email")));
                return $response;
            }
            $user = new User();
            $user->email = $email;
            $user->password = $password;
            $user->tipo = $tipo;

            //legajo
            $find = true;
            do {
                $user->legajo = rand(100000, 999999);
                $legajo = User::where('legajo', $user->legajo);
                if (!isset($legajo->legajo))  $find = false;
            } while ($find);
            //var_dump($user);
            //die();
            $user->save();
            $response->getBody()->write(json_encode($user));
            return $response;
        } catch (\Throwable $th) {
            $response->getBody()->write(json_encode($th));
            return $response;
        }
    }

    public function setEntity(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        try {
            $user = User::where('legajo', $args["legajo"]);
            if (isset($user->legajo)) {
                switch ($user->tipo) {
                    case 'admin':
                        echo 'admin';
                        break;
                    case 'alumno':
                        echo 'alumno';
                        break;
                    case 'profesor':
                        echo 'profesor';
                    default:
                        echo 'tipo no existe';
                        break;
                }
            } else {
                $response->getBody()->write(json_encode(array('message' => 'Legajo inexistente')));
            }
            $response->getBody()->write(json_encode($user));
            return $response;
        } catch (\Throwable $th) {
            $response->getBody()->write(json_encode($th));
            return $response;
        }
    }
}
