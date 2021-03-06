<?php
/** AuthTokens Data Access Object (DAO) Base.
  * 
  * Esta clase contiene toda la manipulacion de bases de datos que se necesita para 
  * almacenar de forma permanente y recuperar instancias de objetos {@link AuthTokens }. 
  * @author alanboy
  * @access private
  * @abstract
  * @package docs
  * 
  */
abstract class AuthTokensDAOBase extends DAO
{

		private static $loadedRecords = array();

		private static function recordExists(  $token ){
			return false;
			$pk = "";
			$pk .= $token . "-";
			return array_key_exists ( $pk , self::$loadedRecords );
		}
		private static function pushRecord( $inventario,  $token){
			$pk = "";
			$pk .= $token . "-";
			self::$loadedRecords [$pk] = $inventario;
		}
		private static function getRecord(  $token ){
			$pk = "";
			$pk .= $token . "-";
			return self::$loadedRecords[$pk];
		}
	/**
	  *	Guardar registros. 
	  *	
	  *	Este metodo guarda el estado actual del objeto {@link AuthTokens} pasado en la base de datos. La llave 
	  *	primaria indicara que instancia va a ser actualizado en base de datos. Si la llave primara o combinacion de llaves
	  *	primarias describen una fila que no se encuentra en la base de datos, entonces save() creara una nueva fila, insertando
	  *	en ese objeto el ID recien creado.
	  *	
	  *	@static
	  * @throws Exception si la operacion fallo.
	  * @param AuthTokens [$Auth_Tokens] El objeto de tipo AuthTokens
	  * @return Un entero mayor o igual a cero denotando las filas afectadas.
	  **/
	public static final function save( &$Auth_Tokens )
	{
		if(  self::getByPK(  $Auth_Tokens->getToken() ) !== NULL )
		{
			try{ return AuthTokensDAOBase::update( $Auth_Tokens) ; } catch(Exception $e){ throw $e; }
		}else{
			try{ return AuthTokensDAOBase::create( $Auth_Tokens) ; } catch(Exception $e){ throw $e; }
		}
	}


	/**
	  *	Obtener {@link AuthTokens} por llave primaria. 
	  *	
	  * Este metodo cargara un objeto {@link AuthTokens} de la base de datos 
	  * usando sus llaves primarias. 
	  *	
	  *	@static
	  * @return @link AuthTokens Un objeto del tipo {@link AuthTokens}. NULL si no hay tal registro.
	  **/
	public static final function getByPK(  $token )
	{
		if(self::recordExists(  $token)){
			return self::getRecord( $token );
		}
		$sql = "SELECT * FROM Auth_Tokens WHERE (token = ? ) LIMIT 1;";
		$params = array(  $token );
		global $conn;
		$rs = $conn->GetRow($sql, $params);
		if(count($rs)==0)return NULL;
			$foo = new AuthTokens( $rs );
			self::pushRecord( $foo,  $token );
			return $foo;
	}


	/**
	  *	Obtener todas las filas.
	  *	
	  * Esta funcion leera todos los contenidos de la tabla en la base de datos y construira
	  * un vector que contiene objetos de tipo {@link AuthTokens}. Tenga en cuenta que este metodo
	  * consumen enormes cantidades de recursos si la tabla tiene muchas filas. 
	  * Este metodo solo debe usarse cuando las tablas destino tienen solo pequenas cantidades de datos o se usan sus parametros para obtener un menor numero de filas.
	  *	
	  *	@static
	  * @param $pagina Pagina a ver.
	  * @param $columnas_por_pagina Columnas por pagina.
	  * @param $orden Debe ser una cadena con el nombre de una columna en la base de datos.
	  * @param $tipo_de_orden 'ASC' o 'DESC' el default es 'ASC'
	  * @return Array Un arreglo que contiene objetos del tipo {@link AuthTokens}.
	  **/
	public static final function getAll( $pagina = NULL, $columnas_por_pagina = NULL, $orden = NULL, $tipo_de_orden = 'ASC' )
	{
		$sql = "SELECT * from Auth_Tokens";
		if($orden != NULL)
		{ $sql .= " ORDER BY " . $orden . " " . $tipo_de_orden;	}
		if($pagina != NULL)
		{
			$sql .= " LIMIT " . (( $pagina - 1 )*$columnas_por_pagina) . "," . $columnas_por_pagina; 
		}
		global $conn;
		$rs = $conn->Execute($sql);
		$allData = array();
		foreach ($rs as $foo) {
			$bar = new AuthTokens($foo);
    		array_push( $allData, $bar);
			//token
    		self::pushRecord( $bar, $foo["token"] );
		}
		return $allData;
	}


	/**
	  *	Buscar registros.
	  *	
	  * Este metodo proporciona capacidad de busqueda para conseguir un juego de objetos {@link AuthTokens} de la base de datos. 
	  * Consiste en buscar todos los objetos que coinciden con las variables permanentes instanciadas de objeto pasado como argumento. 
	  * Aquellas variables que tienen valores NULL seran excluidos en busca de criterios.
	  *	
	  * <code>
	  *  /**
	  *   * Ejemplo de uso - buscar todos los clientes que tengan limite de credito igual a 20000
	  *   {@*} 
	  *	  $cliente = new Cliente();
	  *	  $cliente->setLimiteCredito("20000");
	  *	  $resultados = ClienteDAO::search($cliente);
	  *	  
	  *	  foreach($resultados as $c ){
	  *	  	echo $c->getNombre() . "<br>";
	  *	  }
	  * </code>
	  *	@static
	  * @param AuthTokens [$Auth_Tokens] El objeto de tipo AuthTokens
	  * @param $orderBy Debe ser una cadena con el nombre de una columna en la base de datos.
	  * @param $orden 'ASC' o 'DESC' el default es 'ASC'
	  **/
	public static final function search( $Auth_Tokens , $orderBy = null, $orden = 'ASC')
	{
		$sql = "SELECT * from Auth_Tokens WHERE ("; 
		$val = array();
		if( $Auth_Tokens->getUserId() != NULL){
			$sql .= " user_id = ? AND";
			array_push( $val, $Auth_Tokens->getUserId() );
		}

		if( $Auth_Tokens->getToken() != NULL){
			$sql .= " token = ? AND";
			array_push( $val, $Auth_Tokens->getToken() );
		}

		if(sizeof($val) == 0){return array();}
		$sql = substr($sql, 0, -3) . " )";
		if( $orderBy !== null ){
		    $sql .= " order by " . $orderBy . " " . $orden ;
		
		}
		global $conn;
		$rs = $conn->Execute($sql, $val);
		$ar = array();
		foreach ($rs as $foo) {
			$bar =  new AuthTokens($foo);
    		array_push( $ar,$bar);
    		self::pushRecord( $bar, $foo["token"] );
		}
		return $ar;
	}


	/**
	  *	Actualizar registros.
	  *	
	  * Este metodo es un metodo de ayuda para uso interno. Se ejecutara todas las manipulaciones
	  * en la base de datos que estan dadas en el objeto pasado.No se haran consultas SELECT 
	  * aqui, sin embargo. El valor de retorno indica cu�ntas filas se vieron afectadas.
	  *	
	  * @internal private information for advanced developers only
	  * @return Filas afectadas o un string con la descripcion del error
	  * @param AuthTokens [$Auth_Tokens] El objeto de tipo AuthTokens a actualizar.
	  **/
	private static final function update( $Auth_Tokens )
	{
		$sql = "UPDATE Auth_Tokens SET  user_id = ?, create_time = ? WHERE  token = ?;";
		$params = array( 
			$Auth_Tokens->getUserId(),
			$Auth_Tokens->getCreateTime(),
			$Auth_Tokens->getToken(), );
		global $conn;
		try{$conn->Execute($sql, $params);}
		catch(Exception $e){ throw new Exception ($e->getMessage()); }
		return $conn->Affected_Rows();
	}


	/**
	  *	Crear registros.
	  *	
	  * Este metodo creara una nueva fila en la base de datos de acuerdo con los 
	  * contenidos del objeto AuthTokens suministrado. Asegurese
	  * de que los valores para todas las columnas NOT NULL se ha especificado 
	  * correctamente. Despues del comando INSERT, este metodo asignara la clave 
	  * primaria generada en el objeto AuthTokens dentro de la misma transaccion.
	  *	
	  * @internal private information for advanced developers only
	  * @return Un entero mayor o igual a cero identificando las filas afectadas, en caso de error, regresara una cadena con la descripcion del error
	  * @param AuthTokens [$Auth_Tokens] El objeto de tipo AuthTokens a crear.
	  **/
	private static final function create( &$Auth_Tokens )
	{
		$sql = "INSERT INTO Auth_Tokens ( user_id, token ) VALUES ( ?, ?);";
		$params = array( 
			$Auth_Tokens->getUserId(), 
			$Auth_Tokens->getToken(), 
		 );
		global $conn;
		try{$conn->Execute($sql, $params);}
		catch(Exception $e){ throw new Exception ($e->getMessage()); }
		$ar = $conn->Affected_Rows();
		if($ar == 0) return 0;
		/* save autoincremented value on obj */   /*  */ 
		return $ar;
	}


	/**
	  *	Buscar por rango.
	  *	
	  * Este metodo proporciona capacidad de busqueda para conseguir un juego de objetos {@link AuthTokens} de la base de datos siempre y cuando 
	  * esten dentro del rango de atributos activos de dos objetos criterio de tipo {@link AuthTokens}.
	  * 
	  * Aquellas variables que tienen valores NULL seran excluidos en la busqueda. 
	  * No es necesario ordenar los objetos criterio, asi como tambien es posible mezclar atributos.
	  * Si algun atributo solo esta especificado en solo uno de los objetos de criterio se buscara que los resultados conicidan exactamente en ese campo.
	  *	
	  * <code>
	  *  /**
	  *   * Ejemplo de uso - buscar todos los clientes que tengan limite de credito 
	  *   * mayor a 2000 y menor a 5000. Y que tengan un descuento del 50%.
	  *   {@*} 
	  *	  $cr1 = new Cliente();
	  *	  $cr1->setLimiteCredito("2000");
	  *	  $cr1->setDescuento("50");
	  *	  
	  *	  $cr2 = new Cliente();
	  *	  $cr2->setLimiteCredito("5000");
	  *	  $resultados = ClienteDAO::byRange($cr1, $cr2);
	  *	  
	  *	  foreach($resultados as $c ){
	  *	  	echo $c->getNombre() . "<br>";
	  *	  }
	  * </code>
	  *	@static
	  * @param AuthTokens [$Auth_Tokens] El objeto de tipo AuthTokens
	  * @param AuthTokens [$Auth_Tokens] El objeto de tipo AuthTokens
	  * @param $orderBy Debe ser una cadena con el nombre de una columna en la base de datos.
	  * @param $orden 'ASC' o 'DESC' el default es 'ASC'
	  **/
	public static final function byRange( $Auth_TokensA , $Auth_TokensB , $orderBy = null, $orden = 'ASC')
	{
		$sql = "SELECT * from Auth_Tokens WHERE ("; 
		$val = array();
		if( (($a = $Auth_TokensA->getUserId()) != NULL) & ( ($b = $Auth_TokensB->getUserId()) != NULL) ){
				$sql .= " user_id >= ? AND user_id <= ? AND";
				array_push( $val, min($a,$b)); 
				array_push( $val, max($a,$b)); 
		}elseif( $a || $b ){
			$sql .= " user_id = ? AND"; 
			$a = $a == NULL ? $b : $a;
			array_push( $val, $a);
			
		}

		if( (($a = $Auth_TokensA->getToken()) != NULL) & ( ($b = $Auth_TokensB->getToken()) != NULL) ){
				$sql .= " token >= ? AND token <= ? AND";
				array_push( $val, min($a,$b)); 
				array_push( $val, max($a,$b)); 
		}elseif( $a || $b ){
			$sql .= " token = ? AND"; 
			$a = $a == NULL ? $b : $a;
			array_push( $val, $a);
			
		}

		$sql = substr($sql, 0, -3) . " )";
		if( $orderBy !== null ){
		    $sql .= " order by " . $orderBy . " " . $orden ;
		
		}
		global $conn;
		$rs = $conn->Execute($sql, $val);
		$ar = array();
		foreach ($rs as $foo) {
    		array_push( $ar, new AuthTokens($foo));
		}
		return $ar;
	}


	/**
	  *	Eliminar registros.
	  *	
	  * Este metodo eliminara la informacion de base de datos identificados por la clave primaria
	  * en el objeto AuthTokens suministrado. Una vez que se ha suprimido un objeto, este no 
	  * puede ser restaurado llamando a save(). save() al ver que este es un objeto vacio, creara una nueva fila 
	  * pero el objeto resultante tendra una clave primaria diferente de la que estaba en el objeto eliminado. 
	  * Si no puede encontrar eliminar fila coincidente a eliminar, Exception sera lanzada.
	  *	
	  *	@throws Exception Se arroja cuando el objeto no tiene definidas sus llaves primarias.
	  *	@return int El numero de filas afectadas.
	  * @param AuthTokens [$Auth_Tokens] El objeto de tipo AuthTokens a eliminar
	  **/
	public static final function delete( &$Auth_Tokens )
	{
		if(self::getByPK($Auth_Tokens->getToken()) === NULL) throw new Exception('Campo no encontrado.');
		$sql = "DELETE FROM Auth_Tokens WHERE  token = ?;";
		$params = array( $Auth_Tokens->getToken() );
		global $conn;

		$conn->Execute($sql, $params);
		return $conn->Affected_Rows();
	}


}
