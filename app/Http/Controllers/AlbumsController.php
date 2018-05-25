<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Controller as BaseController;

class AlbumsController extends BaseController
{
    public function index() {
        $array = [];

        array_push($array, [
            'title' => "Prequelle",
            "artist" => "Ghost B.C.",
            "thumbnail_image" => "https://www.pinterest.com/pin/602215781396009034/",
            "image" => "https://images-na.ssl-images-amazon.com/images/I/61JCD3p1HPL._SS500.jpg",
            "url" => "https://www.amazon.com/gp/product/B07BZ8VPK6/ref=dm_ws_sp_ps_dp"
        ]);

        array_push($array, [
            'title' => "Ceremony And Devotion",
            "artist" => "Ghost B.C.",
            "thumbnail_image" => "https://www.pinterest.com/pin/602215781396009034/",
            "image" => "https://images-na.ssl-images-amazon.com/images/I/61528dyVVHL._SS500.jpg",
            "url" => "https://www.amazon.com/Ceremony-Devotion-Ghost-B-C/dp/B0784Y6RTK/ref=ntt_mus_ep_dpi_3"
        ]);


        #title, thumbnail_image, artist, image, url

        return response()->json($array);
    }
}
