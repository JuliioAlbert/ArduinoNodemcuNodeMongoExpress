<?php

class ArduinoController extends AppController{

    public function index(){}

    public function datos(){

   }
     
   public function registrar(){
    $datos= Input::json();
    $retVal = ($datos["temp"]>20) ? "calido" : "frio" ;
    View::json($retVal);
}
    public function puerta(){
        $datos= Input::json();
        $Resp = ($datos["estado"]===1) ? "Cerrada" : "Abierto" ;
        View::json($Resp);
        
    }
    public function jardin(){
        
    }
}
