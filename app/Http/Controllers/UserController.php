<?php

namespace App\Http\Controllers;

use App\Image;
use App\Http\Requests\UsersRequest;
use App\Services\FileManager;
use Illuminate\Http\Request;
use App\User;
use App\ApiToken;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use App\Mail\ActivationEmail;
use App\Mail\ForgotPasswordEmail;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Response;

class UserController extends Controller
{

    public    $DEFAULTIMAGEID = 1;
    protected $response;
    protected $fileManager;
    protected $user;
    protected $apiToken;
    protected $image;


    /**
     * UserController constructor.
     * @param User        $user
     * @param FileManager $fileManager
     * @param ApiToken    $apiToken
     * @param Image   $image
     */
    public function __construct(User $user, FileManager $fileManager, ApiToken $apiToken, Image $image)
    {
        $this->fileManager = $fileManager;
        $this->user        = $user;
        $this->apiToken    = $apiToken;
        $this->image   = $image;
        $this->response = [];
    }

    /**
     * This function logs in the user.
     * @param Request $request
     * @return mixed
     */
    public function login(Request $request)
    {
        $validateInitialToken = $this->validateInitialToken($request->apiToken);
        if (!$validateInitialToken) {
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
        }
        if (!(Auth::attempt([$identityType => $identity, 'password' => $password]))) {
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
                    ['userId', '=', $user->id],
                    ['deviceType', '=', $deviceType],
                    ['deviceToken', '=', $deviceToken],
                ])->first();
                if ($existingLogin) {
                    $this->apiToken->where([
                        ['userId', '=', $user->id],
                        ['deviceToken', '=', $deviceToken],
                        ['deviceType', '=', $deviceType],
                    ])->update(['apiToken' => $apiToken]);
                } else {
                    $this->apiToken->insert(['userId'      => $user->id,
                                             'deviceType'  => $deviceType,
                                             'apiToken'    => $apiToken,
                                             'deviceToken' => $deviceToken,
                    ]);
                }
//                    $profileImage = $user->image;
//                    return Response::json($profileImage);
//                    return response(json_encode($profileImage));
//                    if ($user->profileImageId) {
//                        $image                          = $this->fileEntry->find($user->profileImageId);
                    $this->response['profileImage'] = route('file.get', $user->image->filename);
//                    }
                    $this->response['code']     = '0011';
                    $this->response['message']  = 'Successful login';
                    $this->response['apiToken'] = $apiToken;
                    $this->response['user']     = $user;

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
        $this->response       = [];
        $validateInitialToken = $this->validateInitialToken($request->apiToken);
        if (!$validateInitialToken) {
            $this->response['code']    = "0052";
            $this->response['message'] = "Invalid Token";

            return Response::json($this->response);
        }
        $data['email']               = $request->email;
        $data['userName']            = $request->userName;
        $data['name']                = $request->name;
        $data['password']            = bcrypt($request->password);
        $data['phone']               = $request->phone;
        $data['activation']          = 0;
        $data['confirmationCode']    = str_random(30);
        $data['forgotPasswordToken'] = "";
        $existingEmailCount               = $this->user->where('email', $data['email'])->count();
        if ($existingEmailCount) {
            $this->response['code']    = "0017";
            $this->response['message'] = "This email is already registered";

            return Response::json($this->response);
        }
        $existingUserNameCount = $this->user->where('userName', $data['userName'])->count();
        if ($existingUserNameCount) {
            $this->response['code']    = "0018";
            $this->response['message'] = "This user name is already taken";

            return Response::json($this->response);
        }
        $user = $this->user->create($data);

        if($request->hasFile('profileImage')){
            $files = $request->file('profileImage');
            $user = $this->fileManager->saveFile($user, $files, "user");
        }
        $this->response['user']    = $user;

        return Response::json($this->response);
        if ($request->hasFile('profileImage') &&
            $request->file('profileImage')->isValid()
        ) {
            $file      = $request->file('profileImage');
            $extension = $file->getClientOriginalExtension();
            $filename  = str_random(20).$file->getFilename().'.'.$extension;

            try {
                Storage::disk('local')->put($filename, File::get($file));
            } catch (Exception $e) {
                return null;
            }
            $image = new Image;
            $image->mime              = $file->getClientMimeType();
            $image->original_filename = $file->getClientOriginalName();
            $image->filename          = $filename;
            $user->image()->save($image);
        }
        if ($user) {
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

    public function saveProfileImage($request)
    {
        if ($request->hasFile('profileImage') &&
            $request->file('profileImage')->isValid()
        ) {
            $file  = $request->file('profileImage');
            $entry = $this->fileManager->saveFile($file);

            return ($entry->id);
        }

        return $DEFAULTIMAGEID;
    }

    public function register($token)
    {
        $this->response = [];
        $user           = $this->user->where('confirmationCode', $token)->first();
        if (!$user) {
            $this->response['code']    = "0033";
            $this->response['message'] = "User Already active/ Invalid Token";

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
        try {
            $this->apiToken->where('userApiToken', $request->userApiToken)->delete();
        } catch (exception $e) {
            $this->response['code']    = "0021";
            $this->response['message'] = "Problem occurred during logout";

            return Response::json($this->response);
        }
        $this->response['code']    = "0020";
        $this->response['message'] = "Logged out successfully";

        return Response::json($this->response);
    }

    public function show($userId)
    {
        $user = $this->user->where('userId', $userId)->first();
        echo json_encode($user);
    }

    public function update(Request $request)
    {
        $userData       = $this->getLoggedUser($request->apiToken);
        $this->response = [];
        $user = $this->user->where('userId', '=', $userData->userId)
            ->update(['email'    => $request->email,
                      'name'     => $request->name,
                      'userName' => $request->userName,
                      'phone'    => $request->phone
            ]);
        $this->response['code']    = "0026";
        $this->response['message'] = "profile updated successfully";
        $this->response['user']    = $user;

        return Response::json($this->response);
    }

    public function getLoggedUser($apiToken)
    {
        return ($this->apiToken->where('apiToken', $apiToken)->first());
    }

    public function changePassword(Request $request)
    {
        $this->response = [];
        $userData       = $this->getLoggedUser($request->apiToken);
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
            $this->user->where('userId', $userData->userId)
                ->update(['password' => bcrypt($request->newPassword)]);
            $this->response['code']    = "0024";
            $this->response['message'] = "The password has been changed";
            $this->response['user']    = $user;

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
        $user = $this->user->Where($identityType, $identity)
            ->update(['forgotPasswordToken' => $forgotPasswordToken]);
        \Mail::to($user)->send(new ForgotPasswordEmail($user));
        $this->response['code']    = "0023";
        $this->response['message'] = "Password reset link has been sent to your email";

        return Response::json($this->response);
    }

    public function showForgotPasswordForm($email, $forgotPasswordToken)
    {
        $this->response = [];
        $user           = $this->user->Where('email', $email)->first();
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
        $this->user->where('email', $request->email)
            ->update(['password' => bcrypt($request->password)]);
        $this->response            = [];
        $this->response['code']    = "0024";
        $this->response['message'] = "password updated successfully";

        return Response::json($this->response);
    }

    public function updateProfileImage(Request $request)
    {
        $this->response            = [];
        $this->user                = getLoggedUser($request->apiToken);
        $updatedImageId            = $this->saveProfileImage($request);
        $updatedUser               = User::where('userId', '=', $user->userId)
            ->update(['profileImage' => $updatedImageId]);
        $this->response['code']    = '0026';
        $this->response['message'] = 'Profile updated successfully';
        $this->response['user']    = $updatedUser;

        return Response::json($this->response);
    }



}
