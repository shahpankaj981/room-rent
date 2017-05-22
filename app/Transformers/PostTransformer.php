<?php

namespace App\Transformers;

class PostTransformer extends Transformer
{
    public function transform($post)
    {
        return [
            'id'            => $post->id,
            'user'          => $post->user,
            'title'         => $post->title,
            'location'      => $post->location,
            'latitude'      => $post->latitude,
            'longitude'     => $post->longitude,
            'numberOfRooms' => $post->numberOfRooms,
            'price'         => $post->price,
            'description'   => $post->description,
            'postDate'      => $post->created_at,
            'images'        => $post->images,
        ];
    }
}