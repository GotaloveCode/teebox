<?php

namespace App\Transformers;


use App\Club;
use League\Fractal\TransformerAbstract;

class ClubTransformer extends TransformerAbstract
{
    public function transform(Club $club)
    {
        return [
            'id' => (int) $club->id,
            'name' => ucfirst($club->name),
            'email' => $club->email,
            'website' => $club->website,
            'phone' => $club->phone,
            'postal_address' => $club->postal_address,
            'physical_address' => $club->physical_address,
            'latlong' => $club->latlong
        ];
    }
}