<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends BaseController
{
    public function register(Request $request)
    {
        $this->validate($request, [
            'email' => 'required|email',
            'password' => 'required|min:6',
            'signup' => 'required|in:IOS,Android'
        ]);

        $platform = $request->input('signup');

        $user = User::where('email', $request->input('email'))->first();

        if($user){
            if(!$user->email_verified_at){
                $this->generateCodenSendMail($user);
            }
            return $this->item($user, new UserWithTokenTransformer);
        }

        $user = User::create([
            'email' => $request->input('email'),
            'password' => bcrypt($request->input('password')),
            'signup_platform'  => $platform
        ]);

        $user->assignRole("User");

        $this->generateCodenSendMail($user);


        return $this->item($user, new UserWithTokenTransformer);
    }

    private function generateCodenSendMail($user){

        $email_code = generateCode();

        if($user->code)
        {
            $user->code->update(['email' => $user->email,'code_email' => $email_code]);
        }
        else
        {
            $user->code()->create(['email' => $user->email,'code_email' => $email_code]);
        }

        Log::info("email ". $user->email. " and code: ".$email_code);

        Mail::to($user)->queue(new SendVerification($email_code));
    }

    public function login(Request $request)
    {

        $credentials = $request->only('email', 'password');

        if (!Auth::attempt($credentials)) {
            return response()->json(['error' => 'invalid_credentials'], 401);
        }

        $user = User::where('email',$request->input('email'))->first();

        return $this->item($user, new UserWithTokenTransformer);

    }

    public function logout()
    {
        try {
            JWTAuth::invalidate(JWTAuth::parseToken());
            return response()->json(['message' => 'Logout successful']);
        } catch (JWTException $e) {
            // something went wrong whilst attempting to encode the token
            return response()->json(['error' => 'Session expired'], 401);
        }
    }

    public function authGoogle(Request $request)
    {
        $this->validate($request, [
            'google_token' => 'required',
        ]);

        $CLIENT_ID = env('GOOGLE_CLIENT_APP_ID');


        $client = new Google_Client(['client_id' => $CLIENT_ID]);

        $google = $client->verifyIdToken($request->input('google_token'));


        if(!$google){
            return $this->response->array(['status'=>'error', 'error' =>['code'=>'invalid','message' => ['Invalid Google Token']]])->setStatusCode(422);
        }

        $email = $google['email'];

        $user = User::where('email',$email)->first();

        if(!$user){
            $user = User::create([
                'email' => $email,
                'password' => bcrypt(str_random(13)),
                'signup_platform'  => 'Google',
                'email_verified_at' => now()
            ]);

            $user->assignRole("User");

            $email_code = generateCode();

            $user->code()->create(['email' => $email,'code_email' => $email_code]);
        }

        return $this->item($user, new UserWithTokenTransformer);
    }

    public function activateEmail(Request $request)
    {
        $this->validate($request, [
            'code' => 'required|min:6|exists:codes,code_email'
        ]);

        $user = auth()->user();

        if($user->code->code_email == $request->input('code')){
            $user->email_verified_at = now();
            $user->save();

            return response()->json(['message' => 'Email '.$user->email .' verified successfully']);
        }

        return $this->response->errorNotFound();

    }

    public function activatePhone(Request $request)
    {
        $this->validate($request, [
            'code' => 'required|min:6|exists:codes,code_phone'
        ]);

        $user = auth()->user();

        if($user->email_verified_at == null){
            return response()->json(['errors'=> ['code' => 'Verify email first']],422);
        }

        if($user->code->code_phone == $request->input('code')){

            $user->email_verified_at = now();

            $user->phone_verified_at = now();

            $user->save();

            $code = Code::where('code_phone',$request->input('code'))->first();

            $code->delete();

            return response()->json(['message' => 'Phone verification successful']);
        }

        return $this->response->errorNotFound();

    }

    public function getPhoneCode(Request $request)
    {
        $this->validate($request, [
            'phone' => 'required|numeric:10'
        ]);

        $phone = $request->input('phone');

        $user = auth()->user();

        if($user->email_verified_at == null){
            return response()->json(['message' => 'Verify email first'],422);
        }

        if($user->code->phone == null)
        {
            //first time phone code generation
            $phoneCode = generateCode("phone");

            $user->code->phone = $phone;

            $user->code->code_phone = $phoneCode;

            $user->code->save();

            $user->phone = $phone;

            $user->save();

        }else{

            if (!$user->code->updated_at->isToday()) { //reset count if last trial was not today

                $user->code->count = 0;

                $user->code->save();

                $user->phone = $phone;

                $user->save();

            }

            $codect = $user->code->count;

            if($codect > 3){
                return response()->json([
                    'errors' => [
                        'phone' => 'You have exceeded the sms validation codes quota.Please wait 24 hours then try again'
                    ]
                ])->setStatusCode(422);

            }

            $phoneCode = generateCode("phone");

            $user->code->phone = $phone;

            $user->code->code_phone = $phoneCode;

            $user->code->count = $codect + 1;

            $user->code->save();

            $user->phone = $phone;

            $user->save();

        }

        Log::info("Phone ".$phone." Code ".$phoneCode);

        $user->notify(new PhoneVerificationCode($phoneCode));

        return response()->json([
            'status' => 'success',
            'message' => 'An sms with the registration code has been sent to ' . $phone . '.Please enter the code to confirm your phone number'
        ], 200);

    }







}

