<?php

namespace App\Http\Controllers;

use App\Post;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;


class PostController extends Controller
{
    public function __construct(Post $post, User $user)
    {
        $this->user = $user;
        $this->post = $post;
        $this->fileentryController = new FileentryController;
    }

    public function savePost(Request $request)
    {
        $this->response = [];
        $data = $this->fetchDataFromRequest($request);
        $post = $this->post->create($data);
        if ($request->hasFile('images')) {
            $files = $request->file('images');
            $entry = [];
            $i = 0;
            foreach ($files as $file) {
                $entry[$i] = $this->fileentryController->save($file);
                DB::table('postImages')->insert(['imageId' => $entry[$i]->id, 'postId' => $post->id]);
                $i++;
            }
        }
            $this->response['post'] = $post;
            $this->response['postImages'] = array($entry);
            //$this->response['postImages'] = getImages($entry); //function to fetch the images from database
            if ($post) {
                $this->response['code'] = "1000";
                $this->response['message'] = "Post added successfully";
                return Response::json($this->response);
            } else {
                $this->response['code'] = "1001";
                $this->response['message'] = "Problem adding a Post";
                return Response::json($this->response);
            }
        }

        public function fetchDataFromRequest(Request $request)
        {
            $apiToken = $request->apiToken;
            $userId = DB::table('api_tokens')->where('apiToken', $apiToken)->pluck('userId');

            return ([
                'userId' => $userId[0],
                'location' => $request->location,
                'longitude' => $request->longitude,
                'latitude' => $request->latitude,
                'numberOfRooms' => $request->numberOfRooms,
                'description' => $request->description,
                'price' => $request->price,
                'postType' => $request->postType
            ]);
        }

        public function fetchAllPost()
        {

            $this->post = Post::All();
            return Response::json($this->post);
        }

        public function fetchAllOffer()
        {
            $this->post = Post::where('postType', 1)->get();
            return Response::json($this->post);
        }

        public function fetchAllAsk()
        {
            $this->post = Post::where('postType', 0)->get();
            return Response::json($post);
        }
    }
