require('./src/config/bd');

const express = require('express');
const mongoose = require('mongoose');
const bodyParser = require('body-parser');
const app = express();
require('cors');


//Acceso CORS
app.use(function (req, res, next) {
    res.header("Access-Control-Allow-Origin", "*"); // update to match the domain you will make the request from
    res.header("Access-Control-Allow-Headers", "Origin, X-Requested-With, Content-Type, Accept");
    res.header("Access-Control-Allow-Methods", "POST, GET, PUT, DELETE, OPTIONS")
    next();
});

app.use(bodyParser.urlencoded({ extended: false }))
app.use(bodyParser.json())

//Rutas
const pila = require('./src/routers/app');
app.use('/pila', pila);
//Conexion
mongoose.connect(process.env.URLDB, {
    useCreateIndex: true,
    useNewUrlParser: true,
    useUnifiedTopology: true
}, (err, res) => {
    if (err) throw err;
    console.log('Base de datos ONLINE');
});



app.listen(process.env.PORT, () => { console.log('Escuchando el puerto: ', process.env.PORT); });

module.exports = app;