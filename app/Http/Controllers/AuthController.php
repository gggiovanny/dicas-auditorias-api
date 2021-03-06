<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Firebase\JWT\JWT;
use Datetime, DatetimeZone, DateInterval;

class AuthController extends Controller
{
    private static $key = 'EW-LTW2%YSzQ#Knf+P*FnYnh&9rKt77X9';
    private static $token_expire_delay = 'P5D'; //tiempo que durará valido el token a partir de su creacion
    #private static $token_expire_delay = 'PT3H';
    private static $encryption = ['HS256'];

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if(is_null($request->input('check_login'))) {
            return $this->getToken($request->input('user'), $passwdRequested = $request->input('passwd'));        
        } else {
            self::validateCredentials($request);
            return self::loginSucess();
        }
        
    }

    /**
     * Verifica si el token proporcionado es valido.
     *
     * @return \Illuminate\Http\Response
     */
    public static function validateCredentials(Request $request)
    {
        try {
            $token = $request->input('token');
            $permisionCheck = self::checkToken($token);
            if($permisionCheck['status'] !== 'ok' ) {
                exit(response()->json($permisionCheck)->content()); //mostrar errorLogin
            } 
        } catch (\Exception $th) {
             self::errorExitLogin($th->getMessage());
        }
    }

    /**
     * Obtiene la id del usuario a partir del token usado para la solicitud
     *
     * @return int
     */
    public static function getUserFromToken($token)
    {
        try {
            $decode = JWT::decode($token, self::$key, self::$encryption);
        } catch (\Exception $e) {
            return 0;
        }

        if($decode) {
            return $decode->data->id;
        } else {
            return 0;
        }
    }

    private static function checkToken($jwtToken)
    {
        if(empty($jwtToken)) {
            return self::errorLogin('Token inválido');
        }

        try {
            $decode = JWT::decode($jwtToken, self::$key, self::$encryption);
        } catch (\Exception $e){
            return self::errorLogin($e->getMessage());
        }

        if($decode->cid !== self::clientID()) {
            return self::errorLogin('Dispositivo no válido para este token. Intente iniciar sesión nuevamente.');
        }

        return self::okPartialLogin('Token valido o eres todo un hackerman 7u7');
    }

    private function getToken($userRequested, $passwdRequested)
    {
        $usuario_correcto = false;
        $userID = '';
        $userName = '';

        $users = User::all();
        foreach($users as $user) {
            if(($user->username === $userRequested || $user->email === $userRequested) && $user->password === md5($passwdRequested)) {
                $usuario_correcto = true;
                $userID = $user->id;
                $userName = $user->username;
                break;
            }         
        }

        if($usuario_correcto) {
            $expire = new DateTime('now', new DateTimeZone('America/Mexico_City'));
            $expire->add(new DateInterval(self::$token_expire_delay));

            $token = array(
                    'exp' => $expire->getTimestamp(),
                    'cid' => self::clientID(),
                    'data' => [ //credenciales
                        'username' => $userRequested,
                        'id' => $userID
                    ]
                );

            $response = self::okPartialLogin('Token creado exitosamente!');
            $response +=['username' => $userName];
            $response +=['token' => JWT::encode($token, self::$key)];

            return $response;
            
        } else {
            return self::errorLogin('Usuario o contraseña incorrecto!');
        }
    }

    private static function clientID()
    {
        $clientID = '';
/*
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $clientID = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $clientID = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $clientID = $_SERVER['REMOTE_ADDR'];
        }

        $clientID .= gethostname();
        */
        $clientID .= @$_SERVER['HTTP_USER_AGENT'];
        return sha1($clientID);
    }
    
    

    

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        
        

    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
      
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
