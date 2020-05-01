const express = require('express');
const app = express();

let Pila =require('../models/Pila');
//rutas
app.get('/', (req, res, next) => {

    Pila.find({}, (err, pila) =>{
        if (err) {
            returnres.status(500).json({
                ok: false,
                mensaje: 'Error Cargando Pila',
                errors: err
            });
        } 
        res.status(200).json({
            ok: true,
            pila
        });

    });
    
});
app.post('/:id',  (req, res) => {
    var body = req.body;
    var id = req.params.id;

    Pila.findById(id, (err, pila) =>{
        if (err) {
            return res.status(500).json({
                ok: false,
                mensaje: 'Error al buscar usuario',
                errors: err
            });
        }

      
        pila.carga=body.carga;

        pila.save((err, pila)=>{
            if (err) {
                return res.status(400).json({
                    ok: false,
                    mensaje: 'Error al actualizar usuario',
                    errors: err
                });
            }
            res.status(200).json({
                ok: true,
                pila
            });

        })

    });

    
   /*  let pila= new Pila({
       carga: body.carga 
    });
    pila.save((err,pila) =>{
        if (err) {
            return res.status(500).json({
                ok: false,
                mensaje: 'Error',
                errors: err
            });
        }
        res.status(201).json({
            ok: true,
            pila
        });
    })
 */

});

module.exports = app;