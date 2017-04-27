<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UsersRequest extends FormRequest
{
    protected $response;

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $rules = [
            'email'        => 'required | email | max:35 | unique:users,email',
            'userName'     => 'required | min:4 | max:25| unique:users,username',
            'name'         => 'required | max:25',
            //'profileImage' => 'mimes:jpeg,bmp,png,jpg',
            'phone'        => 'required'
        ];

        if (request()->method() === "POST") {
            $rules['password'] = 'required | min:8';
        }

        return $rules;
    }

    /**
     * Returns the response in JSON Format.
     * @param array $errors
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function response(array $errors)
    {

//        return response(request());
        return response([
            "errors"  => $errors,
            "code"    => "0014",
            "message" => "Validation errors",
        ]);
    }
}
