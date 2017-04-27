<?php

namespace App\Services;

use App\ApiToken;
use App\Image;
use App\Post;
use App\User;
use Illuminate\Http\Request;

class PostService
{
    protected $response = [];
    protected $user;
    protected $post;
    protected $fileManager;
    protected $apiToken;
    protected $image;

    /**
     * PostService constructor.
     * @param Post        $post
     * @param User        $user
     * @param FileManager $fileManager
     * @param ApiToken    $apiToken
     * @param Image       $image
     */
    public function __construct(Post $post, User $user, FileManager $fileManager, ApiToken $apiToken, Image $image)
    {
        $this->fileManager = $fileManager;
        $this->user        = $user;
        $this->post        = $post;
        $this->apiToken    = $apiToken;
        $this->image       = $image;
    }

    /**
     * service function that saves a new post and associate to the user
     * @param Request $request
     * @return array
     */
    public function savePost(Request $request)
    {
        $data = $this->fetchDataFromRequest($request);
        $post = $this->post->create($data);
        if ($post) {
            if ($request->hasFile('images')) {
                $files = $request->file('images');
                $post  = $this->fileManager->saveFile($post, $files, "post");
            }
            $this->response['code']    = "1000";
            $this->response['message'] = "Post added successfully";
            $this->response['post']    = $post;

            return ($this->response);
        } else {
            $this->response['code']    = "1001";
            $this->response['message'] = "Problem adding a Post";

            return ($this->response);
        }
    }

    /**
     * returns all the posts
     * @return mixed
     */
    public function fetchAllPost()
    {
        $posts                  = Post::All();
        $this->response['post'] = $this->getPostDetails($posts);

        return ($this->response);
    }

    /**
     * service function that returns the personal posts
     * @param $apiToken
     * @return array
     */
    public function fetchPersonalPost($apiToken)
    {
        $userId = $this->getLoggedInUserId($apiToken);
        $posts  = $this->post->where('userId', $userId)->get();
        if ($posts) {
            $this->response['code']    = "0000";
            $this->response['message'] = "Posts fetched successfully";
            $this->response['post']    = $this->getPostDetails($posts);
        } else {
            $this->response['code']    = '0001';
            $this->response['message'] = 'No posts to display';
        }
        return ($this->response);
    }

    /**
     * service function that returns the particular type of psots
     * @param $postType
     * @return array
     */
    public function fetchPost($postType)
    {
        $posts                     = $this->post->where('postType', $postType)->get();
        $this->response['code']    = "0000";
        $this->response['message'] = "Posts fetched successfully";
        $this->response['post']    = $this->getPostDetails($posts);

        return ($this->response);
    }

    /**
     * returns the posts of a particular area
     * @param Request $request
     * @return array
     */
    public function fetchPostOfParticularArea(Request $request)
    {
        $latitude                  = $request->latitude;
        $longitude                 = $request->longitude;
        $radius                    = $request->radius;
        $posts                     = $this->post->where('postType', '=', $request->postType)
            ->whereBetween('latitude', [$latitude - 0.018 * $radius, $latitude + 0.018 * $radius])
            ->whereBetween('longitude', [$longitude - 0.018 * $radius, $longitude + 0.018 * $radius])->get();
        $this->response['post']    = $this->getPostDetails($posts);
        $this->response['code']    = "0000";
        $this->response['message'] = "Posts fetched successfully";

        return ($this->response);
    }

    /**
     * get the details of particular type of posts
     * @param $posts
     * @return array
     */
    public function getPostDetails($posts)
    {
        $completePost = [];
        foreach ($posts as $post) {
            $filenameList = $this->image->where('postId', $post->id)->pluck('filename');
            $images       = [];
            foreach ($filenameList as $filename) {
                array_push($images, route('file.get', $filename));
            }
            $post['images'] = $images;
            array_push($completePost, $post);
        }

        return ($completePost);
    }

    /**
     * returns the data from the request
     * @param Request $request
     * @return array
     */
    public function fetchDataFromRequest(Request $request)
    {
        $userId = $this->getLoggedInUserId($request->header('Authorization'));

        return ([
            'userId'        => $userId[0],
            'location'      => $request->location,
            'latitude'      => $request->latitude,
            'longitude'     => $request->longitude,
            'numberOfRooms' => $request->numberOfRooms,
            'type'          => $request->type,
            'description'   => $request->description,
            'price'         => $request->price,
            'postType'      => $request->postType,
        ]);
    }

    /**
     * returns the logged in user
     * @param $header
     * @return mixed
     */
    public function getLoggedInUserId($header)
    {
        $string   = explode(" ", $header);
        $apiToken = $string[1];
        $userId   = $this->apiToken->where('apiToken', $apiToken)->pluck('userId');

        return $userId;
    }
}