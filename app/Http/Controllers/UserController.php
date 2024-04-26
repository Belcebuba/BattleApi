<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Str;
use App\Mail\ActivationLinkEmail;
use Illuminate\Support\Facades\URL;
use PhpParser\Node\Stmt\TryCatch;

class UserController extends Controller
{
    public function store(Request $request)
    {
        $validator= Validator::make($request->all(),[
            'name'=>'required|max:100|string',
            'email'=>'required|max:255|string|email|unique:'.User::class,
            'password'=>'required|max:100|string',
            'isActive'=>'max:100|string',
        ]);
        if($validator->fails())
        {
            return response()->json($validator->errors(),400);
        }
        $user=User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'isActive'=>$request->isActive ?? 0
        ]);
        $token= Str::random(60);
        $user =User::where('email', $request->email)->first();
        $user->remember_token=$token;
        $user->save();

        $activationLink=URL::temporarySignedRoute('activarUsuario', now()->addHours(24),['token'=>$token]);
        Mail::raw($activationLink, function ($message) use($request){
            $message->to($request->email)->subject('codigo de ver verificacion');
        });
        
    }
    public function login(Request $request)
    {
        $credenciales = $request->only('email', 'password');
        
        try
        {
            if(!$token = JWTAuth::attempt($credenciales))
            {
                return response()->json([
                    'status' => 'error',
                    'message' => 'credenciales invalidas'
                ], 400);
            }
        }
        catch(JWTException $e)
        {
            return response()->json([
                'status' => 'error',
                'message' => 'token no existente'
            ], 500);
        }

        $user = JWTAuth::user();

        $user = User::where('email', $request->email)->first();
        $isActiver = $user->isActive;
        if($isActiver==1)
        {
        $codigo=rand(100000,999999);
        $user = User::where('email', $request->email)->first();
        $user->codigo =$codigo;
        $user->save();
        $contenidoCorreo="Su codigo de verificacion es:$codigo";
        Mail::raw($contenidoCorreo, function ($message) use($request){
            $message->to($request->email)->subject('codigo de ver verificacion');
        });
        return response()->json('correo electron enviado');
        }


    }
    public function sendEmail(Request $request)
    {
        $codigo=rand(100000,999999);
        $user = User::where('email', $request->email)->first();
        $user->codigo =$codigo;
        $user->save();
        $contenidoCorreo="Su codigo de verificacion es:$codigo";
        Mail::raw($contenidoCorreo, function ($message) use($request){
            $message->to($request->email)->subject('codigo de ver verificacion');
        });
        return response()->json('correo electron enviado');
    }
    public function codeCheck(Request $request)
    {
        try {
            $authenticatedUser = User::where('email', $request->email)->first();

            $validator = Validator::make($request->all(), [
                'email'=>'required|email',
                'codigo' => 'required|size:6|string',
            ]);
        
            if ($validator->fails()) {
                return response()->json($validator->errors(), 400);
            }    

            $codigo = $authenticatedUser->codigo;
            $codigoSolicitud = $request->codigo;

            if ($codigo != $codigoSolicitud) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'El código ingresado no coincide con el código del usuario'
                ], 400);
            } else {
                $authenticatedUser->codigo = null;
                $authenticatedUser->save();
                $token = JWTAuth::fromUser($authenticatedUser);
                return response()->json([
                    'status' => 'success',
                    'token' => $token],200);
            }
            
        } catch (\Exception $e) {
           
            return response()->json([
                'status' => 'error',
                'message' => 'Error en la verificación del código.',
                'error' => $e->getMessage()
            ], 418);
        }
    }
    public function linkac(Request $request)
    {
        $token= Str::random(60);
        $user =User::where('email', $request->email)->first();
        $user->remember_token=$token;
        $user->save();

        $activationLink=URL::temporarySignedRoute('activarUsuario', now()->addHours(24),['token'=>$token]);
        Mail::raw($activationLink, function ($message) use($request){
            $message->to($request->email)->subject('codigo de ver verificacion');
        });
    }
    public function activaruser($token)
    {
        $user= User::where('remember_token', $token)->first();
        if($user)
        {
            $user->isActive=1;
            $user->remember_token=null;
            $user->save();
            return response()->json('Usuario Activado Correctamente',200);
        }
        else
        {
            return response()->json('Token de activacion invalido',400);
        }
    }
    public function logout(Request $request)
    {
        try{
            $user= JWTAuth::user();
            JWTAuth::parseToken()->invalidate();
            return response()->json(['satus'=>'succes','message'=>'session cerrada correctamente'],200);
        }
        catch(JWTException $e)
        {
            return response()->json(['satus'=>'error','message'=>'Error al cerrar session'],400);
        }
    }

}
