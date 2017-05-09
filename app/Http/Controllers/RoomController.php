<?php

namespace App\Http\Controllers;

use App\Image;
use App\Services\PostService;
use App\Services\UserService;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RoomController extends Controller
{
    protected $postService;
    protected $response = [];
    protected $userService;

    function __construct(PostService $postService, UserService $userService)
    {
        $this->postService = $postService;
        $this->userService = $userService;
        $this->middleware('auth');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('Post.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $userId         = Auth::id();
        $this->response = $this->postService->savePost($request, $userId);
        if ($this->response['code'] == "1000") {
            \Session::flash('flash_message', $this->response['message']);
        } else {
            \Session::flash('flash_message', 'Sorry!! Problem adding post! Please try again');
        }

        return redirect(route('room.profile',['userId'=>$userId]));
    }

    public function viewProfile($userId)
    {
       $this->response = $this->userService->profile($userId);
       if($this->response['code'] == '0070') {
           return view('viewProfile')->with('user', $this->response['user'])->with('posts', $this->response['posts']);
       }
       else{
           return response($this->response['message']);
       }
    }

    public function showAllPosts($postType)
    {
        $posts = $this->postService->fetchPost($postType);

        return view('Post.displayPosts')->with('posts', $posts);
    }

    public function showPersonalPosts()
    {
        $post = $this->postService->fetchPersonalPost();

        return view('Post.personalPostDisplay')->with('post', $post);
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     * @internal param int $postType
     */
    public function show($id)
    {
        $post = $this->postService->getPost($id);

        return view('Post.viewPostDetails')->with('post', $post);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        return view('');
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  int                      $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    public function updateProfileInfo(Request $request, $userId)
    {
        $this->response = $this->userService->update($request, $userId);
        if($this->response['code'] == '0026'){
            return redirect(route('room.profile',['userId'=> $userId]))->with([
                'flash_mesage'=>$this->response['message']
            ]);
        }
        else{
            return response('ERROR UPDATING PROFILE');
        }

    }

    public function updateProfileImage(Request $request)
    {
        $response = $this->userService->updateProfileImage($request);
        if ($response['code'] == '0026') {
            return redirect(route('room.profile'));
        } else {
            return response('Error updating profile image');
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param $postId
     * @return \Illuminate\Http\Response
     * @internal param int $id
     */
    public function destroyPost($postId)
    {
        $delete = $this->postService->destroy($postId);
        if ($delete) {
            return redirect(route('room.profile',['userId'=>Auth::id()]));
        } else {
            return response('Sorry!!! Could not delete the post');
        }
    }

    public function forgotPassword(Request $request)
    {
        
    }
}
