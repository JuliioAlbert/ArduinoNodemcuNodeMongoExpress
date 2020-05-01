process.env.PORT = process.env.PORT || 3002;

process.env.NODE_ENV = process.env.NODE_ENV || 'dev';

var urlDB;

if (process.env.NODE_ENV === 'dev') {
    urlDB = 'mongodb://localhost:27017/NoSql-2020';
} /* else {
    urlDB = 'mongodb+srv://juli:juli@cluster0-ugleq.mongodb.net/milibrotec';
}  */
process.env.URLDB = urlDB;
//db mongo url
//mongodb+srv://juli:juli@cluster0-ugleq.mongodb.net/milibrotec