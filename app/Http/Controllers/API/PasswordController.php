<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Rules\MatchOldPassword;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class PasswordController extends Controller
{
//    public function __construct()
//    {
//        $this->middleware('auth:api');
//    }

    public function update(Request $request){

       $validator = Validator::make(
           $request->all(),[
               'current_password' => ['bail','required', new MatchOldPassword],
               'password' => ['bail','required','min:8','confirmed'],
               [
                   'current_password.required' => 'Введите старый пароль !',
                   'password.required' => ['Введите новый пароль !'],
                   'password.min' => ['Новый пароль должен иметь минимум 8 символов !'],
                   'password.confirmed' => ['Подтвердите новый пароль !']
               ]
           ]
       );
       if ($validator->fails()){
           throw new \Exception($validator->errors()->first());
       }

        User::find(auth('api')->user()->id)->update(['password'=> Hash::make($request->password)]);
       return response([
           'message' => 'Пароль успешно изменен !'
       ]);
    }
    public function forgot(Request $request){
        $request->validate([
        'email' => 'required|email'
        ]);

        $status = Password::sendResetLink(
            $request->only('email')
        );

        if ($status == Password::RESET_LINK_SENT){
            return [
                'status' => __($status)
            ];
        }
        throw new \Exception('Почта не найдена !');
    }
    public function reset(Request $request){
        $request->validate([
            'token' => 'bail|required',
            'email' => 'bail|required|email',
            'password' => 'bail|required|confirmed'
        ]);
        $status = Password::reset(
            $request->only('email','password','token','password_confirmation'),
            function ($user) use ($request){
                $user->forceFill([
                    'password' => Hash::make($request->password),
                    'remember_token' => Str::random(60)
                ])->save();

                event(new PasswordReset($user));
            }
        );

        if ($status == Password::PASSWORD_RESET){
            return response([
                'message' => 'Пароль успешно обновлен !'
            ]);
        }
        return response([
            'message' => __($status)
        ],500);
    }
}
