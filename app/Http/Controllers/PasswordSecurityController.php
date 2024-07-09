<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Google2FA;
use App\User;

class PasswordSecurityController extends Controller
{
    public function show2faForm(Request $request){
        $user = Auth::user();

        $google2fa_url = "";
        if($user->passwordSecurity()->exists()){
            $google2fa = app('pragmarx.google2fa');

            $google2fa_url = $google2fa->getQRCodeInline(
                'ESA',
                $user->email,
                $user->passwordSecurity->google2fa_secret
            );
        }
        $data = array(
            'user' => $user,
            'google2fa_url' => $google2fa_url
        );
        return view('google2fa.form')->with('data', $data);
    }

    public function generate2faSecret(Request $request){
        $user = Auth::user();
        // Initialise the 2FA class
        $google2fa = app('pragmarx.google2fa');
    
        // Add the secret key to the registration data
        $user->passwordSecurity()->create([
            'user_id' => $user->id,
            'google2fa_enable' => 0,
            'google2fa_secret' => $google2fa->generateSecretKey(),
        ]);
    
        return redirect('/2fa')->with('success',"Secret Key is generated, Please verify Code to Enable 2FA");
    }
    
    public function enable2fa($id){
        $user = User::where('id', $id)->first();
        $passwordSecurity = DB::table('password_securities')->where('user_id', $id)->first();
        // $secret = $request->input('verify-code');
        // $valid = $google2fa->verifyKey($user->passwordSecurity->google2fa_secret, $secret);

        if(!$passwordSecurity){
            $google2fa = app('pragmarx.google2fa');
            $user->passwordSecurity()->create([
                'user_id' => $user->id,
                'google2fa_enable' => 1,
                'google2fa_secret' => $google2fa->generateSecretKey(),
            ]);

            return $this->sendResponse('success',"2FA is now enabled.");
        } else {
            $user->passwordSecurity->google2fa_enable = 1;
            $user->passwordSecurity->save();
            
            return $this->sendResponse('success',"2FA is now enabled.");
        }
    }

    public function disable2fa($id){
        $user = User::where('id', $id)->first();
        $user->passwordSecurity->google2fa_enable = 0;
        $user->passwordSecurity->save();

        return $this->sendResponse('success',"2FA is now disabled.");
    }

    public function twoFactorVerifyStatus($id)
    {
        $passwordSecurity = DB::table('password_securities')->where('user_id', $id)->first();

        if(!$passwordSecurity){
            return $this->sendResponse(false);
        } else if($passwordSecurity->google2fa_enable == 1) {
            return $this->sendResponse(true);
        } else {
            return $this->sendResponse(false);
        }
    }

    public function code($id)
    {
        $user = User::where('id', $id)->first();

        $google2fa_url = "";
        if($user->passwordSecurity()->exists()){
            $google2fa = app('pragmarx.google2fa');

            $google2fa_url = $google2fa->getQRCodeInline(
                config('app.name'),
                $user->email,
                $user->passwordSecurity->google2fa_secret
            );
        }

        return $this->sendResponse($google2fa_url);
    }

    public function twoFaVerify()
    {
        return redirect('/');
    }
}
