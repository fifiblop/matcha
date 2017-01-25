<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Classes\Session;
use App\Models\Interest;
use App\Models\Search;

class HomeController extends Controller {

    public function __construct()
    {
        //
    }

    // Render la vue HOME
    public function showHome(Request $request) {
        $session = Session::getInstance();
        $user_id = $session->getValue('id');
        $user = User::getUser($user_id);
        $interests = Interest::getInterests();
        $result = Search::suggestions($user);
        return view('pages.home.home',
        [
            'interests' => $interests, 
            'user' => $user,
            'request' => $request,
            'result' => $result
        ]);
    }
}