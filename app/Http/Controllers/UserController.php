<?php

namespace App\Http\Controllers;

use App\Services\UserService;
use Illuminate\Http\Request;
use Response;


/**
 * Class UserController
 * @package App\Http\Controllers
 */
class UserController extends Controller
{
    protected $response = [];
    protected $userService;

    /**
     * UserController constructor.
     * @param UserService $userService
     */
    public function __construct(UserService $userService)
    {
        $this->userService = $userService;

    }

    /**
     * This function logs in the user.
     * @param Request $request
     * @return Response
     */
    public function login(Request $request)
    {
        $this->response = $this->userService->login($request);

        return Response::json($this->response);
    }


    /**
     * stores the userData in the database
     * @param Request $request
     * @return Response
     */
    public function store(Request $request)
    {
        $this->response = $this->userService->store($request);

        return Response::json(($this->response));
    }


    /**
     * activates the user
     * @param $token
     * @return Response
     */
    public function activation($token)
    {
        $this->response = $this->userService->activation($token);


        return Response::json($this->response);
    }


    /**
     * logs out the user
     * @param Request $request
     * @return Response
     */
    public function logout(Request $request)
    {
        $this->response = $this->userService->logout($request);

        return Response::json($this->response);
    }

    /**
     * shows the user details
     * @param $userId
     */
    public function show($userId)
    {
        $user = $this->user->where('userId', $userId)->first();
        echo json_encode($user);
    }

    /**
     * updates the user info
     * @param Request $request
     * @return Response
     */
    public function update(Request $request)
    {
        $this->response = $this->userService->update($request);

        return Response::json($this->response);
    }


    /**
     * updates the password
     * @param Request $request
     * @return Response
     */
    public function changePassword(Request $request)
    {
        $this->response = $this->userService->changePassword($request);

        return Response::json($this->response);
    }

    /**
     * sends the forgot-password email if the user forgets his/her password
     * @param Request $request
     * @return Response
     */
    public function forgotPassword(Request $request)
    {
        $this->response = $this->userService->forgotPassword($request);

        return Response::json($this->response);
    }

    /**
     * displays the forgot password form to update the password
     * @param $email
     * @param $forgotPasswordToken
     * @return UserController|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function showForgotPasswordForm($email, $forgotPasswordToken)
    {
        $user = $this->userService->getUserDataFromEmail($email);
        if ($user->forgotPasswordToken != $forgotPasswordToken) {
            $this->response['code']    = "0053";
            $this->response['message'] = "Invalid request";

            return Response::json($this->response);
        } else {
            return view('forgotPasswordForm')->with('user', $user);
        }
    }

    /**
     * updates the password acquired from the forgot password form
     * @param Request $request
     * @return Response
     */
    public function forgotPasswordStore(Request $request)
    {
        $this->response = $this->userService->forgotPasswordStore($request);

        return Response::json($this->response);
    }

    /**
     * updates the profile image
     * @param Request $request
     * @return Response
     */
    public function updateProfileImage(Request $request)
    {
        $this->response = $this->userService->updateProfileImage($request);

        return Response::json($this->response);
    }
}
