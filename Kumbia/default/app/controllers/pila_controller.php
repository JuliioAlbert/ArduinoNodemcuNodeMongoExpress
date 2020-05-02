<?php 

class PilaController extends AppController{


    public function index(){
        
    }


    //5eab1f98ca77f1e6b3111b5a
    public function get_status($id){
        $item=(new Pila())->findOne($id);
        View::json($item);

    }

    public function set_status($id){

        $item=(new Pila())->findOne($id);
        $carga= Input::json();
        $item->carga=$carga["carga"];
        $item->update();
        View::json($item);
    }
}

