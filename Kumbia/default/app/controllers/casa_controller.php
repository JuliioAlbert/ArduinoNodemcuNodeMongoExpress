<?php 

class CasaController extends AppController{


    public function dashboard(){
        
    }


    //5e6981a88e43d341e18a8cba
    public function get_status($id){
        $item=(new CasaItems())->findOne($id);
        View::json($item);

    }
    public function get_statusp($id){
        $item=(new CasaItems())->findOne($id);
        View::json($item);

    }

    public function set_status($id){

        $item=(new CasaItems())->findOne($id);

         if($item->status==1):
            $item->status=0;
        else:
            $item->status=1;
        endif;
        $item->update(); 
        //$Resp = ($item->status===1) ? 0 : 1 ;
        $item->update(); 
        View::json($item);
    }
    public function set_statusp($id){

        $item=(new CasaItems())->findOne($id);

         if($item->statusP==1):
            $item->statusP=0;
        else:
            $item->statusP=1;
        endif;
        $item->update(); 
        //$Resp = ($item->status===1) ? 0 : 1 ;
        $item->update(); 
        View::json($item);
    }
  

}