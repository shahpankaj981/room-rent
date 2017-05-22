<?php

namespace App\Transformers;

class UserTransformer extends Transformer
{
    public function transform($user)
    {
        return [
            'id'               => $user->id,
            'userName'         => $user->userName,
            'email'            => $user->email,
            'name'             => $user->name,
            'phone'            => $user->phone,
            'profileImage'     => $user->profileImage,
            'registrationDate' => $user->created_at->toDateString()
        ];
    }
}