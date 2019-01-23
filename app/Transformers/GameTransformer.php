<?php
/**
 * Created by PhpStorm.
 * User: marti
 * Date: 27-Nov-18
 * Time: 08:54 PM
 */

namespace App\Transformers;


use App\Game;
use League\Fractal\TransformerAbstract;

class GameTransformer extends TransformerAbstract
{
    public function transform(Game $game)
    {
        return [
            'id' => (int) $game->id,
            'start' => $game->start,
            'end' => $game->end,
            'type' => $game->type,
            'club' => $game->club()->first()->name
        ];
    }
}
