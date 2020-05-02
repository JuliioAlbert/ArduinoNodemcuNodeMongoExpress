<?php

class Dth11Controller extends AppController {


public function index(){}

public function registrar() {
    $datos = new Dth11();
    $datos->temp = 11;
    $datos->humedad = 56;
    $datos->save();

    View::json($datos);
}

public function actualizar($id) {
    $datos = (new Dth11())->findOne($id);
    $datos->x = "XyZ..*";
    $datos->update();
    View::json($datos);
}

// Recuperacion de datoss
public function todos() {
    $datos = (new Dth11())->find();
    View::json($datos);
}

public function filtro() {
    $datos = (new Dth11())->find(array('temp' => 11));
    View::json($datos);
}

// Recuperacion de datoss
public function leer($id) {
    $datos = (new Dth11())->findOne($id);
    View::json($datos);
}

public function eliminar($id) {
    $datos = (new Dth11())->findOne($id);
    $datos->delete();
    View::json($datos);
}

}

?>