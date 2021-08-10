<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Mail\ConfirmationMail;
use App\Models\SecureCode;
use App\Models\User;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    // Register account
    /**
     * @throws Exception
     */
    public function login(Request $request){
        $loginData = $request->validate([
            'email' => 'bail|email|required',
            'password' => 'bail|required'
        ]);



        $check = User::where('email','=', $request->input('email'))->where('is_confirmed','=',0)->first();

        if (!auth()->attempt($loginData)){
            throw new \Exception('Введен неверный email или пароль!', 422);
//            return response(['message'=>'Введен неверный email или пароль']);
        }
        if ($check){
            throw new Exception('Аккаунт не подтвержден! Подтвердите вашу почту !');
        }

        $accessToken = auth()->user()->createToken('authToken')->accessToken;
        return response(['user' => auth()->user(), 'accessToken' => $accessToken]);
    }

    public function registration(Request $request){

        $validate = $request->validate([
                'name' => 'bail|required|min:3',
                'email' => 'bail|required|email|unique:users',
                'password' => 'bail|required|min:7'
        ],
            [
                'name.required' => 'Введите имя !',
                'email.required' => 'Введите почтовый адрес !',
                'email.unique' => 'Данный почтовый ящик уже был зарегестрирован!',
                'email.email' => 'Неверный формат почтового адреса !',
                'password.required' => 'Введите пароль !',
                'password.min' => 'Пароль должен состоять минимум из 7 символов !'
        ]);

        $validate['password'] = bcrypt($request->password);


        $user = User::create($validate);
        $accessToken = $user->createToken('authToken')->accessToken;


        $code = '';

        for ($i=0; $i<4; $i++) {
            $code .= mt_rand(0, 9);
        }


        // Creating secure code
        $secureCode = new SecureCode([
            'value' => $code,
            'email' => $request->input('email')
        ]);

        DB::transaction(function () use ($request,$secureCode){
            if (!$secureCode->save()) {
                throw new Exception('Не удалось сохранить код! Попробуйте заново !');
            }
        });

        Mail::to($request->input('email'))->send(new ConfirmationMail($code));

        return response([
           'data' => 'Код подтверждения отправлен на почту !'
        ]);

    }

    public function confirm_code(Request $request){

        $email = $request->input('email');
        $code = $request->input('secure_code');

        $request->validate([
            'email' => 'bail|required|email',
            'secure_code' => 'bail|required'
        ]);



        $check = SecureCode::where('email','=', $email)->where('value','=',$code)->first();
        if (!$check){
            throw new Exception('Неверный код или неверный почтовый адрес!');
        }

        $confirmed = DB::table('users')->where('email','=',$email)
            ->update(['is_confirmed' => 1]);

        if (!$confirmed){
            throw new Exception('Пользователь уже был подтрвержден !');
        }

        $user = SecureCode::where('email', $email)->where('value',$code)->first();
        $token = $user->createToken('authToken')->accessToken;

        if (!$user){
            throw new Exception('Пользователь не найден !');
        }

        $already_confirmed = DB::table('users')->where('is_confirmed', '=' , 1);
        if ($already_confirmed){
            throw new Exception('Пользователь уже был подтрвержден !');
        }

        return response([
            'data' => 'Акканут подтвержден !',
            'Token' => $token
        ]);

    }


}
