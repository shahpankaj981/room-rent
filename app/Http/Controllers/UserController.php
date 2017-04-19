<?php

namespace App\Http\Controllers;

use App\ApiToken;
use App\Fileentry;
use App\Http\Requests\UsersRequest;
use App\Mail\ActivationEmail;
use App\Mail\ForgotPasswordEmail;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Response;

class UserController extends Controller
{
    public $DEFAULTIMAGEID = 1;
    protected $response;
    protected $fileentryController;
    protected $user;
    protected $auth;

    /**
     * UserController constructor.
     * @param User $user
     * @param Auth $auth
     */
    public function __construct(User $user)
    {
        $this->fileentryController = new FileentryController;
        $this->user = $user;
    }

    /**
     * This function logs in the user.
     * @param Request $request
     * @return mixed
     */
    public function login(Request $request)
    {
        $this->response = [];
        $validateInitialToken = $this->validateInitialToken($request->apiToken);
        if (!$validateInitialToken) {
            $this->response['code'] = "0052";
            $this->response['message'] = "Invalid Token";
            return Response::json($this->response);
        }
        $identity = $request->identity;
        $password = $request->password;
        $deviceType = $request->deviceType;
        $deviceToken = $request->deviceToken;
        $identityType = $this->findIdentityType($identity);
        $user = User::where($identityType, $identity)->first();
        if (!$user) {
            $this->response['code'] = "0003";
            $this->response['message'] = "Email/UserName does not exist";
            return Response::json($this->response);
        }
        if (!(Auth::attempt([$identityType => $identity, 'password' => $password]))) {
            $this->response['code'] = "0004";
            $this->response['message'] = "Invalid password";
            return Response::json($this->response);
        } else {
            if (!$user->activation) {
                $this->response['code'] = "0031";
                $this->response['message'] = "Verify your account in email";
                return Response::json($this->response);
            } else {
                $apiToken = str_random(30);
                $existingLogin = ApiToken::where([
                    ['userId',      '=', $user->userId],
                    ['deviceType',  '=', $deviceType],
                    ['deviceToken', '=', $deviceToken],
                ])->first();
                if ($existingLogin) {
                    ApiToken::where([
                        ['userId',      '=', $user->userId],
                        ['deviceToken', '=', $deviceToken],
                        ['deviceType',  '=', $deviceType],
                    ])->update(['apiToken' => $apiToken]);
                } else {
                    ApiToken::insert(['userId' => $user->userId,
                        'deviceType' => $deviceType,
                        'apiToken' => $apiToken,
                        'deviceToken' => $deviceToken,
                    ]);
                }
                if ($user->profileImage) {
                    $image = Fileentry::find($user->profileImage);
                    $this->response['profileImage'] = route('file.get', $image->filename);
                }
                $this->response['code'] = '0011';
                $this->response['message'] = 'Successful login';
                $this->response['apiToken'] = $apiToken;
                $this->response['user'] = $user;
                return Response::json($this->response);
            }
        }
    }

    public function validateInitialToken($token)
    {
        $apiToken = "OD44GCYFpHYHcwYFTG1QsQBGPOLcHjk8OMOMPkd3Ew3RTaLX0ox2ES3UASxE";
        return ($apiToken == $token) ? true : false;
    }

    protected function findIdentityType($identity)
    {
        return (strpos($identity, '@')) ? 'email' : 'userName';
    }

    public function store(UsersRequest $request)
    {
        $this->response = [];
        $validateInitialToken = $this->validateInitialToken($request->apiToken);
        if (!$validateInitialToken) {
            $this->response['code'] = "0052";
            $this->response['message'] = "Invalid Token";
            return Response::json($this->response);
        }
        $data['email'] = $request->email;
        $data['userName'] = $request->userName;
        $data['name'] = $request->name;
        $data['password'] = bcrypt($request->password);
        $data['phone'] = $request->phone;
        $data['activation'] = 0;
        $data['confirmationCode'] = str_random(30);
        $data['forgotPasswordToken'] = "";
        $existingEmail = User::where('email', $data['email'])->count();
        if ($existingEmail) {
            $this->response['code'] = "0017";
            $this->response['message'] = "This email is already registered";
            return Response::json($this->response);
        }
        $existingUserName = User::where('userName', $data['userName'])->count();
        if ($existingUserName) {
            $this->response['code'] = "0018";
            $this->response['message'] = "This user name is already taken";
            return Response::json($this->response);
        }
        $data['profileImage'] = saveProfileImage($request);

        $user = User::create($data);
        if ($user) {
            \Mail::to($user)->send(new ActivationEmail($user));
            $this->response['code'] = "0013";
            $this->response['message'] = "User registered";
            $this->response['user'] = $user;
            return Response::json($this->response);
        } else {
            $this->response['code'] = "0035";
            $this->response['message'] = "Database error";
            return Response::json($this->response);
        }
    }

    public function register($token)
    {
        $this->response = [];
        $user = $this->user->where('confirmationCode', $token)->first();
        if (!$user) {
            $this->response['code'] = "0033";
            $this->response['message'] = "User Already active/ Invalid Token";
            return Response::json($this->response);
        }
        if ($user->confirmationCode == $token) {
            $user->activation = 1;
            $user->confirmationCode = null;
            User::where('email', $user->email)
                ->update(['activation' => 1,
                    'confirmationCode' => "NULL"]);
            $this->response['code'] = "0013";
            $this->response['message'] = "User succesfully registered";
            return Response::json($this->response);
        } else {
            $this->response['code'] = "0053";
            $this->response['message'] = "Invalid request";
            return Response::json($this->response);
        }
    }

    public function logout(Request $request)
    {
        try {
            ApiToken::where('userApiToken', $request->userApiToken)->delete();
        } catch (exception $e) {
            $this->response['code'] = "0021";
            $this->response['message'] = "Problem occurred during logout";
            return Response::json($this->response);
        }
        $this->response['code'] = "0020";
        $this->response['message'] = "Logged out successfully";
        return Response::json($this->response);
    }

    public function show($userId)
    {
        $user = DB::table('users')->where('userId', $userId)->first();
        echo json_encode($user);
    }

    public function update(Request $request)
    {
        $userData = $this->getLoggedUser($request->apiToken);
        $this->response = [];
        User::where('userId', '=', $userData->userId)
            ->update(['email' => $request->email,
                'name' => $request->name,
                'userName' => $request->userName,
                'phone' => $request->phone,
            ]);
        $user = User::where('userId', $userData->userId);
        $this->response['code'] = "0026";
        $this->response['message'] = "profile updated successfully";
        $this->response['user'] = $user;
        return Response::json($this->response);
    }

    public function getLoggedUser($apiToken)
    {
        return (ApiToken::where('apiToken', $apiToken)->first());
    }

    public function changePassword(Request $request)
    {
        $this->response = [];
        $userData = $this->getLoggedUser($request->apiToken);
        $user = User::where('userId', $userData->userId)->first();
        if (!Hash::check($request->oldPassword, $user->password)) {
            $this->response['code'] = "0021";
            $this->response['message'] = "Old password doesn\'t match";
            return Response::json($this->response);
        }
        if (is_null($request->newPassword)) {
            $this->response['code'] = "0027";
            $this->response['message'] = "The password cannot be empty";
            return Response::json($this->response);
        } else {
            User::where('userId', $userData->userId)
                ->update(['password' => bcrypt($request->newPassword)]);
            $this->response['code'] = "0024";
            $this->response['message'] = "The password has been changed";
            $this->response['user'] = $user;
            return Response::json($this->response);
        }
    }

    public function forgotPassword(Request $request)
    {
        $this->response = [];
        $identity = $request->identity;
        $identityType = $this->findIdentityType($identity);
        $user = User::Where($identityType, $identity)->first();
        if (!$user) {
            $this->response['code'] = "0022";
            $this->response['message'] = "Email/UserName doesnot exist";
            return Response::json($this->response);
        }
        $forgotPasswordToken = str_random(30);
        User::Where($identityType, $identity)
            ->update(['forgotPasswordToken' => $forgotPasswordToken]);
        $user = User::where($identityType, $identity)->first();
        \Mail::to($user)->send(new ForgotPasswordEmail($user));
        $this->response['code'] = "0023";
        $this->response['message'] = "Password reset link has been sent to your email";
        return Response::json($this->response);
    }

    public function showForgotPasswordForm($email, $forgotPasswordToken)
    {
        $this->response = [];
        $user = User::Where('email', $email)->first();
        if ($user->forgotPasswordToken != $forgotPasswordToken) {
            $this->response['code'] = "0053";
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
        $this->response = [];
        $this->response['code'] = "0024";
        $this->response['message'] = "password updated successfully";
        return Response::json($this->response);
    }

    public function updateProfileImage(Request $request)
    {
        $this->response = [];
        $this->user = getLoggedUser($request->apiToken);
        $updatedImageId = $this->saveProfileImage($request);
        $updatedUser = User::where('userId', '=', $user->userId)
            ->update(['profileImage' => $updatedImageId]);
        $this->response['code'] = '0026';
        $this->response['message'] = 'Profile updated successfully';
        $this->response['user'] = $updatedUser;
        return Response::json($this->response);
    }

    public function saveProfileImage($request)
    {
        if ($request->hasFile('profileImage') &&
            $request->file('profileImage')->isValid()
        ) {
            $file = $request->file('profileImage');
            $entry = $this->fileentryController->save($file);
            return ($entry->id);
        }
        return $DEFAULTIMAGEID;
    }

    public function test(Request $request)
    {
        $path = $request->photo->store('images');
    }
}
