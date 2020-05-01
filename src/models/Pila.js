const mongoose = require('mongoose');

let Schema = mongoose.Schema;
let PilaSchema = new Schema({
    carga:{
        type:String,
    }
});

module.exports = mongoose.model('Pila', PilaSchema);