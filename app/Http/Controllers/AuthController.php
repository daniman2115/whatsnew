<?php

namespace App\Http\Controllers\Api;
use App\Http\Controllers\BaseController;
use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Otp;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Contracts\Encryption\DecryptException;
use Laravel\Sanctum\PersonalAccessToken;
use Illuminate\Support\Str;
use Carbon\Carbon; 
use App\Models\Userdevice;
use Mail;
use DB;
use Hash;
use Socialite;
use App\Mail\SendOtpCodeMail;


class AuthController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum', ['except' => ['connectFacebook', 'connectFacebookPage', 'login', 'register', 'forgot_password','reset_password', 'verify_email', 'resend_verification_email', 'socialLogin', 'handleProviderCallback','get_user_from_token']]);
    }


    

    public function register(Request $request){
        $validator = Validator::make($request->all(), [
            'first_name' => 'nullable',
            'last_name' => 'nullable',
            'name'  => ['required', 'string', 'unique:users','regex:/^[a-zA-Z_]+$/', 'max:255'],
            'email' => 'required|email|unique:users',
            'password' => 'required|min:8|confirmed',
        ]);

        if($validator->fails()){
            return $this->sendError('Validation Error.', $validator->errors());
        }

        $input = $request->all();
        $input['password'] = bcrypt($input['password']);
        $user = User::create($input);

        // Send Verification email here 
        date_default_timezone_set('Africa/Addis_Ababa');
        $now = date("Y-m-d H:i:s");
        $endTime = strtotime("+10 minutes", strtotime($now));
        $expires = date("Y-m-d H:i:s", $endTime);
        // $verificationToken = Crypt::encryptString($input['email'] . '~' . $now . '~' . $expires);
        // try {
        //     Mail::send('emails.activateAccount', ['token' => $verificationToken, 'app_url' => env('FRONTEND_URL'),], function($message) use($request){
        //         $message->to($request->email);
        //         $message->subject('Welcome to Lymlyts! Please activate your Account.');
        //     });
        // } catch (Exeption $e)
        // {
        //     dd($e);
        // }
        // done here
        $otpCode = random_int(1000, 9999);
        
        $user = User::find($user->id);
        Mail::to($user->email)->send(new SendOtpCodeMail($otpCode));
        Otp::create([
            'user' => $user->id, 
            'type' => $user->email, 
            'otp_number' => $otpCode, 
            'expired_at' => Carbon::now()->addMinutes(10), 
            'status' => 'Active'
        ]);
        $success['token'] =  $user->createToken('MyApp')->plainTextToken;
        $success['name'] =  $user->name;
        $success['email'] =  $user->email;
        $success['type'] = $user->type;
        $success['phone'] = $user->phone_number;
        $success['email_verified_at'] = $user->email_verified_at;
        $success['brands'] = $user->brand;
        $success['influencer'] = $user->influencer;
        $success['permissions'] = $user->getAllPermissions()->pluck('name')->toArray();

        return $this->sendResponse($success, 'User register successfully.');
    }

public function get_authenticated_user (Request $request){
        $user = Auth::user();
        if($user) {
            $profile_data = null;
            if($user->type == "Influencer"){
                $profile_data = User::whereId($user->id)->with('influencer.social_media_accounts', 'influencer.content_list', 'brands','influencer.emails', 'privacy_setting')->first();
                $profile_data->total_likes = $profile_data->influencer->likes->count();
                $profile_data->permissions = $user->getAllPermissions()->pluck('name')->toArray();
                $profile_data->token = $user->createToken('token')->plainTextToken;
            }
            else if($user->type == "Brand"){
                $profile_data = User::whereId($user->id)->with('brands.category.categorie', 'influencer', 'privacy_setting')->first();
                $profile_data->permissions = $user->getAllPermissions()->pluck('name')->toArray();
                $profile_data->token = $user->createToken('token')->plainTextToken;
            }
            else if($user->type == "User"){
                $profile_data = User::whereId($user->id)->with('brands', 'influencer', 'privacy_setting')->first();
                $profile_data->permissions = $user->getAllPermissions()->pluck('name')->toArray();
                $profile_data->token = $user->createToken('token')->plainTextToken;
            }
            else {
                $profile_data = User::whereId($user->id)->with('privacy_setting')->first();
                $profile_data->permissions = $user->getAllPermissions()->pluck('name')->toArray();
                $profile_data->token = $user->createToken('token')->plainTextToken;
            }
            return $this->sendResponse($profile_data, 'User login successfully.');
        }
        else{
            return $this->sendError('Unauthorized.', ['error'=>'Unauthorized']);
        }
    }
    
    public function login(Request $request)
    {
        $user = User::where('email', $request->email)->orWhere('name', $request->email)->first();
        if(!$user){
            return $this->sendError("Email or username not found");
        }
        if($user && Hash::check($request->password, $user->password)) {
            $profile_data = null;
            if($user->type == "Influencer"){
                $profile_data = User::whereId($user->id)->with('influencer.social_media_accounts', 'influencer.content_list', 'brands','influencer.emails', 'privacy_setting')->first();
                $profile_data->total_likes = $profile_data->influencer->likes->count();
                $profile_data->total_ratings = optional($profile_data->influencer)->ratings ? $profile_data->influencer->ratings->sum('rating') : 0;
                $profile_data->permissions = $user->getAllPermissions()->pluck('name')->toArray();
                $profile_data->token = $user->createToken('token')->plainTextToken;
            }
            else if($user->type == "Brand"){
                $profile_data = User::whereId($user->id)->with('brands.category.categorie', 'influencer', 'privacy_setting')->first();
                $profile_data->permissions = $user->getAllPermissions()->pluck('name')->toArray();
                $profile_data->token = $user->createToken('token')->plainTextToken;
            }
            else if($user->type == "User"){
                $profile_data = User::whereId($user->id)->with('brands', 'influencer', 'privacy_setting')->first();
                $profile_data->permissions = $user->getAllPermissions()->pluck('name')->toArray();
                $profile_data->token = $user->createToken('token')->plainTextToken;
            }
            else {
                $profile_data = User::whereId($user->id)->first();
                $profile_data->permissions = $user->getAllPermissions()->pluck('name')->toArray();
                $profile_data->token = $user->createToken('token')->plainTextToken;
            }
            // dd($request);

Userdevice::logUserLogin($user->id,  $request);
            return $this->sendResponse($profile_data, 'User login successfully.');
        }
        else{
            return $this->sendError('Invalid Password');
        }
    }

    public function get_user_from_token($token){
        $tokenData = PersonalAccessToken::findToken($token);
        if (!$tokenData) {
            return response()->json(['message' => 'Invalid token'], 401);
        }
        $user = $tokenData->tokenable;
        if($user) {
            $profile_data = null;
            if($user->type == "Influencer"){
                $profile_data = User::whereId($user->id)->with('influencer.social_media_accounts', 'influencer.content_list', 'brands','influencer.emails', 'privacy_setting')->first();
                $profile_data->permissions = $user->getAllPermissions()->pluck('name')->toArray();
                $profile_data->token = $user->createToken('token')->plainTextToken;
            }
            else if($user->type == "Brand"){
                $profile_data = User::whereId($user->id)->with('brands.category.categorie', 'influencer', 'privacy_setting')->first();
                $profile_data->permissions = $user->getAllPermissions()->pluck('name')->toArray();
                $profile_data->token = $user->createToken('token')->plainTextToken;
            }
            else if($user->type == "User"){
                $profile_data = User::whereId($user->id)->with('brands', 'influencer', 'privacy_setting')->first();
                $profile_data->permissions = $user->getAllPermissions()->pluck('name')->toArray();
                $profile_data->token = $user->createToken('token')->plainTextToken;
            }
            else {
                $profile_data = User::whereId($user->id)->first();
                $profile_data->permissions = $user->getAllPermissions()->pluck('name')->toArray();
                $profile_data->token = $user->createToken('token')->plainTextToken;
            }
            return $this->sendResponse($profile_data, 'User login successfully.');
        }
    }

    public function logout(Request $request)
    {
        if ($request->user() && method_exists($request->user(), 'currentAccessToken')) {
            $request->user()->currentAccessToken()->delete();
        }
        $this->sendResponse(1,"Logged out Successfully");
    }

    public function socialLogin($social)
    {
        if($social == "facebook"){
            return Socialite::driver($social)->redirectUrl(env('FACEBOOK_AUTH_REDIRECT'))->redirect(); 
        }
        if($social == "facebook_page"){
            $this->connectFacebookPage();
        }
        return Socialite::driver($social)->redirect();
    }

    public function connectFacebook(){
        return Socialite::driver('facebook')->redirect();
    }

    public function connectFacebookPage(){
        $clientId = env('FACEBOOK_BUSINESS_APP_ID');
        $clientSecret = env('FACEBOOK_BUSINESS_APP_SECRET');
        $redirectUrl = env('FACEBOOK_BUSINESS_REDIRECT_URI');
        $client = new \GuzzleHttp\Client();
        // $permissions = "pages_show_list,pages_read_engagement,pages_manage_posts,read_insights";
        $permissions = "pages_show_list,pages_read_engagement";
        $authUrl = "https://www.facebook.com/v21.0/dialog/oauth?"
        . "client_id={$clientId}"
        . "&redirect_uri={$redirectUrl}"
        . "&scope={$permissions}"
        . "&response_type=code";
        return redirect()->away($authUrl);
 }

public function handleProviderCallback($social)
    {
        if($social == "facebook"){
            $userSocial = Socialite::driver($social)->redirectUrl(env('FACEBOOK_AUTH_REDIRECT'))->user();
        } else {
            $userSocial = Socialite::driver($social)->user();
        }
        $phrase = $userSocial->getName();
        //twitter done 
        if($social == "facebook"){
            if($userSocial->getEmail()){
                $user = User::where(['email' => $userSocial->getEmail()])->first();
            }
            else {
                $generated_email = str_replace(' ', '', $phrase).'@facebook.com';
                $user = User::where(['email' => $generated_email])->first();
            }
        }
        else {
            $user = User::where(['email' => $userSocial->getEmail()])->first();
        }
        if($user){
            $token =  $user->createToken('token')->plainTextToken;
            return redirect(env('FRONTEND_URL') . '/login-social?token='.$token);
        }else{
            $user = User::create([
                'name' => $userSocial->getName(),
                'email' => $userSocial->getEmail()??(str_replace(' ', '', $phrase).'@facebook.com'),
                'email_verified_at' => now(),
            ]);
            $user->email_verified_at = now();
            $user->save();


            $success['token'] =  $user->createToken('token')->plainTextToken;
            $success['name'] =  $user->name;
            $success['email'] =  $user->email;
            $success['type'] = $user->type;
            $success['phone'] = $user->phone_number;
            $success['email_verified_at'] = $user->email_verified_at;
            $success['permissions'] = $user->getAllPermissions()->pluck('name')->toArray();
            return redirect(env('FRONTEND_URL') . '/login-social?token='.$success['token'] );
        }
    }

    public function verify_email($token)
    {
        try {
            $decrypted = Crypt::decryptString($token);
            $token = explode('~', $decrypted);
            $datetime   = date("Y-m-d H:i:s");
            $now        = strtotime($datetime);

            $user = User::where('email', $token[0])->first();
            if (strtotime($token[2]) > $now && $user->email_verified_at == null) {
            $user->email_verified_at = date('Y-m-d H:i:s');
            $user->save();

$profile_data = null;
            if($user->type == "Influencer"){
                $profile_data = User::whereId($user->id)->with('influencer.social_media_accounts', 'influencer.content_list', 'brands','influencer.emails')->first();
                $profile_data->permissions = $user->getAllPermissions()->pluck('name')->toArray();
                $profile_data->token = $user->createToken('token')->plainTextToken;
            }
            else if($user->type == "Brand"){
                $profile_data = User::whereId($user->id)->with('brands.category.categorie', 'brands.brand')->first();
                $profile_data->permissions = $user->getAllPermissions()->pluck('name')->toArray();
                $profile_data->token = $user->createToken('token')->plainTextToken;
            }
            else if($user->type == "User"){
                $profile_data = User::whereId($user->id)->with('brands', 'influencer')->first();
                $profile_data->permissions = $user->getAllPermissions()->pluck('name')->toArray();
                $profile_data->token = $user->createToken('token')->plainTextToken;
            }
            return $this->sendResponse($profile_data, 'User login successfully.');
                
            } else {
                $response['error'] = 'Request Error';
                $response['message'] = 'Your request is invalid. please request a verification email again.';
                $statusCode = 400;
                return response()->json($response, $statusCode);
            }
        } catch (DecryptException $e) {
            $response['error'] = 'Internal Server Error';
            $response['message'] = 'Please try again.';
            $statusCode = 500;
            return response()->json($response, $statusCode);
        }
    }

    public function resend_verification_email(Request $request)
    {
        $email = Auth::user()->email;
        date_default_timezone_set('Africa/Addis_Ababa');
        $now = date("Y-m-d H:i:s");
        $endTime = strtotime("+10 minutes", strtotime($now));
        $expires = date("Y-m-d H:i:s", $endTime);
        $verificationToken = Crypt::encryptString($email . '~' . $now . '~' . $expires);
        try {
            Mail::send('emails.activateAccount', ['token' => $verificationToken, 'app_url' => env('FRONTEND_URL'),], function($message) use($email){
                $message->to($email);
                $message->subject('Welcome to LymLyt ! Please activate your Account.');
            });
        } 
        catch (Exception $e)
        {
            return $this->sendError("Couldn't Send Verification Email", 
                ['error'=>'Cound not send verification Eamil']
            );
        }
        return response()->json(["message"=>"Succussfull sent"]);
    }

    public function reset_password(Request $request)
    {
        $request->validate([
            'email'                     => 'required|email|exists:users',
            'token'                     => 'required|string|min:6',
            'password'                  => 'required|string|min:6|confirmed',
            'password_confirmation'     => 'required',
        ]);

        $updatePassword = DB::table('password_reset_tokens')->where(['email' => $request->email, 'token' => $request->token])->first();

        if(!$updatePassword) {
            $response['error'] = 'Invalid Request';
            $response['message'] = 'This request to reset password is invalid.';
            $statusCode = 400;
            return response()->json($response, $statusCode);
        }

        $user = User::where('email', $request->email)
                ->update(['password' => Hash::make($request->password)]);

        DB::table('password_reset_tokens')->where(['email'=> $request->email])->delete();

        return $this->sendResponse([], 'Password Reset Successfully.');
    }

    public function forgot_password(Request $request)
    {
        $input = $request->all();

$validator = Validator::make($request->all(), [
            'email' => 'required|email',
        ]);
   
        if($validator->fails()){
            return $this->sendError('Validation Error.', $validator->errors());      
        }

        $token = Str::random(64);

        $updatePassword = DB::table('password_reset_tokens')->where(['email' => $request->email])->delete();


        DB::table('password_reset_tokens')->insert(
            ['email' => $request->email, 'token' => $token, 'created_at' => Carbon::now()]
        );

        

        try {
            Mail::send('emails.forgetPassword', ['token' => $token, 'app_url' => env('FRONTEND_URL')], function($message) use($request){
                $message->to($request->email);
                $message->subject('Reset Password Notification');
            });
        } catch (Exception $e)
        {
        }
    
        return $this->sendResponse([], 'Password reset mail sent successfully.');
    }

    public function forgot_password_otp(Request $request)
    {
        $input = $request->all();

        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
        ]);
   
        if($validator->fails()){
            return $this->sendError('Validation Error.', $validator->errors());      
        }

        $token = Str::random(64);

        $updatePassword = DB::table('password_reset_tokens')->where(['email' => $request->email])->delete();


        DB::table('password_reset_tokens')->insert(
            ['email' => $request->email, 'token' => $token, 'created_at' => Carbon::now()]
        );

        

        try {
            Mail::send('emails.forgetPassword', ['token' => $token, 'app_url' => env('FRONTEND_URL')], function($message) use($request){
                $message->to($request->email);
                $message->subject('Reset Password Notification');
            });
        } catch (Exception $e)
        {
        }
    
        return $this->sendResponse([], 'Password reset mail sent successfully.');
    }

    

    public function get_user()
    {   
        $user = Auth::user();
        $profile_data = null;
            if($user->type == "Influencer"){
                $profile_data = User::whereId($user->id)->with('influencer.social_media_accounts', 'influencer.content_list', 'brands','influencer.emails', 'privacy_setting')->first();
                $profile_data->permissions = $user->getAllPermissions()->pluck('name')->toArray();
                $profile_data->token = $user->createToken('token')->plainTextToken;
            }
            else if($user->type == "Brand"){
                $profile_data = User::whereId($user->id)->with('brands.category.categorie', 'influencer', 'privacy_setting')->first();
                $profile_data->permissions = $user->getAllPermissions()->pluck('name')->toArray();
                $profile_data->token = $user->createToken('token')->plainTextToken;
            }
            else if($user->type == "User"){
                $profile_data = User::whereId($user->id)->with('brands', 'influencer', 'privacy_setting')->first();
                $profile_data->permissions = $user->getAllPermissions()->pluck('name')->toArray();
                $profile_data->token = $user->createToken('token')->plainTextToken;
            }
            else {
                $profile_data = User::whereId($user->id)->first();
                $profile_data->permissions = $user->getAllPermissions()->pluck('name')->toArray();
                $profile_data->token = $user->createToken('token')->plainTextToken;
            }
        return $this->sendResponse($profile_data, 'User login successfully.');
    }

public function update_account(Request $request){
        $user = Auth::user();
        $validator = Validator::make($request->all(), [
            'name' => 'required|max:255',
            'email' => 'required|email',
            'phone' => 'nullable',
            'referal_code' => 'nullable'
        ]);
        if($validator->fails()){
            return $this->sendError('Validation Error.', $validator->errors());
        }
        $updated_user = $user->update($validator->validated());
        return $this->sendResponse($updated_user, "Account data updated succesfully");
    }

    public function check_verification(Request $request){
        $verification_time = Auth::user()->email_verified_at;
        if($verification_time == null){
            return $this->sendError("Email is not verified", "");
        }
        return $this->sendResponse(false, "Email Verified Succesfully");
    }

    public function send_email_password_rest_otp(Request $request){

    }
}