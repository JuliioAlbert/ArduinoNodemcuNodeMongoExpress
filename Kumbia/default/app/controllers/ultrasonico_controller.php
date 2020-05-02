<?php 

class UltrasonicoController extends AppController{


    public function dashboard(){
        
    }


    //5e6981a88e43d341e18a8cba
    public function get_status($id){
        $item=(new Ultrasonico())->findOne($id);
        View::json($item);

    }

    public function set_status($id){

        $item=(new Ultrasonico())->findOne($id);
        $distancia= Input::json();
        $item->progress=$distancia["progress"];
        $item->update();
        View::json($item);
    }
}

