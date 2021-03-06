<?php

namespace App\Http\Controllers\Auth;

use App\User;
use App\Mail\VerifyMail;
use App\VerifyUser;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Auth\RegistersUsers;
use Mail;




class RegisterController extends Controller
{

    use RegistersUsers;  

    protected $redirectTo = '/home';


    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    protected function validator(array $data)
    {
        return Validator::make($data, [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
        ]);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return \App\User
     */
   
    protected function create(array $data)
    {
 
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => bcrypt($data['password']),
            'sexo' => $data['sexo'],
        ]);
 
        $verifyUser = VerifyUser::create([
            'user_id' => $user->id,
            'token' => str_random(40)
        ]);
 
        Mail::to($user->email)->send(new VerifyMail($user));
 
        return $user;
    }

   


   public function verifyUser($token)
    {
        $verifyUser = VerifyUser::where('token', $token)->first();

        if(isset($verifyUser) ){
            $user = $verifyUser->user;

            if(!$user->verified) {
                $verifyUser->user->verified = 1;
                $verifyUser->user->save();
                $status = "Seu e-mail foi verificado. Agora você pode fazer o login.";
            }else{
                $status = "Seu e-mail já foi verificado. Agora você pode fazer o login.";
            }
        }else{
            return redirect('/login')->with('warning', "Desculpe seu e-mail não pode ser identificado.");
        }
        
        return redirect('/login')->with('status', $status);
    }

     protected function registered(Request $request, $user)
    {
        $this->guard()->logout();
        return redirect('/login')->with('status', 'Nós lhe enviamos um código de ativação. Verifique seu e-mail e clique no link para confirmar.');
    }


}
