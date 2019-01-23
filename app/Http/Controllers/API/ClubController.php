<?php

namespace App\Http\Controllers\API;

use App\Club;
use App\Transformers\ClubTransformer;
use Illuminate\Http\Request;
use App\Http\Requests\ClubRequest;


class ClubController extends BaseController
{
    public function __construct()
    {
        $this->middleware(['auth', 'verified']);
    }
  
    public function index()
    {
        $clubs = Club::all();
        return $this->collection($clubs, new ClubTransformer());
    }

    public function options()
    {
        return Club::select('id','name')->orderby('name','asc')->get();
    }


    public function store(ClubRequest $request)
    {
        if (Club::Create($request->all())) {
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
