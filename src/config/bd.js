process.env.PORT = process.env.PORT || 3002;

process.env.NODE_ENV = process.env.NODE_ENV || 'dev';

var urlDB;

if (process.env.NODE_ENV === 'dev') {
    urlDB = 'mongodb://localhost:27017/NoSql-2020';
}  else {
    urlDB = 'mongodb+srv://julioA:julio@cluster0-99l1o.mongodb.net/NOSQL';
}  
process.env.URLDB = urlDB;
//db mongo url
//mongodb+srv://julioA:julio@cluster0-99l1o.mongodb.net/NOSQL
//mongodb+srv://juli:juli@cluster0-ugleq.mongodb.net/milibrotec
//mongodb+srv://julioA:julio@cluster0-99l1o.mongodb.net/NOSQL