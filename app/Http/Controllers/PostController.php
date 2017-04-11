<?php

namespace App\Http\Controllers;

use App\Post;
use App\Services\FileManager;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;


/**
 * Class PostController
 * @package App\Http\Controllers
 */
class PostController extends Controller
{
    protected $response = [];
    protected $user;
    protected $post;
    protected $fileManager;

    /**
     * PostController constructor.
     * @param Post        $post
     * @param User        $user
     * @param FileManager $fileManager
     */
    public function __construct(Post $post, User $user, FileManager $fileManager)
    {
        $this->user        = $user;
        $this->post        = $post;
        $this->fileManager = $fileManager;
    }

    /** saves the new post
     * @param Request $request
     * @return mixed
     */
    public function savePost(Request $request)
    {
        $this->response = [];
        $data           = $this->fetchDataFromRequest($request);
        $post           = $this->post->create($data);
        if ($request->hasFile('image')) {
            $files = $request->file('image');
            $entry = [];
            $i     = 0;
            foreach ($files as $file) {
                $entry[$i] = $this->fileManager->saveFile($file);
                DB::table('postImages')->insert(['imageId' => $entry[$i]->id, 'postId' => $post->id]);
                $i++;
            }
        }
        if ($post) {

            $this->response['post']       = $post;
            $this->response['postImages'] = $entry;
            $this->response['code']       = "1000";
            $this->response['message']    = "Post added successfully";
        } else {
            $this->response['code']    = "1001";
            $this->response['message'] = "Problem adding a Post";
        }

        return Response::json($this->response);
    }

    /**
     * fetches all the data from request
     * @param Request $request
     * @return array
     */
    public function fetchDataFromRequest(Request $request)
    {
        $apiToken = $request->apiToken;
        $userId   = DB::table('api_tokens')->where('apiToken', $apiToken)->pluck('userId');

        return ([
            'userId'        => $userId[0],
            'location'      => $request->location,
            'longitude'     => $request->longitude,
            'latitude'      => $request->latitude,
            'numberOfRooms' => $request->numberOfRooms,
            'description'   => $request->description,
            'price'         => $request->price,
            'postType'      => $request->postType,
        ]);
    }

    /**
     * fetches and returns all the saved posts
     * @return mixed
     */
    public function fetchAllPost()
    {
        $this->post = Post::All();

        return Response::json($this->post);
    }

    /**
     * fetches and returns  all the offer posts only
     * @return mixed
     */
    public function fetchAllOffer()
    {
        $this->post = Post::where('postType', 1)->get();

        return Response::json($this->post);
    }

    /**
     * fetches and  returns all the ask posts
     * @return mixed
     */
    public function fetchAllAsk()
    {
        $this->post = Post::where('postType', 0)->get();

        return Response::json($this->post);
    }

}
