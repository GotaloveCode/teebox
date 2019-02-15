<?php
/**
 * Created by PhpStorm.
 * User: martiniriga
 * Date: 12/11/2018
 * Time: 18:46
 */

namespace App\Transformers;


use App\User;
use League\Fractal\TransformerAbstract;
use Tymon\JWTAuth\Facades\JWTAuth;

class UserWithTokenTransformer extends TransformerAbstract
{
    public function transform(User $user)
    {
        $photo = $user->photo_url;

        if($photo){
            $photo= asset("storage/".$photo);
        }

        $token = JWTAuth::fromUser($user);

        $verified_email = $user->email_verified_at;
        $verified_phone = $user->phone_verified_at;
        if($verified_email){
            $verified_email = true;
        }else{
            $verified_email = false;
        }

        if($verified_phone){
            $verified_phone = true;
        }else{
            $verified_phone = false;
        }

        return [
            'id'  => (int) $user->id,
            'firstname' => ucfirst($user->first_name),
            'other_names' =>ucwords($user->other_names),
            'email' => $user->email,
            'photo_url' => $photo,
            'phone' => $user->phone,
            'verified_email' => $verified_email,
            'verified_phone' => $verified_phone,
            'memberships' => $user->clubs()->get(),
            'token' => $token
        ];
    }
}