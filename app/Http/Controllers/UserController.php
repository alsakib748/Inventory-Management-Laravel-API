<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\User;
use App\Mail\OTPEmail;
use App\Helper\JWTToken;
use Illuminate\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

// use Illuminate\Support\Facades\View;

class UserController extends Controller
{

    public function LoginPage(): View
    {
        return view('pages.auth.login-page');
    }

    public function RegistrationPage(): View
    {
        return view('pages.auth.registration-page');
    }

    public function SendOtpPage(): View
    {
        return view('pages.auth.send-otp-page');
    }

    public function VerifyOTPPage(): View
    {
        return view('pages.auth.verify-otp-page');
    }

    public function ResetPasswordPage(): View
    {
        return view('pages.auth.reset-pass-page');
    }

    public function ProfilePage(): View
    {
        return view('pages.dashboard.profile-page');
    }

    public function UserRegistration(Request $request)
    {
        try {
            User::create([
                'firstName' => $request->input('firstName'),
                'lastName' => $request->input('lastName'),
                'email' => $request->input('email'),
                'mobile' => $request->input('mobile'),
                'password' => $request->input('password'),
                // 'password' => Hash::make($request->input('password')),
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'User Registration Successful'
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'failed',
                'message' => 'User Registration Failed'
            ], 401);
        }
    }

    public function UserLogin(Request $request)
    {
        // $count = User::where('email', '=', $request->input('email'))
        //     ->where('password', '=', $request->input('password'))
        //     ->select('id')->first();
        $emailCheck = User::where('email', '=', $request->input('email'))->first();

        if ($emailCheck !== null) {

            if (Hash::check($request->input('password'), $emailCheck->password)) {
                //Token Issue
                $token = JWTToken::CreateToken($request->input('email'), $emailCheck->id);
                return response()->json([
                    'status' => 'success',
                    'message' => 'User Login Successful',
                    'token' => $token,
                ], 200)->cookie('token', $token, 60 * 60 * 24);
            } else {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Password does not match!'
                ], 401);
            }

        } else {
            // return unauthor
            return response()->json([
                'status' => 'failed',
                'message' => 'Email not found!'
            ], 401);
        }
    }

    // public function UserLogin(Request $request){
    //     $user = User::where('email', $request->input('email'))->first();

    //     if ($user && Hash::check($request->input('password'), $user->password)) {
    //         // Token Issue
    //         $token = JWTToken::CreateToken($user->email);
    //         return response()->json([
    //             'status' => 'success',
    //             'message' => 'User Login Successful',
    //             'token' => $token
    //         ], 200);
    //     } else {
    //         // Return unauthorized
    //         return response()->json([
    //             'status' => 'failed',
    //             'message' => 'Unauthorized'
    //         ], 401);
    //     }
    // }


    public function SendOTPCode(Request $request)
    {
        $email = $request->input('email');
        $otp = rand(100000, 999999);
        $result = User::where('email', '=', $email)->count();
        if ($result == 1) {
            // Mail Send
            Mail::to($email)->send(new OTPEmail($otp));
            // Database Update
            User::where('email', '=', $email)->update(['otp' => $otp]);

            return response()->json(['status' => 'success', 'message' => '6 Digit OTP Code has been send to your email !'], 200);
        } else {
            return response()->json(['status' => 'failed', 'data' => 'unauthorized'], 401);
        }
    }

    public function VerifyOTP(Request $request)
    {
        $email = $request->input('email');
        $otp = $request->input('otp');
        $count = User::where('email', '=', $email)->where('otp', '=', $otp)->count();
        if ($count == 1) {
            User::where('email', '=', $email)->update(['otp' => "0"]);
            $token = JWTToken::CreateTokenForSetPassword($request->input('email'));
            return response()->json([
                'status' => 'success',
                'message' => 'OTP Verification Successful',
                'token' => $token
            ], 200)->cookie('token', $token, 60 * 60 * 24);
        } else {
            return response()->json([
                'status' => 'failed',
                'message' => 'unauthorized'
            ], 401);
        }
    }

    public function ResetPassword(Request $request)
    {
        try {
            $email = $request->header('email');
            $password = $request->input('password');
            User::where('email', '=', $email)->update(['password' => $password]);
            return response()->json([
                'status' => 'success',
                'message' => 'Request Successfully'
            ], 200);
        } catch (Exception $exception) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Something Went Wrong'
            ], 401);
        }
    }

    public function UserLogout()
    {
        return redirect('/userLogin')->cookie('token', '', -1);
    }

    public function UserProfile(Request $request)
    {
        // sleep(4);
        $email = $request->header('email');
        $user = User::where('email', '=', $email)->first();
        return response()->json([
            'status' => 'success',
            'message' => 'Request Successful',
            'data' => $user
        ], 200);
    }

    public function UserUpdate(Request $request)
    {
        try {
            $email = $request->header('email');
            $firstName = $request->input('firstName');
            $lastName = $request->input('lastName');
            $mobile = $request->input('mobile');
            $password = $request->input('password');
            User::where('email', '=', $email)->update([
                'firstName' => $firstName,
                'lastName' => $lastName,
                'mobile' => $mobile,
                'password' => $password
            ]);
            return response()->json([
                'status' => 'success',
                'message' => 'Request Successful',
            ], 200);

        } catch (Exception $exception) {
            return response()->json([
                'status' => 'fail',
                'message' => 'Something Went Wrong',
            ], 200);
        }
    }

}