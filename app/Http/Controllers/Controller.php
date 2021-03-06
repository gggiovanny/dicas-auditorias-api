<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use App\Http\Controllers\ResponseType;
use Symfony\Component\HttpFoundation\Response;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public static $PAGE_SIZE_DEFAULT = 25;
    public static $msgAuditoriaTerminadaRegistro = 'No se pueden actualizar existencias de una auditoria marcada como terminada.';
    public static $msgAuditoriaGuardada = 'No se pueden actualizar existencias de una auditoria finalizada y guardada.';
    
    public static function status($status, $description, $tipoRespuesta) {
        return array(
            'status' => $status,
            'description' => $description,
            'tipo' => $tipoRespuesta
        );
    }

    public static function warningNoExisteAuditoriaParaId($id, $max_id)
    {
        $max_id = $max_id ? $max_id : 0; //Por si la tabla esta vacio max_id es null
        $return = AuthController::status('warning', 'No existe auditoria para la ID '.$id.'. La mayor es '.$max_id, ResponseType::WARNING);
        $return +=['max_id' => $max_id];
        return Response($return, 209);
    }

    public static function warningSameAuditoriaInProgress($id)
    {
        $return = self::status('warning', 'Existe una auditoria igual a esta en progreso! Use esa en lugar de crear una nueva.', ResponseType::WARNING);
        $return += ['idAuditoria' => $id];
        return Response($return, 209);
    }

    public static function warningNoEntriesForThatLocation()
    {
        $return = self::status('warning', 'No existen activos fijos registrados para los filtros seleccionados. Seleccione otros filtros, o si está seguro que son los filtros correctos, solicite el alta de dichos activos en la localización correcta.', ResponseType::WARNING);
        return Response($return, 209);
    }

    public static function okPartialLogin($description)
    {
        return AuthController::status('ok', $description, ResponseType::GET);
    }

    public static function loginSucess()
    {
        $return = AuthController::status('ok', 'Inicio de sesión correcto!', ResponseType::GET);
        return Response($return, 200);
    }

    public static function queryOk($query)
    {
        $return = self::status('ok', 'Query correcto', ResponseType::GET);
        $return += ['list' => $query];
        return Response($return, 200);
    }

    public static function postIdOk($query)
    {
        $return = AuthController::status('ok', 'Entry sucessfuly created', ResponseType::POST);
        $return +=['id' => $query[0]->id];
        return Response($return, 200);
    }

    public static function queryEmpty()
    {
        $return = AuthController::status('ok', 'Query correcto, pero sin resultados!', ResponseType::GET);
        return Response($return, 204);
    }

    public static function postOk()
    {
        $return = AuthController::status('ok', 'Nuevo elemento creado exitosamente', ResponseType::POST);
        return Response($return, 201);
    }

    public static function putOk()
    {
        $return = AuthController::status('ok', 'Elemento actualizado exitosamente', ResponseType::PUT);
        return Response($return, 201);
    }

    public static function putOkPayload($payload)
    {
        $return = self::status('ok', 'Elemento actualizado exitosamente', ResponseType::PUT);
        $return += ['payload' => $payload];
        return Response($return, 201);
    }

    public static function deleteOk()
    {
        $return = AuthController::status('ok', 'Elemento borrado exitosamente', ResponseType::DELETE);
        return Response($return, 201);
    }

    public static function warningNoParameters()
    {
        $return = AuthController::status('warning', 'Faltan datos obligatorios!', ResponseType::WARNING);
        return Response($return, 209);
    }

    public static function warningNoSaved()
    {
        $return = AuthController::status('warning', 'No se pudo guardar!', ResponseType::WARNING);
        return Response($return, 209);
    }

    public static function warningEntryNoExist()
    {
        $return = AuthController::status('warning', 'No se pudo obtener el elemento solicitado!', ResponseType::WARNING);
        return Response($return, 209);
    }

    public static function warningAuditoriaGuardada()
    {
        $return = AuthController::status('warning', 'No se puede editar una auditoria marcada como guardada.', ResponseType::WARNING);
        return Response($return, 209);
    }   
    
    public static function warningAuditoriaCantDelete()
    {
        $return = AuthController::status('warning', 'Solo se pueden borrar auditorias sin elementos.', ResponseType::WARNING);
        return Response($return, 209);
    }  

    public static function warningAuditoriaNoTerminada()
    {
        $return = AuthController::status('warning', 'No se puede finalizar y guardar una auditoria no marcada como terminada.', ResponseType::WARNING);
        return Response($return, 209);
    }

    public static function warningAuditoriaActivosNoCompletados()
    {
        $return = AuthController::status('warning', 'Para marcar como terminada, tiene que validar todos los activos fijos de esta auditoria.', ResponseType::WARNING);
        return Response($return, 209);
    }
    
    public static function warningNoChanges()
    {
        $return = AuthController::status('warning', 'Sin cambios!', ResponseType::WARNING);
        return Response($return, 209);
    }

    public static function errorLogin($description)
    {
        switch($description) {
            case "Expired token":
                $description = "Sesion caducada";
            break;
        }
        return AuthController::status('error', $description, ResponseType::LOGIN_FAILED);
    }

    public static function errorExit($msg) {
        exit(response()->json(self::status('errorInternal', $msg, ResponseType::ERROR))->content());
    }

    public static function errorExitLogin($msg) {
        exit(response()->json(self::status('errorInternal', $msg, ResponseType::LOGIN_FAILED))->content());
    }

    public static function isTrue($string_container) {
        if (strpos($string_container, "\x01") !== false || $string_container == "1") {
            return true;
        } else {
            return false;
        }
    }

    public static function getCorrectBooleanStr($valor_raro) {
        if($valor_raro === "\x01") {
            return "1";
        }
        
        if($valor_raro === "\x00") {
            return "0";
        }

        return $valor_raro;
    }

    
}
