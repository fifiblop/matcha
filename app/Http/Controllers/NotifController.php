<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Classes\Session;
use App\Models\Likes;

class NotifController extends Controller {

    public function __construct()
    {
        //
    }

    // Render la vue Notification
    public function showNotif(Request $request) {
        return view('pages.home.notification',
        [
            'request' => $request
        ]);
    }

    public function isOnline(Request $request, $login) {
        $login = htmlentities($login);
        if (User::loginExists($login)) {
            $user = User::getUser(User::getUserId($login));
            if ($user->isOnline())
                return 1;
            else
                return $user->getLastVisit();
        }
        return 0;
    }

    public function likeUser(Request $request, $login) {
        $session = Session::getInstance();
        $user = User::getUser($session->getValue('id'));
        $login = htmlentities($login);
        $user_id = $user->getId();
        $other_id = User::getUserId($login);
        $response = [];
        if (User::loginExists($login)) {
            if ($user_id == $other_id) {
                return $response['error'] = "vous ne pouvez pas vous liker";
            } else if ($user->isBlocked($other_id)) {
                return $response['error'] = "L'utilisateur que vous voulez liker à été bloqué";
            } else if (empty($user->getAvatar())) {
                return $response['error'] = "il faut une photo de profil pour liker";
            } else {
                $other_id = User::getUserId($login);
                // si pas de match
                if (!Likes::isMatch($other_id)) {
                    // si pas de like et pas de like-back
                    if (!Likes::isLike($other_id) && 
                        !Likes::isUserLike($other_id, $user_id)) {
                        Likes::like($other_id);
                        return $response['success'] = "like";
                        // si like et pas de like-back
                    } else if (Likes::isLike($other_id) && 
                                !Likes::isUserLike($other_id, $user_id)) {
                        Likes::deleteLike($other_id);
                        return $response['success'] = "unlike";
                        // si pas de like et like-back
                    } else if (!Likes::isLike($other_id) && 
                                Likes::isUserLike($other_id, $user_id)) {
                        Likes::like($other_id);
                        Likes::match($other_id);
                        return $response['success'] = "match";
                    }
                // si match
                } else {
                    Likes::deleteMatch($other_id);
                    Likes::deleteLike($other_id);
                    return $response['success'] = "unmatch";
                }
            }
        } else {
            return $response['error'] = "cette utilisateur n'existe pas";
        }
    }
}