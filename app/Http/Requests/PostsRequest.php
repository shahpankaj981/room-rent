<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PostsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'title' => 'required | min:5 | max:40',
            'location' => 'required',
            'description' => 'required | max:250',
            'price' => 'required | numeric',
            'latitude' => 'required | geolocation',
            'longitude' => 'required | geolocation',
            'numberOfRooms' => 'required | numeric',
            'postType' => 'required | int',
            'images' => 'mimes: jpeg, jpg, bmp, png'
        ];
    }

    public function response(array $errors)
    {
        return([
            'errors' => $errors,
            'code' => '0014',
            'message' => 'Validation Errors'
        ]);
    }
}
