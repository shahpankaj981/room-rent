<?php

namespace App\Http\Controllers;

use App\Services\PostService;
use App\Services\UserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Response;

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
     * Show the form for creating a new POst.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('Post.create');
    }

    /**
     * Store a newly created Post in database.
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

        return redirect(route('room.profile', ['userId' => $userId]));
    }

    /**
     * Displays the profile info of a user
     * @param $userId
     * @return \Illuminate\View\View
     */
    public function viewProfile($userId)
    {
        $this->response = $this->userService->profile($userId);
        if ($this->response['code'] == '0070') {
            return view('viewProfile')->with('user', $this->response['user'])->with('posts', $this->response['posts']);
        } else {
            return view('response')->with('response', $this->response);
        }
    }

    /**
     * displays all the posts of a particular type
     * @param $postType
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function showAllPosts($postType)
    {
        $this->response = $this->postService->fetchPost($postType);

//        $posts = $this->postService->fetchPost($postType);
        if ($this->response['code'] == '000') {
            return view('Post.displayPosts')->with('posts', $this->response['post']);
        } else {
            return view('response')->with('response', $this->response['message']);
        }
    }

    /**
     * Display the details of a post.
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
     * Updates the info of a user
     * @param Request $request
     * @param         $userId
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function updateProfileInfo(Request $request, $userId)
    {
        $this->response = $this->userService->update($request, $userId);
        if ($this->response['code'] == '0026') {
            return redirect(route('room.profile', ['userId' => $userId]))->with([
                'flash_mesage' => $this->response['message'],
            ]);
        } else {
            return response('ERROR UPDATING PROFILE');
        }
    }

    /**
     * Updates the profile image of a user
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function updateProfileImage(Request $request)
    {
        $response = $this->userService->updateProfileImage($request);
        if ($response['code'] == '0026') {
            return redirect(route('room.profile', ['userId' => Auth::id()]));
        } else {
            return view('response')->with($response['message'] = 'Error updating profile image');
        }
    }

    /**
     * Remove the specified post from database
     *
     * @param $postId
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     * @internal param int $id
     */
    public function destroyPost($postId)
    {
        $delete = $this->postService->destroy($postId);
        if ($delete) {
            return redirect(route('room.profile', ['userId' => Auth::id()]));
        } else {
            return view('response')->with($response['message'] = 'Sorry!!! Could not delete the post');
        }
    }

    /**
     * Changes the password
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function changePassword(Request $request)
    {
        $this->response = $this->userService->changePassword($request);

        return view('response')->with('response', $this->response);
    }
}
