<?php

namespace App\Services;

use App\Image;
use Illuminate\Http\Request;
use App\User;
use App\ApiToken;
use Illuminate\Support\Facades\Auth;
use App\Mail\ActivationEmail;
use App\Mail\ForgotPasswordEmail;
use Illuminate\Support\Facades\Hash;

class UserService
{
    const     ACTIVE   = 1;
    const     INACTIVE = 0;
    protected $response = [];
    protected $fileManager;
    protected $user;
    protected $apiToken;
    protected $image;

    public function __construct(User $user, FileManager $fileManager, ApiToken $apiToken,
                                Image $image)
    {
        $this->fileManager = $fileManager;
        $this->user        = $user;
        $this->apiToken    = $apiToken;
        $this->image       = $image;
    }

    /**
     * Service function that logs in a user and return the new apiToken
     * @param Request $request
     * @return array
     */
    public function login(Request $request)
    {
        $validateInitialToken = $this->validateInitialToken($request->header('Authorization'));
        if (!$validateInitialToken) {
            $this->response['code']    = "0052";
            $this->response['message'] = "Invalid Token";

            return ($this->response);
        }
        $identity     = $request->identity;
        $password     = $request->password;
        $identityType = $this->findIdentityType($identity);
        $user         = $this->user->where($identityType, $identity)->first();
        if (!$user) {
            $this->response['code']    = "0003";
            $this->response['message'] = "Email/UserName does not exist";

            return ($this->response);
        }

        if (!$this->passwordMatch($identityType, $identity, $password)) {
            $this->response['code']    = "0004";
            $this->response['message'] = "Invalid password";

            return ($this->response);
        }

        if (!$this->isActive($user)) {
            $this->response['code']    = "0031";
            $this->response['message'] = "Verify your account in email";

            return ($this->response);
        }
        $apiToken                   = $this->generateApiToken($user, $request);
        $user->profileImage         = $this->image->where('userId', $user->id)->pluck('filename')->first();
        $this->response['code']     = '0011';
        $this->response['message']  = 'Successful login';
        $this->response['apiToken'] = $apiToken;
        $this->response['user']     = $user;

        return ($this->response);
    }

    /**
     * checks if the user is active
     * @param $user
     * @return bool
     */
    public function isActive($user)
    {
        return ($user->activation ? true : false);
    }

    /**
     * checks if the password is correct
     * @param $identityType
     * @param $identity
     * @param $password
     * @return bool
     */
    public function passwordMatch($identityType, $identity, $password)
    {
        return (Auth::attempt([$identityType => $identity, 'password' => $password]) ? true : false);
    }

    /**
     * generates the apiToken for recently logged in user
     * @param User $user
     * @param      $request
     * @return string
     */
    public function generateApiToken(User $user, $request)
    {
        $apiToken = str_random(30);
        $data     = ['userId'      => $user->id,
                     'deviceType'  => $request->deviceType,
                     'deviceToken' => $request->deviceToken];
        $this->apiToken->updateOrCreate($data, ['apiToken' => $apiToken]);

        return $apiToken;
    }

    /**
     * validates whether the app is used by the authorized app or not
     * @param $token
     * @return bool
     */
    public function validateInitialToken($token)
    {
        $apiToken = "Bearer OD44GCYFpHYHcwYFTG1QsQBGPOLcHjk8OMOMPkd3Ew3RTaLX0ox2ES3UASxE";

        return ($apiToken == $token) ? true : false;
    }

    /**
     * finds the identity type, i.e. if the identity entered by user is email or userName
     * @param $identity
     * @return string
     */
    protected function findIdentityType($identity)
    {
        return (strpos($identity, '@')) ? 'email' : 'userName';
    }

    /**
     * service function that helps in storing the data of a new user in the database
     * @param Request $request
     * @return array
     */
    public function store(Request $request)
    {
        $this->response       = [];
        $validateInitialToken = $this->validateInitialToken($request->header('Authorization'));
        if (!$validateInitialToken) {
            $this->response['code']    = "0052";
            $this->response['message'] = "Invalid Token";

            return ($this->response);
        }
        $data                        = $this->fetchUserData($request);
        $data['activation']          = self::INACTIVE;
        $data['confirmationCode']    = str_random(30);
        $data['forgotPasswordToken'] = "";


        if ($this->emailExists($data['email'])) {
            $this->response['code']    = "0017";
            $this->response['message'] = "This email is already registered";

            return ($this->response);
        }

        if ($this->UserNameTaken($data['userName'])) {
            $this->response['code']    = "0018";
            $this->response['message'] = "This user name is already taken";

            return ($this->response);
        }
        $user                   = $this->user->create($data);
        $user                   = $this->saveProfileImage($request, $user);
        $this->response['user'] = $user;
        if ($user) {
            \Mail::to($user)->send(new ActivationEmail($user));
            $this->response['code']    = "0013";
            $this->response['message'] = "User registered";
            $this->response['user']    = $user;

            return ($this->response);
        } else {
            $this->response['code']    = "0035";
            $this->response['message'] = "Database error";

            return ($this->response);
        }
    }

    /**
     * fetches the user data entered by the user
     * @param $request
     * @return mixed
     */
    public function fetchUserData($request)
    {
        $data['email']    = $request->email;
        $data['userName'] = $request->userName;
        $data['name']     = $request->name;
        $data['password'] = bcrypt($request->password);
        $data['phone']    = $request->phone;


        return $data;
    }

    /**
     * checks if the email already exists
     * @param $email
     * @return bool
     */
    public function emailExists($email)
    {
        return ($this->user->where('email', $email)->count() ? true : false);
    }

    /**
     * checks if the userName is already taken
     * @param $userName
     * @return bool
     */
    public function userNameTaken($userName)
    {
        return ($this->user->where('userName', $userName)->count() ? true : false);
    }

    /**
     * saves the profile image to the database if exists
     * @param $request
     * @param $user
     * @return null|string
     */
    public function saveProfileImage($request, $user)
    {
        if ($request->hasFile('profileImage')) {
            $files = $request->file('profileImage');
            $user  = $this->fileManager->saveFile($user, $files, "user");
        }

        return $user;
    }

    /**
     * activates the user when s/he clicks the activqation link in his/her email
     * @return array
     */
    public function activation()
    {
        $user = $this->user->where('confirmationCode', $token)->first();
        if (!$user) {
            $this->response['code']    = "0033";
            $this->response['message'] = "User Already active/ Invalid Token";

            return ($this->response);
        }
        if ($user->confirmationCode == $token) {
            $this->user->where('email', $user->email)
                ->update(['activation'       => self::ACTIVE,
                          'confirmationCode' => "NULL"]);
            $this->response['code']    = "0013";
            $this->response['message'] = "User succesfully registered";
        } else {
            $this->response['code']    = "0053";
            $this->response['message'] = "Invalid request";
        }

        return ($this->response);
    }

    /**
     * logs out a user deleting the token that was created during login
     * @param Request $request
     * @return array
     */
    public function logout(Request $request)
    {
        $apiToken = $this->getApiToken($request->header('Authorization'));

        try {
            $this->apiToken->where('apiToken', $apiToken)->delete();
        } catch (exception $e) {
            $this->response['code']    = "0021";
            $this->response['message'] = "Problem occurred during logout";

            return Response::json($this->response);
        }
        $this->response['code']    = "0020";
        $this->response['message'] = "Logged out successfully";

        return ($this->response);
    }

    /**
     * service function that updates the user info
     * @param Request $request
     * @return array
     */
    public function update(Request $request)
    {
        $userData                  = $this->getLoggedUser($request->header('Authorization'));
        $this->response            = [];
        $user                      = $this->user->where('userId', '=', $userData->userId)
            ->update(['email'    => $request->email,
                      'name'     => $request->name,
                      'userName' => $request->userName,
                      'phone'    => $request->phone,
            ]);
        $this->response['code']    = "0026";
        $this->response['message'] = "profile updated successfully";
        $this->response['user']    = $user;

        return ($this->response);
    }

    /**
     * returns the logged in user from the apiToken
     * @param $header
     * @return mixed
     */
    public function getLoggedUser($header)
    {
        $apiToken = $this->getApiToken($header);

        return ($this->apiToken->where('apiToken', $apiToken)->first());
    }


    /**
     * extracts the apiToken from the header
     * @param $header
     * @return mixed
     */
    public function getApiToken($header)
    {
        $string = explode(" ", $header);

        return ($string[1]);
    }

    /**
     * changes the password of  a user
     * @param Request $request
     * @return array
     */
    public function changePassword(Request $request)
    {
        $userData = $this->getLoggedUser($request->header('Authorization'));
        $user     = $this->user->where('userId', $userData->userId)->first();
        if (!Hash::check($request->oldPassword, $user->password)) {
            $this->response['code']    = "0021";
            $this->response['message'] = "Old password doesn\'t match";

            return ($this->response);
        }
        if (is_null($request->newPassword)) {
            $this->response['code']    = "0027";
            $this->response['message'] = "The password cannot be empty";

            return ($this->response);
        }
        $this->user->where('userId', $userData->userId)
            ->update(['password' => bcrypt($request->newPassword)]);
        $this->response['code']    = "0024";
        $this->response['message'] = "The password has been changed";
        $this->response['user']    = $user;

        return ($this->response);
    }

    /**
     * sends the email to reset password
     * @param Request $request
     * @return array
     */
    public function forgotPassword(Request $request)
    {
        $identity     = $request->identity;
        $identityType = $this->findIdentityType($identity);
        $user         = $this->user->Where($identityType, $identity)->first();
        if (!$user) {
            $this->response['code']    = "0022";
            $this->response['message'] = "Email/UserName doesnot exist";

            return ($this->response);
        }
        $forgotPasswordToken = str_random(30);
        $user                = $this->user->Where($identityType, $identity)
            ->update(['forgotPasswordToken' => $forgotPasswordToken]);
        \Mail::to($user)->send(new ForgotPasswordEmail($user));
        $this->response['code']    = "0023";
        $this->response['message'] = "Password reset link has been sent to your email";

        return ($this->response);
    }

    /**
     * get user info from the email
     * @param $email
     * @return mixed
     */
    public function getUserDataFromEmail($email)
    {
        return ($this->user->Where('email', $email)->first());
    }

    /**
     * stores the password that was received from the forgot  password form
     * @param Request $request
     * @return array
     */
    public function forgotPasswordStore(Request $request)
    {
        $this->user->where('email', $request->email)
            ->update(['password' => bcrypt($request->password)]);
        $this->response            = [];
        $this->response['code']    = "0024";
        $this->response['message'] = "password updated successfully";

        return ($this->response);
    }

    /**
     * service function that updates the profile image of the logged in user
     * @param Request $request
     * @return array
     */
    public function updateProfileImage(Request $request)
    {
        $this->response            = [];
        $user                      = $this->getLoggedUser($request->header('Authorization'));
        $updatedUser               = $this->saveProfileImage($request, $user);
        $this->response['code']    = '0026';
        $this->response['message'] = 'Profile updated successfully';
        $this->response['user']    = $updatedUser;

        return ($this->response);
    }
}