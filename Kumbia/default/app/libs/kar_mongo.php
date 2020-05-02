<?php

/**
 * KumbiaPHP web & app Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.
 *
 * @category   Kumbia
 * @package    ActiveRecord
 *
 * @copyright  Copyright (c) 2005 - 2019 KumbiaPHP Team (http://www.kumbiaphp.com)
 * @license    https://github.com/KumbiaPHP/KumbiaPHP/blob/master/LICENSE   New BSD License
 */
/**
 * @see Db
 */
/**
 * ActiveRecordBase Clase para el Mapeo Objeto Relacional.
 *
 * Active Record es un enfoque al problema de acceder a los datos de una
 * base de datos en forma orientada a objetos. Una fila en la
 * tabla de la base de datos (o vista) se envuelve en una clase,
 * de manera que se asocian filas &uacute;nicas de la base de datos
 * con objetos del lenguaje de programaci&oacute;n usado.
 * Cuando se crea uno de estos objetos, se a&ntilde;de una fila a
 * la tabla de la base de datos. Cuando se modifican los atributos del
 * objeto, se actualiza la fila de la base de datos.
 *
 * Propiedades Soportadas:
 * $db = Conexion al Motor de Base de datos
 * $database = Base de datos a la que se conecta, especificada en databases.ini
 * $source = Tabla que contiene la tabla que esta siendo mapeada
 * $fields = Listado de Campos de la tabla que han sido mapeados
 * $count = Conteo del ultimo Resultado de un Select
 * $primary_key = Listado de columnas que conforman la llave primaria
 * $non_primary = Listado de columnas que no son llave primaria
 * $not_null = Listado de campos que son not_null
 * $attributes_names = nombres de todos los campos que han sido mapeados
 * $debug = Indica si se deben mostrar los SQL enviados al RDBM en pantalla
 * $logger = Si es diferente de false crea un log utilizando la clase Logger
 * en library/kumbia/logger/logger.php, esta crea un archivo .txt en logs/ con todas las
 * operaciones realizadas en ActiveRecord, si $logger = "nombre", crea un
 * archivo con ese nombre
 *
 * Propiedades sin Soportar:
 * $dynamic_update : La idea es que en un futuro ActiveRecord solo
 * actualize los campos que han cambiado.  (En Desarrollo)
 * $dynamic_insert : Indica si los valores del insert son solo aquellos
 * que sean no nulos. (En Desarrollo)
 * $select_before_update: Exige realizar una sentencia SELECT anterior
 * a la actualizacion UPDATE para comprobar que los datos no hayan sido
 * cambiados (En Desarrollo)
 * $subselect : Permitira crear una entidad ActiveRecord de solo lectura que
 * mapearia los resultados de un select directamente a un Objeto (En Desarrollo)
 *
 * @category   Kumbia
 */
require_once APP_PATH . 'libs/vendor/autoload.php';

class KarMongo {

    protected $attrs = [];
    protected $coleccion;
    protected $document;
    //Soportados
    /**
     * Resource de conexion a la base de datos.
     *
     * @var DbBase
     */
    protected $db;

    /**
     * Base de datos a la que se conecta.
     *
     * @var string
     */
    protected $database;

    /**
     * Schema donde esta la tabla.
     *
     * @var string
     */
    protected $schema;

    /**
     * Tabla utilizada para realizar el mapeo.
     *
     * @var string
     */
    protected $collection;
    protected $source;

    /**
     * Numero de resultados generados en la ultima consulta.
     *
     * @var int
     */
    protected $count;

    /**
     * Nombres de los atributos de la entidad.
     *
     * @var array
     */
    protected $fields = array();

    /**
     * LLaves primarias de la entidad.
     *
     * @var array
     */
    protected $primary_key = array();

    /**
     * Campos que no son llave primaria.
     *
     * @var array
     */
    protected $non_primary = array();

    /**
     * Campos que no permiten nulos.
     *
     * @var array
     */
    protected $not_null = array();

    /**
     * Campos que tienen valor por defecto.
     *
     * @var array
     */
    protected $_with_default = array();

    /**
     * Nombres de atributos, es lo mismo que fields.
     *
     * @var array
     */
    protected $alias = array();

    /**
     * Indica si la clase corresponde a un mapeo de una vista
     * en la base de datos.
     *
     * @var bool
     */
    protected $is_view = false;

    /**
     * Indica si el modelo esta en modo debug.
     *
     * @var bool
     */
    protected $debug = false;

    /**
     * Indica si se logearan los mensajes generados por la clase.
     *
     * @var mixed
     */
    protected $logger = false;

    /**
     * Indica si los datos del modelo deben ser persistidos.
     *
     * @var bool
     */
    protected $persistent = false;

    /**
     * Validaciones.
     *
     * inclusion_in: el campo pertenece a un conjunto de elementos
     * exclusion_of: el campo no pertenece a un conjunto de elementos
     * numericality_of: el campo debe ser númerico
     * format_of: el campo debe coincidir con la expresión regular
     * date_in: el campo debe ser una fecha válida
     * email_in: el campo debe ser un correo electrónico
     * uniqueness_of: el campo debe ser único
     *
     * @var array
     * */
    protected $_validates = array('inclusion_in' => array(), 'exclusion_of' => array(), 'numericality_of' => array(),
        'format_of' => array(), 'date_in' => array(), 'email_in' => array(), 'uniqueness_of' => array(),);

    /**
     * Campos que terminan en _in.
     *
     * @var array
     */
    protected $_in = array();

    /**
     * Campos que terminan en _at.
     *
     * @var array
     */
    protected $_at = array();

    /**
     * Variable para crear una condicion basada en los
     * valores del where.
     *
     * @var string
     */
    protected $_where_pk;

    /**
     * Indica si ya se han obtenido los metadatos del Modelo.
     *
     * @var bool
     */
    protected $_dumped = false;

    /**
     * Indica si hay bloqueo sobre los warnings cuando una propiedad
     * del modelo no esta definida-.
     *
     * @var bool
     */
    protected $_dump_lock = false;

    /**
     * Tipos de datos de los campos del modelo.
     *
     * @var array
     */
    protected $_data_type = array();

    /**
     * Relaciones a las cuales tiene una cardinalidad 1-1.
     *
     * @var array
     */
    protected $_has_one = array();

    /**
     * Relaciones a las cuales tiene una cardinalidad 1-n.
     *
     * @var array
     */
    protected $_has_many = array();
    protected $addMany = array();

    /**
     * Relaciones a las cuales tiene una cardinalidad 1-1.
     *
     * @var array
     */
    protected $_belongs_to = array();

    /**
     * Relaciones a las cuales tiene una cardinalidad n-n (muchos a muchos) o 1-n inversa.
     *
     * @var array
     */
    protected $_has_and_belongs_to_many = array();

    /**
     * Clases de las cuales es padre la clase actual.
     *
     * @var array
     */
    protected $parent_of = array();

    /**
     * Persistance Models Meta-data.
     */
    protected static $models = array();

    /**
     * Constructor del Modelo.
     *
     * @param array $data
     */
    public function __construct($data = null) {
        $this->_model_name();
        /*
         * Inicializa el modelo en caso de que exista initialize
         */
        if (method_exists($this, 'initialize')) {
            $this->initialize();
        }
        /*
         * Conecta a la bd
         * */
        $this->_connect();
        $coleccion = $this->coleccion;
        $this->collection = $this->db->$coleccion;
        //var_dump($data);

        $this->primary_key[0] = "id";

        if ($data) {
            if (!is_array($data)) {
                $data = Util::getParams(func_get_args());
            }
            foreach ($data as $d) {
                $this->$d = $data[$d];
            }
        }
    }

    /**
     * Obtiene el nombre de la relacion en el RDBM a partir del nombre de la clase.
     */
    protected function _model_name() {
        if (!$this->coleccion) {
            $this->coleccion = Util::smallcase(get_class($this));
        }
    }

    /**
     * Establece publicamente el $source de la tabla.
     *
     * @param string $source
     */
    public function set_source($source) {
        $this->source = $source;
        $this->db->$source;
        $this->collection = $this->db->$source;
    }

    /**
     * Devuelve el source actual.
     *
     * @return string
     */
    public function get_source() {
        return $this->source;
    }

    /**
     * Establece la base datos a utilizar.
     *
     * @param string $database
     */
    public function set_database($database) {
        $this->database = $database;
    }

    /**
     * Devuelve la base de datos.
     *
     * @return string
     */
    public function get_database() {
        return $this->database ?: Config::read('config')['application']['database'];
    }

    /**
     * Devuelve los registros del modelo al que se está asociado.
     *
     * @param string $relmodel nombre del modelo asociado
     *
     * @return array|null|false si existen datos devolverá un array,
     *                          NULL si no hay datos asociados aun, y false si no existe ninguna asociación
     */
    protected function _get_relation_data($relmodel) {
        if (isset($this->_belongs_to[$relmodel])) {
            $relation = $this->_belongs_to[$relmodel];

            return (new $relation->model())->find_first($this->{$relation->fk});
        }
        if (isset($this->_has_one[$relmodel])) {
            $relation = $this->_has_one[$relmodel];
            if ($this->{$this->primary_key[0]}) {
                return (new $relation->model())->find_first("{$relation->fk}={$this->db->add_quotes($this->{$this->primary_key[0]}) }");
            }

            return null;
        }
        if (isset($this->_has_many[$relmodel])) {
            $relation = $this->_has_many[$relmodel];
            if ($this->{$this->primary_key[0]}) {
                $filters[$relation->fk] = new MongoDB\BSON\ObjectId($this->{$this->primary_key[0]});
                return (new $relation->model())->find($filters);
            }
            return array();
        }
        if (isset($this->_has_and_belongs_to_many[$relmodel])) {
            $relation = $this->_has_and_belongs_to_many[$relmodel];
            $relation_model = self::get($relation->model);
            $source = ($this->schema ? "{$this->schema}." : null) . $this->source;
            $relation_source = ($relation_model->schema ? "{$relation_model->schema}." : null) . $relation_model->source;
            /*
             * Cargo atraves de que tabla se efectuara la relacion
             *
             */
            if (!isset($relation->through)) {
                if ($source > $relation_source) {
                    $relation->through = "{$this->source}_{$relation_source}";
                } else {
                    $relation->through = "{$relation_source}_{$this->source}";
                }
            } else {
                $through = explode('/', $relation->through);
                $relation->through = end($through);
            }
            if ($this->{$this->primary_key[0]}) {
                return $relation_model->find_all_by_sql("SELECT $relation_source.* FROM $relation_source, {$relation->through}, $source
                    WHERE {$relation->through}.{$relation->key} = {$this->db->add_quotes($this->{$this->primary_key[0]}) }
                    AND {$relation->through}.{$relation->fk} = $relation_source.{$relation_model->primary_key[0]}
                    AND {$relation->through}.{$relation->key} = $source.{$this->primary_key[0]}
                    ORDER BY $relation_source.{$relation_model->primary_key[0]}");
            }

            return array();
        }

        return false; //si no existe ninguna asociación devuelve false.
    }

    /**
     * Valida que los valores que sean leidos del objeto ActiveRecord esten definidos
     * previamente o sean atributos de la entidad.
     *
     * @param string $property
     */
    public function __get($property) {
        if (!$this->_dump_lock) {
            if (!isset($this->$property)) {
                return $this->_get_relation_data($property);
            }
        }

        return $this->$property;
    }

    /**
     * Valida que los valores que sean asignados al objeto ActiveRecord esten definidos
     * o sean atributos de la entidad.
     *
     * @param string $property
     * @param mixed  $value
     */
    public function __set($property, $value) {
        /*

          if (!$this->_dump_lock) {
          if (is_object($value) && is_subclass_of($value, 'KumbiaActiveRecord')) {
          if (array_key_exists($property, $this->_belongs_to)) {
          $relation = $this->_belongs_to[$property];
          $value->dump_model();
          $this->{$relation->fk} = $value->{$value->primary_key[0]};

          return;
          } elseif (array_key_exists($property, $this->_has_one)) {
          $relation = $this->_has_one[$property];
          KumbiaActiveRecord $value->{$relation->fk} = $this->{$this->primary_key[0]};

          return;
          }
          } elseif ($property == 'source') {
          $value = self::sql_item_sanitize($value);
          }
          }
         */
        $this->$property = $value;
    }

    /**
     * Devuelve un valor o un listado dependiendo del tipo de Relación.
     */
    public function __call($method, $args = array()) {

        $relacion = Util::smallcase(substr($method, 3, strlen($method)));
        //var_dump($this->_has_many);
        //  return true;

        if (key_exists($relacion, $this->_has_many)) {
            $this->addMany[$this->_has_many[$relacion]->model][] = $args;
            return true;
        }


        if (substr($method, 0, 8) == 'find_by_') {
            $field = substr($method, 8);
            self::sql_item_sanitize($field);
            if (isset($args[0])) {
                $arg = array("conditions: $field = {$this->db->add_quotes($args[0])}");
                unset($args[0]);
            } else {
                $arg = array();
            }

            return call_user_func_array(array($this, 'find_first'), array_merge($arg, $args));
        }
        if (substr($method, 0, 9) == 'count_by_') {
            $field = substr($method, 9);
            self::sql_item_sanitize($field);
            if (isset($args[0])) {
                $arg = array("conditions: $field = {$this->db->add_quotes($args[0])}");
                unset($args[0]);
            } else {
                $arg = array();
            }

            return call_user_func_array(array($this, 'count'), array_merge($arg, $args));
        }
        if (substr($method, 0, 12) == 'find_all_by_') {
            $field = substr($method, 12);
            self::sql_item_sanitize($field);
            if (isset($args[0])) {
                $arg = array("conditions: $field = {$this->db->add_quotes($args[0])}");
                unset($args[0]);
            } else {
                $arg = array();
            }

            return call_user_func_array(array($this, 'find'), array_merge($arg, $args));
        }
        $model = preg_replace('/^get/', '', $method);
        $mmodel = Util::smallcase($model);
        if (($data = $this->_get_relation_data($mmodel)) !== false) {
            return $data;
        }
        if (method_exists($this, $method)) {
            call_user_func_array(array($this, $method), $args);
        } else {
            throw new KumbiaException("No existe el método '$method' en ActiveRecord::" . get_class($this));
        }

        return $this->$method($args);
    }

    /**
     * Se conecta a la base de datos y descarga los meta-datos si es necesario.
     */
    protected function _connect() {


        $server = Config::read('databases')['mongodb']['host'];

        $connection = new MongoDB\Client($server);
        $base = Config::read('databases')['mongodb']['name'];

        $this->db = $connection->$base;

        $this->db->debug = $this->debug;
        $this->db->logger = $this->logger;
    }

    /**
     * Retorna un array de los campos (fields) de una tabla Humanizados.
     *
     * @param $key
     *
     * @return array
     */
    public function get_alias($key = null) {
        if ($key === null) {
            return $this->alias;
        }
        if (isset($this->alias[$key])) {
            return $this->alias[$key];
        }
        throw new KumbiaException("No se pudo obtener el Alias, porque el key: \"$key\" no existe.");
    }

    /**
     * Asigna un nuevo valor al alias dado un key.
     *
     * @param string $key
     * @param string $value
     */
    public function set_alias($key, $value) {
        if (isset($this->alias[$key])) {
            $this->alias[$key] = $value;
        }
        throw new KumbiaException("No se pudo asignar el nuevo valor al Alias, porque el key: \"$key\" no existe.");
    }

    /**
     * Commit a Transaction.
     *
     * @return success
     */
    public function commit() {
        return $this->db->commit();
    }

    /**
     * Rollback a Transaction.
     *
     * @return success
     */
    public function rollback() {
        return $this->db->rollback();
    }

    /**
     * Start a transaction in RDBM.
     *
     * @return success
     */
    public function begin() {
        $this->_connect(); //(true);
        return $this->db->begin();
    }

    /**
     * Find all records in this table using a SQL Statement.
     *
     * @param string $sqlQuery
     *
     * @return ActiveRecord Cursor
     */
    public function find_all_by_sql($sqlQuery) {
        $results = array();
        foreach ($this->db->fetch_all($sqlQuery) as $result) {
            $results[] = $this->dump_result($result);
        }

        return $results;
    }

    /**
     * Find a record in this table using a SQL Statement.
     *
     * @param string $sqlQuery
     *
     * @return ActiveRecord Cursor
     */
    public function find_by_sql($sqlQuery) {
        $row = $this->db->fetch_one($sqlQuery);
        if ($row !== false) {
            $this->dump_result_self($row);

            return $this->dump_result($row);
        } else {
            return false;
        }
    }

    /**
     * Execute a SQL Statement directly.
     *
     * @param string $sqlQuery
     *
     * @return int affected
     */
    public function sql($sqlQuery) {
        return $this->db->query($sqlQuery);
    }

    /**
     * Return Fist Record.
     *
     * Recibe los mismos parametros que find
     *
     * @param mixed $what
     *
     * @return ActiveRecord Cursor
     */
    public function find_first($what = '') {
        $what = Util::getParams(func_get_args());
        $select = 'SELECT ';
        if (isset($what['columns'])) {
            $select .= self::sql_sanitize($what['columns']);
        } elseif (isset($what['distinct'])) {
            $select .= 'DISTINCT ';
            $select .= $what['distinct'] ? self::sql_sanitize($what['distinct']) : join(',', $this->fields);
        } else {
            $select .= join(',', $this->fields);
        }
        if ($this->schema) {
            $select .= " FROM {$this->schema}.{$this->source}";
        } else {
            $select .= " FROM {$this->source}";
        }
        $what['limit'] = 1;
        $select .= $this->convert_params_to_sql($what);
        $resp = false;
        $result = $this->db->fetch_one($select);
        if ($result) {
            $this->dump_result_self($result);
            $resp = $this->dump_result($result);
        }

        return $resp;
    }

    /**
     * Find data on Relational Map table.
     *
     * @param string $what
     *
     * @return ActiveRecord Cursor
     *
     * columns: columnas a utilizar
     * conditions : condiciones de busqueda en WHERE
     * join: inclusion inner join o outer join
     * group : campo para grupo en GROUP BY
     * having : condicion para el grupo
     * order : campo para criterio de ordenamiento ORDER BY
     * distinct: campos para hacer select distinct
     */
    public function find($what = null) {

        if ($what == null) {
            $results = iterator_to_array($this->collection->find());
        } else {
            $results = iterator_to_array($this->collection->find($what));
        }

        return (count($results) > 1) ? $results : $results[0];
    }

    public function findOne($id) {
        $res = $this->collection->findOne(['_id' => new MongoDB\BSON\ObjectId($id)]);
        $this->document = $res;
        // return $this->document;
        //   var_dump($res);
        foreach ($res as $k => $v):
            //    $this->attrs[$k] = $v;
            $this->$k = $v;
        endforeach;
        //$this->id = $id;

        return $this; //->fields;
    }

    /*
     * Devuelve una clausula LIMIT adecuada al RDBMS empleado
     *
     * limit: maxima cantidad de elementos a mostrar
     * offset: desde que elemento se comienza a mostrar
     *
     * @param string $sql consulta select
     * @return String clausula LIMIT adecuada al RDBMS empleado
     */

    public function limit($sql) {
        $args = func_get_args();

        return call_user_func_array(array($this->db, 'limit'), $args);
    }

    /**
     * Iguala los valores de un resultado de la base de datos
     * con sus correspondientes atributos de la clase.
     *
     * @param array $result
     *
     * @return ActiveRecord
     */
    public function dump_result_self($result) {
        $this->_dump_lock = true;
        if (is_array($result)) {
            foreach ($result as $k => $r) {
                if (!is_numeric($k)) {
                    if (!is_object($r)) {
                        $this->$k = is_array($r) ? $r : stripslashes($r);
                    } else {
                        $this->$k = $r->load();
                    }
                }
            }
        }
        $this->_dump_lock = false;
    }

    /**
     * Crea un nuevo registro utilizando los datos del $_REQUEST.
     *
     * @param string $form, equivalente a $_REQUEST[$form]
     *
     * @return bool success
     *
     * @deprecated No es seguro
     */
    public function create_from_request($form = null) {
        if (!$form) {
            $form = $this->source;
        }

        return $this->create($_REQUEST[$form]);
    }

    /**
     * Saves a new Row using values from $_REQUEST.
     *
     * @param string $form form name for request, equivalent to $_REQUEST[$form]
     *
     * @return bool success
     *
     * @deprecated No es seguro
     */
    public function save_from_request($form = null) {
        if (!$form) {
            $form = $this->source;
        }

        return $this->save($_REQUEST[$form]);
    }

    /**
     * Updates a Row using values from $_REQUEST.
     *
     * @param string $form form name for request, equivalent to $_REQUEST[$form]
     *
     * @return bool|null success
     */
    public function update_from_request($form = null) {
        if (!$form) {
            $form = $this->source;
        }

        return $this->update($_REQUEST[$form]);
    }

    /**
     * Creates a new Row in map table.
     *
     * @param mixed $values
     *
     * @return bool success
     */
    public function create() {
        if (func_num_args() > 0) {
            $params = Util::getParams(func_get_args());
            $values = (isset($params[0]) && is_array($params[0])) ? $params[0] : $params;
            var_dump($params);
            $this->attrs = $values;
        }
//        if ($this->primary_key[0] == 'id') {
//            $this->id = null;
//        }

        return $this->save();
    }

    /**
     * Saves Information on the ActiveRecord Properties.
     *
     * @param array $values array de valores a cargar
     *
     * @return bool success
     */
    public function save($values = null) {
        if ($values) {
            if (!is_array($values)) {
                $values = Util::getParams(func_get_args());
            }
        }

        if (!isset($this->_id)) {
            $res = $this->collection->insertOne($this);
            $this->_id = $res->getInsertedId();
            return $this;
        } else {
            $this->collection->FindOneAndUpdate(array('_id' => $this->_id), array('$set' => $this));
            return $this->findOne($this->_id);
        }

        foreach ($this->addMany as $k => $v) {
            foreach ($v as $s) {
                echo($k);
                $class = $k;
                # Se asocia el objeto has_many
                $v[$this->_has_many[Util::smallcase($k)]->fk] = new MongoDB\BSON\ObjectId($this->id);
                (new $class)->create($v);
            }
        }

        // $this->findOne($res->getInsertedId());



        return $this;

        if ($this->schema) {
            $table = $this->schema . '.' . $this->source;
        } else {
            $table = $this->source;
        }
        //Run Validation Callbacks Before
        if (method_exists($this, 'before_validation')) {
            if ($this->before_validation() == 'cancel') {
                return false;
            }
        } else {
            if (isset($this->before_validation)) {
                $method = $this->before_validation;
                if ($this->$method() == 'cancel') {
                    return false;
                }
            }
        }
        if (!$ex) {
            if (method_exists($this, 'before_validation_on_create')) {
                if ($this->before_validation_on_create() == 'cancel') {
                    return false;
                }
            } else {
                if (isset($this->before_validation_on_create)) {
                    $method = $this->before_validation_on_create;
                    if ($this->$method() == 'cancel') {
                        return false;
                    }
                }
            }
        }
        if ($ex) {
            if (method_exists($this, 'before_validation_on_update')) {
                if ($this->before_validation_on_update() == 'cancel') {
                    return false;
                }
            } else {
                if (isset($this->before_validation_on_update)) {
                    $method = $this->before_validation_on_update;
                    if ($this->$method() == 'cancel') {
                        return false;
                    }
                }
            }
        }
        /*
         * Validacion validates_presence
         *
         */
        if (isset($this->_validates['presence_of'])) {
            foreach ($this->_validates['presence_of'] as $f => $opt) {
                if (isset($this->$f) && (is_null($this->$f) || $this->$f === '')) {
                    if (!$ex && $f == $this->primary_key[0]) {
                        continue;
                    }
                    if (isset($opt['message'])) {
                        Flash::error($opt['message']);

                        return false;
                    } else {
                        $field = isset($opt['field']) ? $opt['field'] : $f;
                        Flash::error("Error: El campo $field no puede ser nulo");

                        return false;
                    }
                }
            }
        }
        /*
         * Recordamos que aqui no aparecen los que tienen valores por defecto,
         * pero sin embargo se debe estar pendiente de validar en las otras verificaciones
         * los campos nulos, ya que en estas si el campo es nulo, realmente se refiere a un campo que
         * debe tomar el valor por defecto
         *
         */
        foreach ($this->not_null as $f) {
            if (in_array($f, $this->_with_default)) {
                continue;
            }
            if (!isset($this->$f) || is_null($this->$f) || $this->$f == '') {
                if (!$ex && $f == $this->primary_key[0]) {
                    continue;
                }
                if (!$ex && in_array($f, $this->_at)) {
                    continue;
                }
                if ($ex && in_array($f, $this->_in)) {
                    continue;
                }
                Flash::error("Error: El campo $f no puede ser nulo");

                return false;
            }
        }
        /*
         * Validacion validates_length
         *
         */
        if (isset($this->_validates['length_of'])) {
            foreach ($this->_validates['length_of'] as $f => $opt) {
                if (isset($this->$f) && !is_null($this->$f) && $this->$f != '') {
                    $field = isset($opt['field']) ? $opt['field'] : $f;
                    if (strlen($this->$f) < $opt['min']) {
                        if (isset($opt['too_short'])) {
                            Flash::error($opt['too_short']);
                        } else {
                            Flash::error("Error: El campo $field debe tener como mínimo $opt[min] caracteres");
                        }

                        return false;
                    }
                    if (strlen($this->$f) > $opt['max']) {
                        if (isset($opt['too_long'])) {
                            Flash::error($opt['too_long']);
                        } else {
                            Flash::error("Error: El campo $field debe tener como máximo $opt[max] caracteres");
                        }

                        return false;
                    }
                }
            }
        }
        /*
         * Validacion validates_inclusion
         *
         */
        foreach ($this->_validates['inclusion_in'] as $f => $opt) {
            if (isset($this->$f) && !is_null($this->$f) && $this->$f != '') {
                if (!in_array($this->$f, $opt['list'])) {
                    if (isset($opt['message'])) {
                        Flash::error($opt['message']);
                    } else {
                        $field = isset($opt['field']) ? $opt['field'] : $f;
                        Flash::error("$field debe tener un valor entre (" . join(',', $opt['list']) . ')');
                    }

                    return false;
                }
            }
        }
        /*
         * Validacion validates_exclusion
         *
         */
        foreach ($this->_validates['exclusion_of'] as $f => $opt) {
            if (isset($this->$f) && !is_null($this->$f) && $this->$f != '') {
                if (in_array($this->$f, $opt['list'])) {
                    if (isset($opt['message'])) {
                        Flash::error($opt['message']);
                    } else {
                        $field = isset($opt['field']) ? $opt['field'] : $f;
                        Flash::error("$field no debe tener un valor entre (" . join(',', $opt['list']) . ')');
                    }

                    return false;
                }
            }
        }
        /*
         * Validacion validates_numericality
         *
         */
        foreach ($this->_validates['numericality_of'] as $f => $opt) {
            if (isset($this->$f) && !is_null($this->$f) && $this->$f != '') {
                if (!is_numeric($this->$f)) {
                    if (isset($opt['message'])) {
                        Flash::error($opt['message']);
                    } else {
                        $field = isset($opt['field']) ? $opt['field'] : $f;
                        Flash::error("$field debe tener un valor numérico");
                    }

                    return false;
                }
            }
        }
        /*
         * Validacion validates_format
         *
         */
        foreach ($this->_validates['format_of'] as $f => $opt) {
            if (isset($this->$f) && !is_null($this->$f) && $this->$f != '') {
                if (!filter_var($this->$f, FILTER_VALIDATE_REGEXP, array('options' => array('regexp' => $opt['pattern'])))) {
                    if (isset($opt['message'])) {
                        Flash::error($opt['message']);
                    } else {
                        $field = isset($opt['field']) ? $opt['field'] : $f;
                        Flash::error("Formato erroneo para $field");
                    }

                    return false;
                }
            }
        }
        /*
         * Validacion validates_date
         *
         */
        foreach ($this->_validates['date_in'] as $f => $opt) {
            if (isset($this->$f) && !is_null($this->$f) && $this->$f != '') {
                if (!filter_var($this->$f, FILTER_VALIDATE_REGEXP, array('options' => array('regexp' => "/^\d{4}[-\/](0[1-9]|1[012])[-\/](0[1-9]|[12][0-9]|3[01])$/")))) {
                    if (isset($opt['message'])) {
                        Flash::error($opt['message']);
                    } else {
                        $field = isset($opt['field']) ? $opt['field'] : $f;
                        Flash::error("Formato de fecha erroneo para $field");
                    }

                    return false;
                }
            }
        }
        /*
         * Validacion validates_email
         *
         */
        foreach ($this->_validates['email_in'] as $f => $opt) {
            if (isset($this->$f) && !is_null($this->$f) && $this->$f != '') {
                if (!filter_var($this->$f, FILTER_VALIDATE_EMAIL)) {
                    if (isset($opt['message'])) {
                        Flash::error($opt['message']);
                    } else {
                        $field = isset($opt['field']) ? $opt['field'] : $f;
                        Flash::error("Formato de e-mail erroneo en el campo $field");
                    }

                    return false;
                }
            }
        }
        /**
         * Validacion validates_uniqueness.
         */
        // parche para que no tome encuenta el propio registro
        // al validar campos unicos, ya que si lo toma en cuenta
        // lanzará error de validacion porque ya existe un registro
        // con igual valor en el campo unico.
        $and_condition = $ex ? " AND {$this->primary_key[0]} != '{$this->{$this->primary_key[0]}}'" : '';
        foreach ($this->_validates['uniqueness_of'] as $f => $opt) {
            if (isset($this->$f) && !is_null($this->$f) && $this->$f != '') {
                $result = $this->db->fetch_one("SELECT COUNT(*) FROM $table WHERE $f = {$this->db->add_quotes($this->$f)} $and_condition");
                if ($result[0]) {
                    if (isset($opt['message'])) {
                        Flash::error($opt['message']);
                    } else {
                        $field = isset($opt['field']) ? $opt['field'] : $f;
                        Flash::error("El valor '{$this->$f}' ya existe para el campo $field");
                    }

                    return false;
                }
            }
        }
        //Run Validation Callbacks After
        if (!$ex) {
            if (method_exists($this, 'after_validation_on_create')) {
                if ($this->after_validation_on_create() == 'cancel') {
                    return false;
                }
            } else {
                if (isset($this->after_validation_on_create)) {
                    $method = $this->after_validation_on_create;
                    if ($this->$method() == 'cancel') {
                        return false;
                    }
                }
            }
        }
        if ($ex) {
            if (method_exists($this, 'after_validation_on_update')) {
                if ($this->after_validation_on_update() == 'cancel') {
                    return false;
                }
            } else {
                if (isset($this->after_validation_on_update)) {
                    $method = $this->after_validation_on_update;
                    if ($this->$method() == 'cancel') {
                        return false;
                    }
                }
            }
        }
        if (method_exists($this, 'after_validation')) {
            if ($this->after_validation() == 'cancel') {
                return false;
            }
        } else {
            if (isset($this->after_validation)) {
                $method = $this->after_validation;
                if ($this->$method() == 'cancel') {
                    return false;
                }
            }
        }
        // Run Before Callbacks
        if (method_exists($this, 'before_save')) {
            if ($this->before_save() == 'cancel') {
                return false;
            }
        } else {
            if (isset($this->before_save)) {
                $method = $this->before_save;
                if ($this->$method() == 'cancel') {
                    return false;
                }
            }
        }
        if ($ex) {
            if (method_exists($this, 'before_update')) {
                if ($this->before_update() == 'cancel') {
                    return false;
                }
            } else {
                if (isset($this->before_update)) {
                    $method = $this->before_update;
                    if ($this->$method() == 'cancel') {
                        return false;
                    }
                }
            }
        }
        if (!$ex) {
            if (method_exists($this, 'before_create')) {
                if ($this->before_create() == 'cancel') {
                    return false;
                }
            } else {
                if (isset($this->before_create)) {
                    $method = $this->before_create;
                    if ($this->$method() == 'cancel') {
                        return false;
                    }
                }
            }
        }
        $environment = Config::read('databases');
        $config = $environment[$this->get_database()];
        if ($ex) {
            $fields = array();
            $values = array();
            foreach ($this->non_primary as $np) {
                $np = self::sql_item_sanitize($np);
                if (in_array($np, $this->_in)) {
                    if ($config['type'] == 'oracle') {
                        $this->$np = date('Y-m-d');
                    } else {
                        $this->$np = date('Y-m-d G:i:s');
                    }
                }
                if (isset($this->$np)) {
                    $fields[] = $np;
                    if (is_null($this->$np) || $this->$np == '' && $this->$np != '0') {
                        $values[] = 'NULL';
                    } else {
                        /*
                         * Se debe especificar el formato de fecha en Oracle
                         */
                        if ($this->_data_type[$np] == 'date' && $config['type'] == 'oracle') {
                            $values[] = 'TO_DATE(' . $this->db->add_quotes($this->$np) . ", 'YYYY-MM-DD')";
                        } else {
                            $values[] = $this->db->add_quotes($this->$np);
                        }
                    }
                }
            }
            $val = $this->db->update($table, $fields, $values, $this->_where_pk);
        } else {
            $fields = array();
            $values = array();
            foreach ($this->fields as $field) {
                if ($field != $this->primary_key[0] || $this->{$this->primary_key[0]}) {
                    if (in_array($field, $this->_at)) {
                        if ($config['type'] == 'oracle') {
                            $this->$field = date('Y-m-d');
                        } else {
                            $this->$field = date('Y-m-d G:i:s');
                        }
                    }
                    if (in_array($field, $this->_in)) {
                        unset($this->$field);
                    }
                    if (isset($this->$field) && $this->$field !== '' && $this->$field !== null) {
                        $fields[] = self::sql_sanitize($field);

                        if (($this->_data_type[$field] == 'datetime' || $this->_data_type[$field] == 'date') && ($config['type'] == 'mysql' || $config['type'] == 'mysqli')) {
                            $values[] = $this->db->add_quotes(date('Y-m-d G:i:s', strtotime($this->$field)));
                        } elseif ($this->_data_type[$field] == 'date' && $config['type'] == 'oracle') {
                            //Se debe especificar el formato de fecha en Oracle
                            $values[] = 'TO_DATE(' . $this->db->add_quotes($this->$field) . ", 'YYYY-MM-DD')";
                        } else {
                            $values[] = $this->db->add_quotes($this->$field);
                        }
                    } elseif (in_array($field, $this->_with_default)) {
                        $fields[] = self::sql_sanitize($field);
                        $values[] = 'DEFAULT';
                    } else {
                        $fields[] = self::sql_sanitize($field);
                        $values[] = 'NULL';
                    }
                } else {
                    /*
                     * Campos autonumericos en Oracle deben utilizar una sequencia auxiliar
                     */
                    if ($config['type'] == 'oracle') {
                        if (!$this->id) {
                            $fields[] = 'id';
                            $values[] = $this->source . '_id_seq.NEXTVAL';
                        }
                    }
                    if ($config['type'] == 'informix') {
                        if (!$this->id) {
                            $fields[] = 'id';
                            $values[] = 0;
                        }
                    }
                }
            }
            $val = $this->db->insert($table, $values, $fields);
        }
        if (!isset($config['pdo']) && $config['type'] == 'oracle') {
            $this->commit();
        }
        if (!$ex) {
            //$this->db->logger = true;
            $m = $this->db->last_insert_id($table, $this->primary_key[0]);
            $this->find_first($m);
        }
        if ($val) {
            if ($ex) {
                if (method_exists($this, 'after_update')) {
                    if ($this->after_update() == 'cancel') {
                        return false;
                    }
                } else {
                    if (isset($this->after_update)) {
                        $method = $this->after_update;
                        if ($this->$method() == 'cancel') {
                            return false;
                        }
                    }
                }
            }
            if (!$ex) {
                if (method_exists($this, 'after_create')) {
                    if ($this->after_create() == 'cancel') {
                        return false;
                    }
                } else {
                    if (isset($this->after_create)) {
                        $method = $this->after_create;
                        if ($this->$method() == 'cancel') {
                            return false;
                        }
                    }
                }
            }
            if (method_exists($this, 'after_save')) {
                if ($this->after_save() == 'cancel') {
                    return false;
                }
            } else {
                if (isset($this->after_save)) {
                    $method = $this->after_save;
                    if ($this->$method() == 'cancel') {
                        return false;
                    }
                }
            }

            return $val;
        } else {
            return false;
        }
    }

    /**
     * Find All data in the Relational Table.
     *
     * @param string $field
     * @param string $value
     *
     * @return ActiveRecord Cursor
     */
    public function find_all_by($field, $value) {
        self::sql_item_sanitize($field);

        return $this->find("conditions: $field = {$this->db->add_quotes($value) }");
    }

    /**
     * 2020-03-03.
     *
     * @param mixed $values
     *
     * @return bool|null sucess
     */
    public function update() {
        $this->collection->FindOneAndUpdate(array('_id' => $this->document["_id"]), array('$set' => $this));
        return $this->findOne($this->document["_id"]);


        if ($this->exists()) {
            if (method_exists($this, 'before_change')) {
                $obj = clone $this;
                if ($this->before_change($obj->find($this->{$this->primary_key[0]})) == 'cancel') {
                    return false;
                }
                unset($obj);
            }
            if ($this->save()) {
                if (method_exists($this, 'after_change')) {
                    if ($this->after_change($this) == 'cancel') {
                        return false;
                    }
                }

                return true;
            }
        } else {
            Flash::error('No se puede actualizar porque el registro no existe');

            return false;
        }
    }

    /**
     * Deletes data from Relational Map Table.
     * delete()
     * delete("id")
     * delete(["activo" => 0])
     *
     * @param mixed $what
     */
    public function delete($what = null) {
        $filters = [];

        if (is_array($what)) {
            $filters = $what;
            $filters = [];
            foreach ($what as $w) {
                $j = new Jardin();
                //$filters[] = $j->findOne($w["_id"]);
                $filters = $j->find();
            }
        }

        if (func_num_args() > 1) {
            $filters = Util::getParams(func_get_args());
        }

        // delete("5c886d6e0a21a0043345c79b")
//        if (is_string($what) && func_num_args() === 1) {
//            $filters["_id"] = $what;
//        }
        //la instancia cuenta con un id
        if ($what === null) {
            $filters = $this;
        }

        //var_dump($filters);
        return $this->collection->deleteMany($filters);
        // true;


        if (func_num_args() > 1) {
            $what = Util::getParams(func_get_args());
        }
        if ($this->schema) {
            $table = $this->schema . '.' . $this->source;
        } else {
            $table = $this->source;
        }
        $conditions = '';
        if (is_array($what)) {
            if ($what['conditions']) {
                $conditions = $what['conditions'];
            }
        } else {
            if (is_numeric($what)) {
                self::sql_sanitize($this->primary_key[0]);
                $conditions = "{$this->primary_key[0]} = '$what'";
            } else {
                if ($what) {
                    $conditions = $what;
                } else {
                    self::sql_sanitize($this->primary_key[0]);
                    $conditions = "{$this->primary_key[0]} = '{$this->{$this->primary_key[0]}}'";
                }
            }
        }
        if (method_exists($this, 'before_delete')) {
            if ($this->{$this->primary_key[0]}) {
                $this->find($this->{$this->primary_key[0]});
            }
            if ($this->before_delete() == 'cancel') {
                return false;
            }
        } else {
            if (isset($this->before_delete)) {
                if ($this->{$this->primary_key[0]}) {
                    $this->find($this->{$this->primary_key[0]});
                }
                $method = $this->before_delete;
                if ($this->$method() == 'cancel') {
                    return false;
                }
            }
        }
        $val = $this->db->delete($table, $conditions);
        if ($val) {
            if (method_exists($this, 'after_delete')) {
                if ($this->after_delete() == 'cancel') {
                    return false;
                }
            } else {
                if (isset($this->after_delete)) {
                    $method = $this->after_delete;
                    if ($this->$method() == 'cancel') {
                        return false;
                    }
                }
            }
        }

        return $val;
    }

    /**
     * Actualiza todos los atributos de la entidad
     * $Clientes->update_all("estado='A', fecha='2005-02-02'", "id>100");
     * $Clientes->update_all("estado='A', fecha='2005-02-02'", "id>100", "limit: 10");.
     *
     * @param string $values
     */
    public function update_all($values) {
        $params = array();
        if ($this->schema) {
            $table = $this->schema . '.' . $this->source;
        } else {
            $table = $this->source;
        }
        if (func_num_args() > 1) {
            $params = Util::getParams(func_get_args());
        }
        if (!isset($params['conditions']) || !$params['conditions']) {
            if (isset($params[1])) {
                $params['conditions'] = $params[1];
            } else {
                $params['conditions'] = '';
            }
        }
        if ($params['conditions']) {
            $params['conditions'] = ' WHERE ' . $params['conditions'];
        }
        $sql = "UPDATE $table SET $values {$params['conditions']}";
        $limit_args = array($sql);
        if (isset($params['limit'])) {
            array_push($limit_args, "limit: $params[limit]");
        }
        if (isset($params['offset'])) {
            array_push($limit_args, "offset: $params[offset]");
        }
        if (count($limit_args) > 1) {
            $sql = call_user_func_array(array($this, 'limit'), $limit_args);
        }
        $environment = Config::read('databases');
        $config = $environment[$this->get_database()];
        if (!isset($config->pdo) || !$config->pdo) {
            if ($config['type'] == 'informix') {
                $this->db->set_return_rows(false);
            }
        }

        return $this->db->query($sql);
    }

    /**
     * Delete All data from Relational Map Table.
     *
     * @param string $conditions
     *
     * @return bool
     */
    public function delete_all($conditions = '') {
        //$limit = '';
        if ($this->schema) {
            $table = $this->schema . '.' . $this->source;
        } else {
            $table = $this->source;
        }
        if (func_num_args() > 1) {
            $params = Util::getParams(func_get_args());
            $limit_args = array($select);
            if (isset($params['limit'])) {
                array_push($limit_args, "limit: $params[limit]");
            }
            if (isset($params['offset'])) {
                array_push($limit_args, "offset: $params[offset]");
            }
            if (count($limit_args) > 1) {
                $select = call_user_func_array(array($this, 'limit'), $limit_args);
            }
        }

        return $this->db->delete($table, $conditions);
    }

    /**
     * *********************************************************************************
     * Metodos de Debug
     * *********************************************************************************.
     */

    /**
     * Imprime una version humana de los valores de los campos
     * del modelo en una sola linea.
     */
    public function inspect() {
        $inspect = array();
        foreach ($this->fields as $field) {
            if (!is_array($field)) {
                $inspect[] = "$field: {$this->$field}";
            }
        }

        return join(', ', $inspect);
    }

    /**
     * *********************************************************************************
     * Metodos de Validacion
     * *********************************************************************************.
     */

    /**
     * Valida que el campo no sea nulo.
     *
     * @param string $field  campo a validar
     * @param array  $params parametros adicionales
     *
     * message: mensaje a mostrar
     * field: nombre de campo a mostrar en el mensaje
     */
    protected function validates_presence_of($field, $params = array()) {
        if (is_string($params)) {
            $params = Util::getParams(func_get_args());
        }
        $this->_validates['presence_of'][$field] = $params;
    }

    /**
     * Valida el tamañoo de ciertos campos antes de insertar
     * o actualizar.
     *
     * @params string $field campo a validar
     *
     * @param int   $max    valor maximo
     * @param int   $min    valor minimo
     * @param array $params parametros adicionales
     *
     * too_short: mensaje a mostrar cuando se muy corto
     * too_long: mensaje a mostrar cuando sea muy largo
     * field: nombre de campo a mostrar en el mensaje
     */
    protected function validates_length_of($field, $max, $min = 0, $params = array()) {
        if (is_string($params)) {
            $params = Util::getParams(func_get_args());
        }
        $this->_validates['length_of'][$field] = $params;
        $this->_validates['length_of'][$field]['min'] = $min;
        $this->_validates['length_of'][$field]['max'] = $max;
    }

    /**
     * Valida que el campo se encuentre entre los valores de una lista
     * antes de insertar o actualizar.
     *
     * @param string $field campo a validar
     * @param array  $list
     *
     * message: mensaje a mostrar
     * field: nombre del campo a mostrar en el mensaje
     */
    protected function validates_inclusion_in($field, $list, $params = array()) {
        if (is_string($params)) {
            $params = Util::getParams(func_get_args());
        }
        $this->_validates['inclusion_in'][$field] = $params;
        $this->_validates['inclusion_in'][$field]['list'] = $list;
    }

    /**
     * Valida que el campo no se encuentre entre los valores de una lista
     * antes de insertar o actualizar.
     *
     * @param string $field campo a validar
     * @param array  $list
     *
     * message: mensaje a mostrar
     * field: nombre del campo a mostrar en el mensaje
     */
    protected function validates_exclusion_of($field, $list, $params = array()) {
        if (is_string($params)) {
            $params = Util::getParams(func_get_args());
        }
        $this->_validates['exclusion_of'][$field] = $params;
        $this->_validates['exclusion_of'][$field]['list'] = $list;
    }

    /**
     * Valida que el campo tenga determinado formato segun una expresion regular
     * antes de insertar o actualizar.
     *
     * @param string $field   campo a validar
     * @param string $pattern expresion regular para preg_match
     *
     * message: mensaje a mostrar
     * field: nombre del campo a mostrar en el mensaje
     */
    protected function validates_format_of($field, $pattern, $params = array()) {
        if (is_string($params)) {
            $params = Util::getParams(func_get_args());
        }
        $this->_validates['format_of'][$field] = $params;
        $this->_validates['format_of'][$field]['pattern'] = $pattern;
    }

    /**
     * Valida que ciertos atributos tengan un valor numerico
     * antes de insertar o actualizar.
     *
     * @param string $field campo a validar
     *
     * message: mensaje a mostrar
     * field: nombre del campo a mostrar en el mensaje
     */
    protected function validates_numericality_of($field, $params = array()) {
        if (is_string($params)) {
            $params = Util::getParams(func_get_args());
        }
        $this->_validates['numericality_of'][$field] = $params;
    }

    /**
     * Valida que ciertos atributos tengan un formato de e-mail correcto
     * antes de insertar o actualizar.
     *
     * @param string $field campo a validar
     *
     * message: mensaje a mostrar
     * field: nombre del campo a mostrar en el mensaje
     */
    protected function validates_email_in($field, $params = array()) {
        if (is_string($params)) {
            $params = Util::getParams(func_get_args());
        }
        $this->_validates['email_in'][$field] = $params;
    }

    /**
     * Valida que ciertos atributos tengan un valor unico antes
     * de insertar o actualizar.
     *
     * @param string $field campo a validar
     *
     * message: mensaje a mostrar
     * field: nombre del campo a mostrar en el mensaje
     */
    protected function validates_uniqueness_of($field, $params = array()) {
        if (is_string($params)) {
            $params = Util::getParams(func_get_args());
        }
        $this->_validates['uniqueness_of'][$field] = $params;
    }

    /**
     * Valida que ciertos atributos tengan un formato de fecha acorde al indicado en
     * config/config.ini antes de insertar o actualizar.
     *
     * @param string $field campo a validar
     *
     * message: mensaje a mostrar
     * field: nombre del campo a mostrar en el mensaje
     */
    protected function validates_date_in($field, $params = array()) {
        if (is_string($params)) {
            $params = Util::getParams(func_get_args());
        }
        $this->_validates['date_in'][$field] = $params;
    }

    /**
     * Verifica si un campo es de tipo de dato númerico o no.
     *
     * @param string $field
     *
     * @return bool
     */
    public function is_a_numeric_type($field) {
        return (strpos(' ' . $this->_data_type[$field], 'int') || strpos(' ' . $this->_data_type[$field], 'decimal') || strpos(' ' . $this->_data_type[$field], 'number'));
    }

    /**
     * Obtiene los datos de los metadatos generados por Primera vez en la Sesión.
     *
     * @param string $table
     *
     * @return array
     */
    public static function get_meta_data($table) {
        if (isset(self::$models[$table])) {
            return self::$models[$table];
        }
        if (PRODUCTION) {
            $metadata = Cache::driver()->get($table, 'kumbia.models');
            if ($metadata) {
                return self::$models[$table] = unserialize($metadata);
            }
        }

        return array();
    }

    /**
     * Crea un registro de meta datos para la tabla especificada.
     *
     * @param string $table
     * @param array  $meta_data
     */
    public static function set_meta_data($table, $meta_data) {
        if (PRODUCTION) {
            Cache::driver()->save(serialize($meta_data), Config::get('config.application.metadata_lifetime'), $table, 'kumbia.models');
        }
        self::$models[$table] = $meta_data;

        return true;
    }

    /**     * *****************************************************************************************
     * Métodos para generación de relaciones.
     * ***************************************************************************************** */

    /**
     * Crea una relacion 1-1 entre dos modelos.
     *
     * @param string $relation
     *
     * model : nombre del modelo al que se refiere
     * fk : campo por el cual se relaciona (llave foranea)
     */
    protected function has_one($relation) {
        $params = Util::getParams(func_get_args());
        for ($i = 0; isset($params[$i]); ++$i) {
            $relation = Util::smallcase($params[$i]);
            $index = explode('/', $relation);
            $index = end($index);
            if (!array_key_exists($index, $this->_has_one)) {
                $this->_has_one[$index] = new stdClass();
                $this->_has_one[$index]->model = isset($params['model']) ? $params['model'] : $relation;
                $this->_has_one[$index]->fk = isset($params['fk']) ? $params['fk'] : Util::smallcase(get_class($this)) . '_id';
            }
        }
    }

    /**
     * Crea una relacion 1-1 inversa entre dos modelos.
     *
     * @param string $relation
     *
     * model : nombre del modelo al que se refiere
     * fk : campo por el cual se relaciona (llave foranea)
     */
    protected function belongs_to($relation) {
        $params = Util::getParams(func_get_args());
        for ($i = 0; isset($params[$i]); ++$i) {
            $relation = Util::smallcase($params[$i]);
            $index = explode('/', $relation);
            $index = end($index);
            if (!array_key_exists($index, $this->_belongs_to)) {
                $this->_belongs_to[$index] = new stdClass();
                $this->_belongs_to[$index]->model = isset($params['model']) ? $params['model'] : $relation;
                $this->_belongs_to[$index]->fk = isset($params['fk']) ? $params['fk'] : "{$relation}_id";
            }
        }
    }

    /**
     * Crea una relacion 1-n entre dos modelos.
     *
     * @param string $relation
     *
     * model : nombre del modelo al que se refiere
     * fk : campo por el cual se relaciona (llave foranea)
     */
    protected function has_many($relation) {
        $params = Util::getParams(func_get_args());
        for ($i = 0; isset($params[$i]); ++$i) {
            $relation = Util::smallcase($params[$i]);
            $index = explode('/', $relation);
            $index = end($index);
            if (!array_key_exists($index, $this->_has_many)) {
                $this->_has_many[$index] = new stdClass();
                $this->_has_many[$index]->model = isset($params['model']) ? $params['model'] : $relation;
                $this->_has_many[$index]->fk = isset($params['fk']) ? $params['fk'] : Util::smallcase(get_class($this)) . '_id';
            }
        }
    }

    /**
     * Crea una relación n-n o 1-n inversa entre dos modelos.
     *
     * @param string $relation
     *
     * model : nombre del modelo al que se refiere
     * fk : campo por el cual se relaciona (llave foranea)
     * key: campo llave que identifica al propio modelo
     * through : através de que tabla
     */
    protected function has_and_belongs_to_many($relation) {
        $params = Util::getParams(func_get_args());
        for ($i = 0; isset($params[$i]); ++$i) {
            $relation = Util::smallcase($params[$i]);
            if (!array_key_exists($relation, $this->_has_and_belongs_to_many)) {
                $this->_has_and_belongs_to_many[$relation] = new stdClass();
                $this->_has_and_belongs_to_many[$relation]->model = isset($params['model']) ? $params['model'] : $relation;
                $this->_has_and_belongs_to_many[$relation]->fk = isset($params['fk']) ? $params['fk'] : "{$relation}_id";
                $this->_has_and_belongs_to_many[$relation]->key = isset($params['key']) ? $params['key'] : Util::smallcase(get_class($this)) . '_id';
                if (isset($params['through'])) {
                    $this->_has_and_belongs_to_many[$relation]->through = $params['through'];
                }
            }
        }
    }

    /**
     * Herencia Simple.
     */

    /**
     * Especifica que la clase es padre de otra.
     *
     * @param string $parent
     */
    public function parent_of($parent) {
        $parents = func_get_args();
        foreach ($parents as $parent) {
            if (!in_array($parent, $this->parent_of)) {
                $this->parent_of[] = $parent;
            }
        }
    }

    /**
     * Elimina caracteres que podrian ayudar a ejecutar
     * un ataque de Inyeccion SQL.
     *
     * @param string $sql_item
     */
    public static function sql_item_sanitize($sql_item) {
        $sql_item = trim($sql_item);
        if ($sql_item !== '' && $sql_item !== null) {
            $sql_temp = preg_replace('/\s+/', '', $sql_item);
            if (!preg_match('/^[a-zA-Z0-9_\.]+$/', $sql_temp)) {
                throw new KumbiaException('Se está tratando de ejecutar un SQL peligroso!');
            }
        }

        return $sql_item;
    }

    /**
     * Elimina caracteres que podrian ayudar a ejecutar
     * un ataque de Inyeccion SQL.
     *
     * @param string $sql_item
     */
    public static function sql_sanitize($sql_item) {
        $sql_item = trim($sql_item);
        if ($sql_item !== '' && $sql_item !== null) {
            $sql_temp = preg_replace('/\s+/', '', $sql_item);
            if (!preg_match('/^[a-zA-Z_0-9\,\(\)\.\*]+$/', $sql_temp)) {
                throw new KumbiaException('Se está tratando de ejecutar un SQL peligroso!');
            }
        }

        return $sql_item;
    }

    /**
     * Al sobreescribir este metodo se puede controlar las excepciones de un modelo.
     *
     * @param unknown_type $e
     */
    protected function exceptions($e) {
        throw $e;
    }

    /**
     * Implementacion de __toString Standard.
     */
    public function __toString() {
        return '<' . get_class($this) . ' Object>';
    }

    /**
     * Paginador para el modelo.
     *
     * conditions: condiciones para paginacion
     * page: numero de pagina a mostrar (por defecto la pagina 1)
     * per_page: cantidad de elementos por pagina (por defecto 10 items por pagina)
     *
     * @return un objeto Page identico al que se regresa con el util paginate
     */
    public function paginate() {
        $args = func_get_args();
        array_unshift($args, $this);
        //if(!class_exists('Paginator')){
        require_once CORE_PATH . 'libs/kumbia_active_record/behaviors/paginate.php';
        //}
        return call_user_func_array(array('Paginator', 'paginate'), $args);
    }

    /**
     * Paginador para el modelo atraves de consulta sql.
     *
     * @param string $sql consulta sql
     *
     * page: numero de pagina a mostrar (por defecto la pagina 1)
     * per_page: cantidad de elementos por pagina (por defecto 10 items por pagina)
     *
     * @return un objeto Page identico al que se regresa con el util paginate_by_sql
     */
    public function paginate_by_sql($sql) {
        $args = func_get_args();
        array_unshift($args, $this);
        //if(!class_exists('Paginator')){
        require_once CORE_PATH . 'libs/kumbia_active_record/behaviors/paginate.php';
        //}
        return call_user_func_array(array('Paginator', 'paginate_by_sql'), $args);
    }

    /**
     * Operaciones al serializar.
     *
     * */
    public function __sleep() {
        /*
         * Anulando conexion a bd en el modelo
         * */
        $this->db = null;

        return array_keys(get_object_vars($this));
    }

    /**
     * Operaciones al deserializar.
     *
     * */
    public function __wakeup() {
        /*
         * Restableciendo conexion a la bd
         * */
        $this->_connect();
    }

    /**
     * Obtiene la instacia de un modelo.
     *
     * @param string $model
     *
     * @return ActiveRecord
     * @throw KumbiaException
     * */
    public static function get($model) {
        /**
         * Nombre de la clase.
         * */
        $Model = Util::camelcase($model);

        return new $Model();
    }

    /**
     * Devuelve un JSON de este modelo.
     *
     * @return string JSON del modelo
     */
    public function to_json() {
        return json_encode($this->attrs);
    }

    /**
     * Devuelve un array de este modelo.
     *
     * @return array Array del modelo
     */
    public function to_array() {
        return (array) $this;
    }

    /**
     * Devuelve un PHP serial de este modelo
     * Usarlo con cuidado, ya que pasa todos los atributos, no sólo los publicos.
     *
     * @return string Serial de PHP del modelo
     */
    public function serialize() {
        return serialize($this);
    }

}
