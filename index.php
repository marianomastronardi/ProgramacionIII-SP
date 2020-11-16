<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Log\LoggerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Routing\RouteCollectorProxy;
use Seguridad\Usuario;
use Slim\Factory\AppFactory;
use Config\Database;
use Illuminate\Container\Container;
use \Firebase\JWT\JWT;
use Slim\Exception\HttpNotFoundException;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Middleware\ErrorMiddleware;
use Psr\Http\Message\UploadedFileInterface;
//Controller
use App\Controllers\UserController;
//Middleware
use App\Middlewares\JsonMiddleware;
use App\Middlewares\AuthMiddleware;
use App\Middlewares\UserMiddleware;

require __DIR__ . '/vendor/autoload.php';
$container = new Container();
AppFactory::setContainer($container);
$app = AppFactory::create();
$app->addRoutingMiddleware();

$app->setBasePath("/ProgramacionIII-SP");
new Database();

$app->post('/registro', UserController::class . ":signup")
            ->add(new JsonMiddleware); //1

$app->post('/login', UserController::class . ":LogIn")->add(new JsonMiddleware); //2
/*
$app->post('/tipo_mascota', MascotaController::class . ":addPetType")
            ->add(new JsonMiddleware)
            ->add(new AuthMiddleware)
            ->add(new UserMiddleware); //3

$app->post('/mascota', MascotaController::class . ":add")
            ->add(new JsonMiddleware)
            ->add(new AuthMiddleware); //4

 $app->group('/turnos', function (RouteCollectorProxy $group) {
    $group->post('/mascota', TurnoController::class . ":add"); //5
    $group->get('/{id_usuario}', TurnoController::class . ":getByUserId");  //6 //7 //8
    $group->get('/mascota/{id_mascota}', TurnoController::class . ":getByPet"); //9
})->add(new JsonMiddleware)->add(new AuthMiddleware);  
*/
$app->run(); 
