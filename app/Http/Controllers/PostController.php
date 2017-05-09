<?php

namespace App\Http\Controllers;

use App\Services\PostService;
use Illuminate\Http\Request;
use Response;


class PostController extends Controller
{
    protected $response = [];
    protected $postService;

    /**
     * PostController constructor.
     * @param PostService $postService
     */
    public function __construct(PostService $postService)
    {
        $this->postService = $postService;
    }

    /**
     * saves the new post and associate it  to the particular user
     * @param Request $request
     * @return mixed
     */
    public function savePost(Request $request, $userId)
    {
        $this->response = $this->postService->savePost($request, $userId);
        return Response::json($this->response);

    }



    /**
     * returns only the logged-in user's posts
     * @param $apiToken
     * @return mixed
     */
    public function fetchPersonalPost($apiToken)
    {
      $this->response = $this->postService->fetchPersonalPost($apiToken);

        return Response::json($this->response);
    }

    /**
     * returns only the particular type of posts, i.e either ask-posts or offer-posts
     * @param $postType
     * @return mixed
     */
    public function fetchPost($postType)
    {
        $this->response = $this->postService->fetchPost($postType);

        return Response::json($this->response);
    }

    /**
     * returns the posts of a particular location
     * @param Request $request
     * @return mixed
     */
    public function fetchPostOfParticularArea(Request $request)
    {
        $$this->response = $this->postService->fetchPostOfParticularArea($request);

        return Response::json($this->response);
    }
}
