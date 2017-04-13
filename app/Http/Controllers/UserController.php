<?php

namespace App\Http\Controllers;

use App\Http\Requests\UsersRequest;
use Illuminate\Http\Request;
use App\User;
use App\ApiToken;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Mail\ActivationEmail;
use App\Mail\ForgotPasswordEmail;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Response;

class UserController extends Controller
{
    protected $response;

    protected $user;
    protected $auth;

    /**
     * UserController constructor.
     * @param User $user
     * @param Auth $auth
     */
    public function __construct(User $user, Auth $auth)
    {
        $this->user = $user;
        $this->auth = $auth;
    }

    /**
     * This function logs in the user.
     * @param Request $request
     * @return mixed
     */
    public function login(Request $request)
    {
        $this->response = [];
        $apiToken       = "OD44GCYFpHYHcwYFTG1QsQBGPOLcHjk8OMOMPkd3Ew3RTaLX0ox2ES3UASxE";
        if ($request->apiToken != $apiToken) {
            $this->response['code']    = "0052";
            $this->response['message'] = "Invalid Token";

            return Response::json($this->response);
        }
        $identity     = $request->identity;
        $password     = $request->password;
        $deviceType   = $request->deviceType;
        $deviceToken  = $request->deviceToken;
        $identityType = $this->findIdentityType($identity);
        $user         = $this->user->where($identityType, $identity)->first();

        if (!$user) {
            $this->response['code']    = "0003";
            $this->response['message'] = "Email/UserName does not exist";

            return Response::json($this->response);
        } elseif (!(Auth::attempt([$identityType => $identity, 'password' => $password]))) {
            $this->response['code']    = "0004";
            $this->response['message'] = "Invalid password";

            return Response::json($this->response);
        } else {
            if (!$user->activation) {
                $this->response['code']    = "0031";
                $this->response['message'] = "Verify your account in email";

                return Response::json($this->response);
            } else {
                $apiToken      = str_random(30);
                $existingLogin = ApiToken::where([
                    ['userId', '=', $user->userId],
                    ['deviceType', '=', $deviceType],
                    ['deviceToken', '=', $deviceToken],
                ])->first();
                if ($existingLogin) {
                    ApiToken::where([
                        ['userId', '=', $user->userId],
                        ['deviceToken', '=', $deviceToken],
                        ['deviceType', '=', $deviceType],
                    ])->update(['apiToken' => $apiToken]);
                } else {
                    ApiToken::insert(['userId'      => $user->userId,
                                      'deviceType'  => $deviceType,
                                      'apiToken'    => $apiToken,
                                      'deviceToken' => $deviceToken,
                    ]);
                }
                $this->response['code']     = '0011';
                $this->response['message']  = 'Successful login';
                $this->response['apiToken'] = $apiToken;
                $this->response['user']     = $user;

                return Response::json($this->response);
            }
        }
    }

    public function store(UsersRequest $request)
    {
        $this->response = [];
        $apiToken       = "OD44GCYFpHYHcwYFTG1QsQBGPOLcHjk8OMOMPkd3Ew3RTaLX0ox2ES3UASxE";

        if ($request->apiToken != $apiToken) {
            $this->response['code']    = "0052";
            $this->response['message'] = "Invalid Token";

            return Response::json($this->response);
        }
        $user              = new User;
        $confirmationCode  = str_random(30);
        $user->email       = $request->email;
        $existingUserCount = DB::table('users')
            ->where('email', $user->email)
            ->count();
        if ($existingUserCount > 0) {
            $this->response['code']    = "0017";
            $this->response['message'] = "This email is already registered";

            return Response::json($this->response);
        }

        $user->userName    = $request->userName;
        $existingUserCount = DB::table('users')
            ->where('userName', $user->userName)
            ->count();

        if ($existingUserCount > 0) {
            $this->response['code']    = "0018";
            $this->response['message'] = "This user name is already taken";

            return Response::json($this->response);
        }

        $user->name                = $request->name;
        $user->password            = bcrypt($request->password);
        $user->phone               = $request->phone;
        $user->profileImage        = $request->profileImage;
        $user->activation          = 0;
        $user->confirmationCode    = $confirmationCode;
        $user->forgotPasswordToken = "";

        //For profile Image

        $success = $user->save();
        if ($success) {
            \Mail::to($user)->send(new ActivationEmail($user));
            $this->response['code']    = "0013";
            $this->response['message'] = "User registered";
            $this->response['user']    = $user;

            return Response::json($this->response);
        } else {
            $this->response['code']    = "0035";
            $this->response['message'] = "Database error";

            return Response::json($this->response);
        }
    }

    public function register($token)
    {
        $this->response = [];
        $user           = $this->user->where('confirmationCode', $token)->first();

        if (!$user) {
            $this->response['code']    = "0033";
            $this->response['message'] = "User Already active";

            return Response::json($this->response);
        }

        if ($user->confirmationCode == $token) {
            $user->activation       = 1;
            $user->confirmationCode = null;

            User::where('email', $user->email)
                ->update(['activation'       => 1,
                          'confirmationCode' => "NULL"]);

            $this->response['code']    = "0013";
            $this->response['message'] = "User succesfully registered";

            return Response::json($this->response);
        } else {
            $this->response['code']    = "0053";
            $this->response['message'] = "Invalid request";

            return Response::json($this->response);
        }
    }

    public function logout(Request $request)
    {
        $logoutSuccess = ApiToken::where('userApiToken', $request->userApiToken)
            ->delete();
        if ($logoutSuccess) {
            $this->response['code']    = "0020";
            $this->response['message'] = "Logged out successfully";

            return Response::json($this->response);
        } else {
            $this->response['code']    = "0021";
            $this->response['message'] = "Problem occurred during logout";

            return Response::json($this->response);
        }
    }

    public function show($userId)
    {
        $user = DB::table('users')->where('userId', $userId)->first();
        echo json_encode($user);
    }

    public function update(UsersRequest $request)
    {
        $userData       = ApiToken::where('apiToken', $request->apiToken)->first();
        $this->response = [];

        User::where('userId', '=', $userData->userId)
            ->update(['email'    => $request->email,
                      'name'     => $request->name,
                      'userName' => $request->userName,
                      'phone'    => $request->phone,
            ]);

        $user                      = User::where('userId', $userData->userId);
        $this->response['code']    = "0026";
        $this->response['message'] = "profile updated successfully";
        $this->response['user']    = $user;

        return Response::json($this->response);
    }

    public function changePassword(Request $request)
    {
        $this->response = [];
        $userData       = ApiToken::where('userApiToken', $request->apiToken)->first();
        $user           = User::where('userId', $userData->userId)->first();

        if (!Hash::check($request->oldPassword, $user->password)) {
            $this->response['code']    = "0021";
            $this->response['message'] = "Old password doesn\'t match";

            return Response::json($this->response);
        }
        if (is_null($request->newPassword)) {
            $this->response['code']    = "0027";
            $this->response['message'] = "The password cannot be empty";

            return Response::json($this->response);
        } else {
            User::where('userId', $userData->userId)
                ->update(['password' => bcrypt($request->newPassword)]);
            $this->response['code']    = "0024";
            $this->response['message'] = "The password has been changed";
            $this->response['user']   = $user;

            return Response::json($this->response);
        }
    }

    public function forgotPassword(Request $request)
    {
        $this->response = [];
        $identity       = $request->identity;
        $identityType   = $this->findIdentityType($identity);
        $user           = User::Where($identityType, $identity)->first();

        if (!$user) {
            $this->response['code']    = "0022";
            $this->response['message'] = "Email/UserName doesnot exist";

            return Response::json($this->response);
        }
        $forgotPasswordToken = str_random(30);

        User::Where($identityType, $identity)
            ->update(['forgotPasswordToken' => $forgotPasswordToken]);

        $user = User::where($identityType, $identity)->first();

        \Mail::to($user)->send(new ForgotPasswordEmail($user));

        $this->response['code']    = "0023";
        $this->response['message'] = "Password reset link has been sent to your email";

        return Response::json($this->response);
    }

    public function showForgotPasswordForm($email, $forgotPasswordToken)
    {
        $this->response = [];
        $user           = User::Where('email', $email)->first();
        if ($user->forgotPasswordToken != $forgotPasswordToken) {
            $this->response['code']    = "0053";
            $this->response['message'] = "Invalid request";

            return Response::json($this->response);
        } else {
            return view('forgotPasswordForm')->with('user', $user);
        }
    }

    public function forgotPasswordStore(Request $request)
    {
        User::where('email', $request->email)
            ->update(['password' => bcrypt($request->password)]);

        $this->response            = [];
        $this->response['code']    = "0024";
        $this->response['message'] = "password updated successfully";

        return Response::json($this->response);
    }

    protected function findIdentityType($identity)
    {
        return (strpos($identity, '@')) ? 'email' : 'userName';
    }

    public function checkValidApiToken($apiToken)
    {
        //Check Valid api token
        //to be done later
    }
}
