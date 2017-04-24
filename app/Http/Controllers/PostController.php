<?php

namespace App\Http\Controllers;

use App\ApiToken;
use App\Image;
use App\Post;
use App\Services\FileManager;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;


class PostController extends Controller
{
    protected $user;
    protected $post;
    protected $response = [];
    protected $fileManager;
    protected $apiToken;
    protected $image;

    public function __construct(Post $post, User $user, FileManager $fileManager, ApiToken $apiToken, Image $image)
    {
        $this->fileManager = $fileManager;
        $this->user        = $user;
        $this->post        = $post;
        $this->apiToken    = $apiToken;
        $this->image       = $image;
    }

    public function savePost(Request $request)
    {
        $data = $this->fetchDataFromRequest($request);
        $post = $this->post->create($data);
        if ($request->hasFile('images')) {

            $files = $request->file('images');

//            $entry = [];
//            $i     = 0;
//            foreach ($files as $file) {
            $post = $this->fileManager->saveFile($post, $files, "post");
//                DB::table('postImages')->insert(['imageId' => $entry[$i]->id, 'postId' => $post->id]);
//                $i++;
//            }
        }
        $this->response['post']       = $post;
        //$this->response['postImages'] = getImages($entry); //function to fetch the images from database
        if ($post) {
            $this->response['code']    = "1000";
            $this->response['message'] = "Post added successfully";

            return Response::json($this->response);
        } else {
            $this->response['code']    = "1001";
            $this->response['message'] = "Problem adding a Post";

            return Response::json($this->response);
        }
    }

    public function fetchAllPost()
    {
        $posts = Post::All();
        $this->response['post'] = [];
//        dd($posts->image);
        foreach ($posts as $post){
            $this->response['post'] = $post;
            $this->response['post']['images'] = [];
            foreach($post->image as $image){
                $this->response['post']['images'] = route('file.get', $image->filename);
            }
        }

        return Response::json($this->response);
    }

    public function fetchPersonalPost($apiToken)
    {
        $userId = $this->getLoggedInUserId($apiToken);
        $post   = $this->post->where('userId', $userId)->get();
        if ($post) {
            $this->response['code']    = "0000";
            $this->response['message'] = "Posts fetched successfully";
            $this->response['posts']   = $post;
        } else {
            $this->response['code']    = '0001';
            $this->response['message'] = 'No posts to display';
        }

        return Response::json($this->response);
    }

    public function fetchAllOffer()
    {
        $this->post = $this->post->where('postType', 1)->get();

        return Response::json($this->post);
    }

    public function fetchAllAsk()
    {
        $post = $this->post->where('postType', 0)->get();

        return Response::json($post);
    }

    public function fetchDataFromRequest(Request $request)
    {
        $userId = $this->getLoggedInUserId($request->apiToken);

        return ([
            'userId'        => $userId[0],
            'location'      => $request->location,
            'numberOfRooms' => $request->numberOfRooms,
            'type'          => $request->type,
            'description'   => $request->description,
            'price'         => $request->price,
            'postType'      => $request->postType,
        ]);
    }

    public function fetchImages($postId)
    {
        $images  = [];
        $imageId = DB::table('postImages')->where('postId', $postId)->pluck('imageId');
        foreach ($imageId as $id) {
            $image  = $this->fileEntry->where('id', $id)->get();
            $images = route('file.get', $image->filename);
        }

        return $images;
    }

    public function getLoggedInUserId($apiToken)
    {
        $userId = $this->apiToken->where('apiToken', $apiToken)->pluck('userId');

        return $userId;
    }
}
