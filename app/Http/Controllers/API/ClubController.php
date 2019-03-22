<?php

namespace App\Http\Controllers\API;

use App\Club;
use App\Transformers\ClubTransformer;
use Illuminate\Http\Request;
use App\Http\Requests\ClubRequest;


class ClubController extends BaseController
{
    protected $user;
    public function __construct()
    {
        $this->middleware(['auth', 'verified']);
        $this->middleware(function ($request, $next) {
            $this->user= auth()->user();
            return $next($request);
        });
    }
  
    public function index()
    {
        $clubs = Club::with('rates')->get();
        return $clubs;
        // return $this->collection($clubs, new ClubTransformer());
    }

    public function options()
    {
        $clubIds = $this->user->clubs()->pluck('club_id');
        return Club::whereNotIn('id',$clubIds)->select('id','name')->orderby('name','asc')->get();
    }


    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => "required|unique:clubs,name",
            'email' => "email",
            'phone' => "required|phone:KE",
        ]);
        $club = Club::Create($request->only([
            'name','email','phone','website',
            'postal_address', 'physical_address','latlong' 
        ]));
        $club->rates()->create([
            'is_member' => $request->input('is_member')[0],
            'amount' => $request->input('amount')[0]
        ]);
        $club->rates()->create([
            'is_member' => $request->input('is_member')[1],
            'amount' => $request->input('amount')[1]
        ]);
        if ($club->rates()->count() == 2) {
            return $this->response->created();
        }
        return $this->response->errorBadRequest();
    }


    public function show($id)
    {
        $club= Club::find($id);
        if ($club) {
            return $this->item($club, new ClubTransformer);
        }
        return $this->response->errorNotFound();
    }



    public function update(ClubRequest $request, $id)
    {
        $club = Club::find($id);
        if ($club) {
            $club->name = $request->input('name');
            $club->save();
            return response()->json(['message'=>'Club updated successfully']);
        }
        return $this->response->errorNotFound();
    }


    public function destroy($id)
    {
        $club = Club::find($id);
        if ($club) {
            $club->delete();
            return $this->response->noContent();
        }
        return $this->response->errorBadRequest();
    }


   
}
