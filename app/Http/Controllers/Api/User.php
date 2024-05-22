<?php

namespace App\Http\Controllers\api;

use App\Mail\RegistrationEmail;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;


class User extends Controller
{
    public function register(Request $request){
        $validation = $this->validateUser($request);
        if ($validation->fails()) {
            return response()->json([
                'result' => false,
                'error' => $validation->messages()->first()
            ], 400);
        }

        $email = $request->input("user_email");
        $name = $request->input("user_name");
        $password = $request->input("user_password");

        $user = new \App\Models\User();
        $user->user_name = $name;
        $user->user_email = $email;
        $user->user_password = Hash::make($password);
        $user->user_token = Str::random(24);
        $user->user_email_token = Str::random(24);
        $user->save();

        $registrationObject["activation_link"] = url('/verify/'). '/' . $user->user_email_token;
        Mail::to($email)->send(new RegistrationEmail($registrationObject));

        return response()->json([
            'result' => true
        ]);
    }

    public function verifyEmail($emailToken){
        $user = \App\Models\User::select('*')->where("user_email_token", "=", $emailToken)->first();

        $user->user_email_verified = 1;
        $user->save();

        return view('email_verified');

    }

    public function login(Request $request){
        $email = $request->input("user_email");
        $password = $request->input("user_password");

        $user = \App\Models\User::select('*')->where([['user_email', '=',$email]])->first();
        if(!isset($user)){
            return response()->json([
                'result' => false,
                'error' => "Prihlasovacie údaje nie sú spravné"
            ], 400);
        }

        if($user->user_email_verified != 1){
            return response()->json([
                'result' => false,
                'error' => "Emailová adresa používateľa nie je overná"
            ], 400);
        }
        if (Hash::check($password, $user->user_password)) {
            return response()->json([
                'result' => true,
                'data' => $user,
                'token' => $user->user_token
            ]);
        } else {
            return response()->json([
                'result' => false,
                'error' => "Prihlasovacie údaje nie sú spravné"
            ], 400);
        }
    }






    public function validateUser($request)
    {
        $validator = Validator::make($request->all(), [
            'user_email' => 'required|email|unique:user',
            'user_name' => 'required|string|max:50',
            'user_password' => 'required|min:6'
        ]);

        return $validator;
    }




}
