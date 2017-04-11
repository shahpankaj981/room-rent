<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;
use App\Mail\ActivationEmail;
use App\Mail\ForgotPasswordEmail;
use Illuminate\Support\Facades\Hash;
use Response;

class UserController extends Controller
{
    public function __construct(User $user)
	{
        $this->user = $user;
	}

	public function login(Request $request)
	{
		$this->response = [];
		$apiToken = "OD44GCYFpHYHcwYFTG1QsQBGPOLcHjk8OMOMPkd3Ew3RTaLX0ox2ES3UASxE";
		if($request->apiToken!=$apiToken){
			$this->response['code']= "0052";
			$this->response['message']= "Invalid Token";
			return Response::json($this->response);
		}
		$identity = $request->identity;
		$password = $request->password;

		
		$identityType = static::findIdentityType($identity);
		$user = User::where($identityType , $identity)->first();

		
		if(!$user){
			$this->response['code']= "0003";
			$this->response['message']= "Email/UserName doesnot exist";
			return Response::json($this->response);
		}
		elseif(!(Auth::attempt(array($identityType=>$identity,'password'=>$password)))){
			$this->response['code']= "0004";
			$this->response['message']= "Invalid password";
			return Response::json($this->response);
		}
		else{
			if(!$user->activation){
				$this->response['code']= "0031";
				$this->response['message']= "Verify your account in email";
				return Response::json($this->response);
			}
			else{
				$apiToken = str_random(30);
				DB::table('userApiTokens')->insert(['userId'=>$user->userId,
													'deviceType'=>$request->deviceType,
													'userApiToken'=>$apiToken
													]);
				$this->response['code'] = '0001';
				$this->response['message'] = 'Successfull login';
				$this->response['apiToken'] = $apiToken;
				$this->response['user'] = $user;
				return Response::json($this->response);	
			}
		}

		//  && $user->activation){
		// 	return response(json_encode(array('code'=>'0001','message'=>'Successfull login')));	
		// } 
		// elseif(Auth::attempt(array('email'=>$email,'password'=>$password)) && !$user->activation){
		// 	return response(json_encode(array('code'=>'0002','message'=>'Activate your account in email')));
		// }
		// else{
			
		// }
		//return response("hello");
	}

	public function store(Request $request)
	{
		$this->response = [];
		$apiToken = "OD44GCYFpHYHcwYFTG1QsQBGPOLcHjk8OMOMPkd3Ew3RTaLX0ox2ES3UASxE";
		// return response($request);
		if($request->apiToken!=$apiToken){
				$this->response['code']= "0052";
				$this->response['message']= "Invalid Token";
				return Response::json($this->response);
		}
		
		$user = new User;
		$confirmationCode = str_random(30);
		$validator = Validator::make($request->all(),[
			'email'=>'required | email',
			'userName'=>'required | max:25',
			'name' => 'required | max:25',
			'password' => 'required | min:8'
			]);

		if($validator->fails())
		{

			$this->response['code'] = '0014'; 
			$this->response['message'] ='Validation Errors';
			$errors = [];
			$validationErrors = json_decode($validator->errors());
			foreach ($validationErrors as $key=>$value) {
				$errors[$key] = $value[0];
			}
			$this->response['errors'] = $errors;
			return Response::json($this->response);	
		}
		else
		{
			$user->email = $request->email;
			$existingUserCount = DB::table('users')
										->where('email',$user->email)
										->count();
		

			if($existingUserCount>0){
				$this->response['code']= "0017";
				$this->response['message']= "This email is already registered";
				return Response::json($this->response);
			}
			
			$user->userName = $request->userName;
			$existingUserCount = DB::table('users')
										->where('userName',$user->userName)
										->count();

			if($existingUserCount>0){
				$this->response['code']= "0018";
				$this->response['message']= "This user name is already taken";
				return Response::json($this->response);
			}

			$user->name = $request->name;
			$user->password = bcrypt($request->password);
			$user->phone = $request->phone;
			$user->profileImage = $request->profileImage;
			$user->activation = 0;
			$user->confirmationCode = $confirmationCode;
			$user->forgotPasswordToken = "";


		// 	if($request->profileImage)
		// 	{

		// 	}
		// }
		// else
		// {
		// 	return response()->json[
		// 	'message'=>validator->errors(),
		// 	'data'=>request->all()
		// 	];
		}
		
		// \Mail::send('emails.activation', $user, function($message) use ($user) {

  //               $message->to($user['email']);

  //               $message->subject('Site - Activation Code');

  //           });

		
		$success = $user->save();
		if ($success){
			/*$mailSuccess = */
			$this->response['code']= "0013";
			$this->response['message']= "User registered";
			
			\Mail::to($user)->send(new ActivationEmail($user ));
			// if ($mailSuccess){
				$this->response['user'] = $user;
				return Response::json($this->response);
			// }
			// else{
			// 	return response(json_encode(array('code'=>'0016',
			// 								'message'=>'Could not send mail')));
			// }
		}
		else{
			$this->response['code']= "0035";
			$this->response['message']= "Database error";
			return Response::json($this->response);
		}
	}

	public function register($token)
	{
		$this->response = [];
		$user = new User;
		$user = User::where('confirmationCode',$token)->first();
		// return response($user->confirmationCode.'-'.$token);

		if(!$user)
		{
			$this->response['code']= "0033";
			$this->response['message']= "User Already active";
			return Response::json($this->response); 
		}
		
		if($user->confirmationCode == $token)
		{
			$user->activation = 1;
			$user->confirmationCode = null;
			DB::table('users')
					->where('email',$user->email)
					->update(['activation'=>1,
							  'confirmationCode'=>"NULL"]);

			// return response(json_encode($user));
			$this->response['code']= "0013";
			$this->response['message']= "User succesfully registered";
			return Response::json($this->response);
		}
		
		else
		{
			$this->response['code']= "0053";
			$this->response['message']= "Invalid request";
			return Response::json($this->response);
		}
	}

	public function logout(Request $request)
	{
		//delete from database the userapitoken upon logout
		$logoutSuccess = DB::table('userApiTokens')
							->where('userApiToken',$request->userApiToken)
							->delete();
		if($logoutSuccess){
			$this->response['code']= "0020";
			$this->response['message']= "Logged out successfully";
			return Response::json($this->response);
		}
		else{
			$this->response['code']= "0021";
			$this->response['message']= "problem Occured during logout";
			return Response::json($this->response);
		}
	}

	public function show($userId)
	{
		//
		$user = DB::table('users')->where('userId',$userId)->first();
		echo json_encode($user);
		// echo "hello";

	}

	public function update(Request $request)
	{
		$this->response = [];
		// return response(json_encode($request->userApiToken));
		$userData = DB::table('userApiTokens')
							->where('userApiToken',$request->userApiToken)
							->first();
		// return response(json_encode($userData));
		$validator = Validator::make($request->all(),[
								'name'=>'required',
								'userName'=>'required',
								'email'=>'required']);

		if(!$validator->fails()){
			$userOldData = User::where('userId',$userData->userId)->first();

			if ($userOldData->phone == $request->phone && $userOldData->name==$request->name &&
				$userOldData->email == $request->email && $userOldData->userName == $request->userName){
						$this->response['code']= "0025";
						$this->response['message']= "You enetered the same old info";
						return Response::json($this->response);
				}

			$success = User::where('userId',$userData->userId)
						  	->update(['userName'=>$request->userName,
						  			  'email'=>$request->email,
						  			  'name'=>$request->name,
						  			  'phone'=>$request->phone]);

			$user = User::where('userId',$userData->userId)->first();
			if($success){
				$this->response['code']= "0026";
				$this->response['message']= "Profile updated successfully";
				return Response::json($this->response);
			}
			else{
				$this->response['code']= "0035";
				$this->response['message']= "Database error";
				return Response::json($this->response);
			}
		}

		else
		{
			$this->response['code']= "0014";
			$this->response['message']= "Validation Errors";
			$errors = [];
			$validationErrors = json_decode($validator->errors());
			foreach ($validationErrors as $key=>$value) {
				$errors[$key] = $value[0];
			}
			$this->response['errors'] = $errors;
			return Response::json($this->response);
		}
	}

	public function changePassword(Request $request)
	{
		// return response(json_encode($request->apiToken));
		$this->response = [];
		$userData = DB::table('userApiTokens')
							->where('userApiToken',$request->apiToken)->first();
		
		
		$user = User::where('userId',$userData->userId)->first();
		
		if (!Hash::check($request->oldPassword,$user->password)){
			$this->response['code']= "0021";
			$this->response['message']= "Old password doesn\'t match";
			return Response::json($this->response);
		}

		if(is_null($request->newPassword)){
			$this->response['code']= "0027";
			$this->response['message']= "The password cannot be empty";
			return Response::json($this->response);
		}
		else{
			
			DB::table('users')->where('userId',$userData->userId)
						  	  ->update(['password'=>bcrypt($request->newPassword)]);
			$this->response['code']= "0024";
			$this->response['message']= "The password has been changed";
			$this->response['users'] = $user;
			return Response::json($this->response);
		}
	}

	public function forgotPassword(Request $request)
	{
		$this->response = [];
		$identity = $request->identity;
		$identityType = $this->findIdentityType($identity);
		

		$user = User::Where($identityType,$identity)->first();
		
		if(!$user){
			$this->response['code']= "0022";
			$this->response['message']= "Email/UserName doesnot exist";
			return Response::json($this->response);
		}

		$forgotPasswordToken = str_random(30);


		DB::table('users')->Where($identityType,$identity)
								  ->update(['forgotPasswordToken'=>$forgotPasswordToken]);
		$user = User::where($identityType,$identity)->first();
		// return response($user);
		// return response(json_encode($user));

		/*$mailSuccess = */ 
		\Mail::to($user)->send(new ForgotPasswordEmail($user));
			// if ($mailSuccess){
				$this->response['code']= "0023";
				$this->response['message']= "Password reset link has been sent to your email";
				return Response::json($this->response);			// }
			// else{
			// 	return response(json_encode(array('code'=>'0016',
			// 								'message'=>'Could not send mail')));
			// }
	}

	public function forgotPasswordForm($email, $forgotPasswordToken)
	{
		$this->response = [];
		$user = User::Where('email',$email)->first();
		if($user->forgotPasswordToken != $forgotPasswordToken){
			$this->response['code']= "0053";
			$this->response['message']= "Invalid request";
			return Response::json($this->response);
		}
		else{
			// return response($user->email);
			return view('forgotPasswordForm')->with('user',$user);
		}
	}

	public function forgotPasswordStore(Request $request)
	{
		$this->response = [];

		DB::table('users')->where('email',$request->email)
						  ->update(['password'=>bcrypt($request->password)]);

		$this->response['code']= "0024";
		$this->response['message']= "password updated successfully";
		return Response::json($this->response);
	}

	public static function findIdentityType($identity)
	{
		if (strpos($identity,'@')){
			return('email');
		}
		else{
			return('userName');	
		}
	}

	public function delete()
	{
		//
	}
}
