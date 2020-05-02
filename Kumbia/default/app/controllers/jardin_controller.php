<?php

class JardinController extends AppController{

    public function dashboard(){
        
    }

    public function getxy($id){
        $xy = (new Jardin())->findOne($id);
        View::json($xy);
    }

    public function actualizar($id) {
        $datos =(new Jardin())->findOne($id); 
        $da= Input::json();
        $datos->x=$da["x"];
        $datos->y=$da["y"];
        $datos->update(); 
        View::json($datos);
    }


}


?>