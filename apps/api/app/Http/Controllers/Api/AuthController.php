<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
class AuthController extends Controller {
    public function login(LoginRequest $request) {
        $user=User::query()->where('email',$request->validated('username'))->first();
        if(!$user || !Hash::check($request->validated('password'),$user->password)) {
            throw ValidationException::withMessages(['username'=>'Usuário ou senha inválidos.']);
        }
        return response()->json(['token'=>$user->createToken('sistema-contabil')->plainTextToken,'user'=>$user->only('id','name','email')]);
    }
    public function me(Request $request) {return response()->json($request->user()->only('id','name','email'));}
    public function logout(Request $request) {
        $request->user()->currentAccessToken()?->delete();
        return response()->json(['message'=>'Sessão encerrada.']);
    }
}
