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
            $email = $body["email"] ?? '';
            $password = $body["clave"] ?? '';
            $tipoUsuario = $body["tipo"] ?? '';
            $nombre = $body["nombre"] ?? '';

            if (strlen($email) == 0) {
                $response->getBody()->write(json_encode(array("message" => "Email is required")));
            } else {
                if (strlen($password) == 0) {
                    $response->getBody()->write(json_encode(array("message" => "Password is required")));
                } else {
                    if (strlen($tipoUsuario) == 0) {
                        $response->getBody()->write(json_encode(array("message" => "User Type is required")));
                    } else {
                        if (strlen($nombre) == 0) {
                            $response->getBody()->write(json_encode(array("message" => "Name is required")));
                        } else {
                            $count = User::where('email', $body["email"])->count();

                            if ($count > 0) {
                                $response->getBody()->write(json_encode(array("rta" => "User is already exists")));
                            } else {
                                $user = new User;
                                $user->email = $email;
                                $user->tipo_usuario = $tipoUsuario;
                                $user->password = password_hash($password, PASSWORD_BCRYPT);
                                $user->nombre = $nombre;
                                $user->save();
                                $response->getBody()->write(json_encode(array("rta" => "User has been saved successfuly")));
                            }
                        }
                    }
                }
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
            $nombre = $body["nombre"] ?? '';
            if (strlen($email) > 0 || strlen($nombre) > 0) {
                if (strlen($email) > 0) {
                    $user = User::find($email);
                } else {
                    $user = User::where('nombre', $nombre)->get();
                }

                if (!isset($user->email)) {
                    $response->getBody()->write(json_encode(array('message' => "Wrong email")));
                } else {
                    if (strlen($email) == 0) $email = $user->email;
                    $password = $body["clave"] ?? '';
                    if (strlen($password) > 0) {
                        if (!password_verify($password, $user->password)) {
                            $response->getBody()->write(json_encode(array('message' => "Wrong Password")));
                        } else {
                            $token = iToken::encodeUserToken($email, $password, $user->tipo_usuario);
                            if (isset($token)) {
                                $response->getBody()->write(json_encode($token));
                            } else {
                                $response->getBody()->write(json_encode(array("error" => "Something goes wrong. Please, check your credentials")));
                            }
                        }
                    } else {
                        $response->getBody()->write(json_encode(array("error" => "You must set a password")));
                    }
                }
            } else {
                $response->getBody()->write(json_encode(array("error" => "You must set an email or name")));
            }

            return $response;
        } catch (\Throwable $th) {
            $response->getBody()->write($th->getMessage());
            return $response;
        }
    }
}
