<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\SecureCode;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    // Register account
    /**
     * @throws Exception
     */
    public function register(Request $request){
        $validatedData = $request->validate([
           'name' => 'bail|required|max:30',
            'email' => 'bail|required|email|unique:users',
            'password' => 'bail|required|confirmed',
//            'code' => 'bail|required'
        ] ,
        [
            'email.unique' => 'Данный почтовый ящик уже был зарегестрирован!'
        ]);

        $validatedData['password'] = bcrypt($request->password);

        //Code check for mail

        if (!SecureCode::check($request->input('email'), $request->input('secure_code'))) {
            throw new Exception('Неверный код! Повторите попытку или отправьте код заново!');
        }

        $user = User::create($validatedData);
        $accessToken = $user->createToken('authToken')->accessToken;
        return response(['user'=>$user,'token'=>$accessToken],201);
    }

    //Login
    public function login(Request $request){
        $loginData = $request->validate([
           'email' => 'bail|email|required',
            'password' => 'bail|required'
        ]);

        if (!auth()->attempt($loginData)){
            throw new \Exception('Введен неверный email или пароль!', 422);
//            return response(['message'=>'Введен неверный email или пароль']);
        }
        $accessToken = auth()->user()->createToken('authToken')->accessToken;
        return response(['user' => auth()->user(), 'accessToken' => $accessToken]);
    }

    //Logout

//    public function logout(){
//        Auth::user()->token()->revoke();
//    }

}
