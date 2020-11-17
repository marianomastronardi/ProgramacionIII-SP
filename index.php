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
use App\Controllers\SubjectController;
use App\Controllers\EnrolmentController;
//Middleware
use App\Middlewares\JsonMiddleware;
use App\Middlewares\AuthMiddleware;
use App\Middlewares\UserMiddleware;
use App\Middlewares\ProfesorMiddleware;
use App\Middlewares\AlumnoMiddleware;

require __DIR__ . '/vendor/autoload.php';
$container = new Container();
AppFactory::setContainer($container);
$app = AppFactory::create();
$app->addRoutingMiddleware();

$app->setBasePath("/ProgramacionIII-SP");
new Database();

$app->post('/users', UserController::class . ":signup")
    ->add(new JsonMiddleware); //1

$app->post('/login', UserController::class . ":LogIn")->add(new JsonMiddleware); //2

$app->group('/inscripcion', function (RouteCollectorProxy $group) {
    $group->post('/{idMateria}', EnrolmentController::class . ":add")->add(new AlumnoMiddleware); //4
    $group->get('/{idMateria}', EnrolmentController::class . ":getOne");  //6
})->add(new JsonMiddleware)->add(new AuthMiddleware);

$app->group('/notas/{idMateria}', function (RouteCollectorProxy $group) {
    $group->put('', EnrolmentController::class . ":setNote")->add(new ProfesorMiddleware); //5
    $group->get('', EnrolmentController::class . ":getNotes"); //9
})->add(new JsonMiddleware)->add(new AuthMiddleware);

$app->group('/materia', function (RouteCollectorProxy $group) {
    $group->post('', SubjectController::class . ":add")->add(new UserMiddleware); //3
    $group->get('', SubjectController::class . ":getAll"); //7
})->add(new JsonMiddleware)->add(new AuthMiddleware);

$app->run();
