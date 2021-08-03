<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Rules\MatchOldPassword;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class PasswordController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }

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
}
