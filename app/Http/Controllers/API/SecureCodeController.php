<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Mail\ConfirmationMail;
use App\Models\SecureCode;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Exception;
use Symfony\Component\Console\Input\Input;
use function PHPUnit\Framework\throwException;

class SecureCodeController extends Controller
{
    /**
     * @throws Exception
     */
    public function create(Request $request)
    {
        $request->validate(

          [
              'email' => ['bail','required','email','unique:secure_codes']

          ],

          [
              'email.required'=>'Укажите ваш почтовый адрес!',
              'email.email' => 'Неверный почтовый адрес!',
              'email.unique' => 'Данный почтовый адрес уже зарегистрирован !'
            ]
        );
//        if ($validator->fails()) {
//            throw new Exception($validator->errors()->first());
//        }
        if (!SecureCode::canSend($request->input('email'))) {
            throw new Exception(
                'Отправить код можно один раз в минуту! Немного подождите и повторите попытку!'
            );
        }

        SecureCode::deleteInvalid($request->input('email'));
        // Generate secure code
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


        return response()->json([
            'Код' => 'Код подтверждения отправлен на вашу почту !'
        ]);

//        (new \App\Models\SecureCode)->code_sent();
    }

    public function register(Request $request){

        $validator = $request->validate([
            'name' => ['bail','required'],
            'email' => ['bail','required','email','unique:users'],
            'password' => ['bail','required','confirmed'],
            'secure_code' => ['bail','required']
        ]);

        $validator['password'] = bcrypt($request->password);


//        $check = SecureCode::where('email',$request->input('email'))->where('value',$request->input('secure_code'));
        $check = SecureCode::where('email','=', $request->input('email'))->where('value','=',$request->input('secure_code'))->first();

        if (!$check){
            throw new Exception(
                'Неверный код !'
            );
        }

        $user = User::create($validator);

        $accessToken = $user->createToken('authToken')->accessToken;
        return response(['user'=>$user,'token'=>$accessToken],201);

    }

}
