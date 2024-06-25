<?php

namespace App\Http\Controllers\Auth;

use Exception;
use App\Models\User;
use App\Mail\OTPMail;
use App\Helper\JWTToken;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class AuthController extends Controller
{
    function UserLogin(Request $request)
{
    try {
        $user = User::where('email', $request->input('email'))->first();

        if ($user && password_verify($request->input('password'), $user->password)) {
            $token = JWTToken::CreateToken($user->email, $user->id);
            return response()->json([
                'status' => 'success',
                'message' => 'User Login Successful',
            ], 200)->cookie('token', $token, 60*24*30);
        } else {
            return response()->json([
                'status' => 'failed',
                'message' => 'Authentication failed'
            ], 401); 
        }
    } catch (Exception $e) {
        return response()->json(['status' => 'fail', 'message' => $e->getMessage()], 500);
    }
}

    function SendOTPCode(Request $request)
    {
        $email = $request->input('email');
        $otp = rand(1000, 9999);
        $count = User::where('email', '=', $email)->count();

        if ($count == 1) {
            // mail send
            Mail::to($email)->send(new OTPMail($otp));

            // OTP code update to database
            User::where('email', '=', $email)->update(['otp' => $otp]);
            return response()->json([
                'status' => 'success',
                'message' => '4 Digit Send successfully'
            ], 200);
        } else {
            return response()->json([
                'status' => 'failed',
                'message' => 'unauthorized'
            ], 401);
        }
    }

    function VerifyOTP(Request $request)
    {
        $email = $request->input('email');
        $otp = $request->input('otp');
        $count = User::where('email', '=', $email)
            ->where('otp', '=', $otp)->count();
        if ($count == 1) {
            User::where('email', '=', $email)->update(['otp' => '0']);
            // Token Issue
            $token = JWTToken::CreateTokenForSetPassword($email);
            return response()->json([
                'status' => 'success',
                'message' => 'OTP verification successful',
                'token' => $token
            ], 200)->cookie('token', $token, 60*24*30);
        } else {
            return response()->json([
                'status' => 'failed',
                'message' => 'Unauthorized'
            ], 401);
        }
    }

    function ResetPassword(Request $request){
        try{
            $email=$request->header('email');
            $password=$request->input('password');
            $hashedPassword = Hash::make($password);
            User::where('email','=',$email)->update(['password'=>$hashedPassword]);
            return response()->json([
                'status' => 'success',
                'message' => 'Request Successful',
            ],200);

        }catch (Exception $e){
            return response()->json(['status' => 'fail', 'message' => $e->getMessage()]);
        }
    }


}
