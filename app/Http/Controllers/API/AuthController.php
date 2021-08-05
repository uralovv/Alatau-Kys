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


// Remastered AuthController

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



        //Generating Secure Code

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
           'Код' => 'Код подтверждения отправлен на почту !'
        ]);

    }

    public function confirm_code(Request $request){



        $check = SecureCode::where('email','=', $request->input('email'))->where('value','=',$request->input('secure_code'))->first();
        if (!$check){
            throw new Exception('Неверный код !');
        }
    }

}
