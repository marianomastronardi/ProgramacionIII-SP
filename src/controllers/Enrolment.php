<?php

namespace App\Controllers;

use App\Models\Alumno;
use App\Models\Enrolment;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UploadedFileInterface;
use DI\ContainerBuilder;
use Seguridad\iToken;
use App\Models\Subject;
use App\Models\User;


class EnrolmentController
{

    public function add(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        //$email = $body['email']??'';
        $id_materia = $args['idMateria'] ?? '';

        //if(strlen($email) > 0){
        if (strlen($id_materia) > 0) {
            $arr = $request->getHeader('token');
            if (count($arr) > 0) $token = $arr[0];
            $jwt = isset($token) ? iToken::decodeUserToken($token) : false; // VALIDAR EL TOKEN

            if ($jwt) {
                $user = User::find($jwt["email"]);
                if ($user->tipo_usuario == 'alumno') {

                    $cupos = Subject::find($id_materia);
                    $inscriptos = Enrolment::where('materia_id', $id_materia)->count();
                    if ($cupos->cupos <= $inscriptos) {
                        $response->getBody()->write(json_encode(array('message' => 'Sin vacantes!!')));
                    } else {
                        $alumno = Alumno::select('id')->where('email', $jwt["email"])->get();
                        $enrolment = new Enrolment();
                        $enrolment->alumno_id = $alumno[0]["id"];
                        $enrolment->materia_id = $id_materia;

                        $enrolment->save();

                        $response->getBody()->write(json_encode(array('message' => 'Enrolment has been saved!!')));
                    }
                } else {
                    $response->getBody()->write(json_encode(array('message' => 'Debe ser alumno para poder inscribirse')));
                }
            }
        } else {
            $response->getBody()->write(json_encode(array('message' => 'Debe cargar la materia')));
        }
        //}else{
        //    $response->getBody()->write(json_encode(array('message' => 'Debe cargar el email')));
        //}

        return $response;
    }

    public function setNote(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $body = $request->getParsedBody();
        $nota = $body['nota'] ?? '';
        $alumno = $body['alumno'] ?? '';
        $materia = $args['idMateria'];

        $enrolment = Enrolment::select('id')->where('materia_id', $materia)->where('alumno_id', $alumno)->get();

        $enrolment->update(['nota' => $nota]);
        $response->getBody()->write(json_encode(array('message' => 'Note has been saved')));

        return $response;
    }

    public function getOne(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {

        $arr = $request->getHeader('token');
        if (count($arr) > 0) $token = $arr[0];
        $jwt = isset($token) ? iToken::decodeUserToken($token) : false; // VALIDAR EL TOKEN

        if ($jwt['email']) {
            $user = User::find($jwt['email']);

            if ($user->tipo != 'alumno') {

                $materia = $args['idMateria'];

                $enrolment = Enrolment::select()
                    ->join('subjects', 'subjects.id', '=', 'enrolments.materia_id')
                    ->join('alumnos', 'alumnos.id', '=', 'enrolments.alumno_id')
                    ->where('materia_id', $materia)->get();

                $response->getBody()->write(json_encode($enrolment));
            } else {
                $response->getBody()->write(json_encode(array('message' => 'You must be teacher or admin to get your subjects')));
            }
        } else {
            $response->getBody()->write(json_encode(array('message' => 'You are not logged')));
        }

        $response->getBody()->write(json_encode($enrolment));

        return $response;
    }

    public function getNotes(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $materia = $args['idMateria'];

        $enrolment = Enrolment::select('enrolments.nota')
            ->join('subjects', 'subjects.id', '=', 'enrolments.materia_id')
            ->join('alumnos', 'alumnos.id', '=', 'enrolments.alumno_id')
            ->where('materia_id', $materia)->get();

        $response->getBody()->write(json_encode($enrolment));

        return $response;
    }
}
