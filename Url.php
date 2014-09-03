<?php
/**
 * Url Parser & Builder
 * @version  1.0.0
 * @author idollo <stone58@qq.com>
 * 
 * @properties
 * protocol
 * auth
 * hostname
 * port
 * path
 * query
 * queryString [get only]
 * hash
 * url [get]
 */
class Url {
	public $protocol;
	public $auth;
	public $hostname;
	public $port;
	public $path;
	public $hash;
	private $_query;

	/**
	 * [__construct description]
	 * @param string $url [description]
	 */
	public function __construct($url=""){
		$this->protocol = "http"; // default as http
		$this->query = "";
		if($url){
			$url = self::parse($url);
			$vars = get_class_vars(__CLASS__);
			foreach ($vars as $k) {
				$this->{$k} = $url->{$k};
			}
			$this->query = "".$url->query;
		}
	}

	/**
	 * [parse description]
	 * @param  [type] $url [description]
	 * @return [type]      [description]
	 */
	public static function parse($url){
		$reg = "/^((\w+):\/\/)?((.*?)@)?([^\/:]*)?(:(\d+))?(\/[^\?]*)?(\?(.*?))?(#.*)?$/";
		if(preg_match($reg, $url, $m)){

			$url = new self;
			$url->protocol = $m[2]?$m[2]:$url->protocol;
			$url->auth = $m[4];
			$url->hostname = $m[5];
			$url->port = isset($m[7])?$m[7]:"";
			$url->path = isset($m[8])?$m[8]:"";
			$url->hash = isset($m[11])?$m[11]:"";

			$url->_query = new UrlQuery( isset($m[10])?$m[10]:"" );
			return $url;
		}
		return null;
	}

	/**
	 * Getter
	 */
	public function __get($n){
		switch(strtolower($n)){
			// url string
			case "url": return $this->__toString();
			// query 
			case "query": return $this->_query;
			case "queryString": return $this->query->toString();

			// auth
			case "_auth":
				$auth = $this->auth;
				if(!$auth) return array("","");
				$auth = explode(":",$auth);
				return array($auth[0], isset($auth[1])?$auth[1]:"");

			case "user": return $this->_auth[0];
			case "password": return $this->_auth[1];
		}
	}

	/**
	 * Setter
	 */
	public function __set($k,$v){
		switch(strtolower($k)){
			case "query":
				$this->_query = new UrlQuery($v); break;
			case "user":
				$this->auth = $v.":".$this->password; break;
			case "password":
				$this->auth = $this->user.":".$v; break;
		}
	}

	/**
	 * appendQuery
	 *
	 * it works as these follows:
	 * appendQuery("id=123");
	 * appendQuery("id", 123);
	 * appendQuery("id=123&acion=save");
	 * appendQuery(array("id"=>123, "action"=>save));
	 * 
	 * @return [void]
	 */
	public function appendQuery(){
		$args = func_get_args();
		$qs = count($args)>1 ? "{$args[0]}={$args[1]}" : $args[0];
		$this->query->extend($qs);
	}

	/**
	 * join url with components
	 * @return [string] $url;
	 */
	public static function join(){
		$parts = func_get_args();
		$url = new self;

		$base = array_splice($parts, 0, 1);
		$base = self::parse($base[0]);

		$url->protocol = $base->protocol;
		$url->hostname = $base->hostname;
		$url->path = $base->path;
		$url->port = $base->port;

		foreach ($parts as $p) {
			// query slice
			if(is_array($p) || preg_match("/(^\w+=.*?)(&\w+=.*?)?$/", $p, $m)){
				$url->appendQuery($p); continue;
			}

			if(!is_string($p)) continue;
			// port
			if(preg_match("/^:(\d+)$/",$p,$m)){
				$url->port = $m[1]; continue;
			}
			// auth
			if(preg_match("/^@(.*)$/", $p, $m)){
				$url->auth = $m[1]; continue;
			}
			// hash
			if(preg_match("/^(#.*)$/", $p, $m)){
				$url->hash = $m[1]; continue;
			}
			$url->path = self::joinPath($url->path, $p);
		}
		
		return $url->url;
	}


	/**
	 * join url with base host
	 * Warning: it only work for http protocol
	 * @return [type] [description]
	 */
	public static function basejoin(){
		$args = func_get_args();
		array_unshift($args, self::_base()."/");
		return call_user_func_array(array(new self, "join"), $args);
	}

	/**
	 * convert to  absolute url
	 * parse and trim the directory modifier like ../, ./
	 * @return [type] [description]
	 */
	public static function abs(){
		$args = func_get_args();
		array_unshift($args, self::_base(). self::_path() );
		return call_user_func_array(array(new self, "join"), $args);
	}

	/**
	 * [joinPath description]
	 * @return [type] [description]
	 */
	public static function joinPath(){
		$paths = func_get_args();
		$path = array_shift($paths);
		foreach($paths as $p){
			$path = self::_abspath($p, $path);
		}
		return $path;
	}

	/**
	 * Take a base URL, and a href URL, and resolve them as a browser would for an anchor tag. 
	 * @param  [string] $from, the base URL
	 * @param  [string] $to, the href URL
	 * @return [string] the resolved URL
	 */
	public static function resolve($from, $to){
		$from = self::parse($from);
		$from->path = self::_abspath($from->path, $to);
		return $from->url;
	}


	// return current base url;
	protected static function _base(){
		return self::join($_SERVER['HTTP_HOST']);
	}

	// return current path
	protected static function _path(){
		$uri = explode("?",$_SERVER['REQUEST_URI']);
		return $uri[0];
	}

	/**
	 * return dirname of the path
	 * @param [string] $path, an absolute path string;
	 */
	protected static function _parentpath($path){
		return preg_replace("/^\/(.*?)(\/?[^\/]*\/?)$/", "/$1", $path);
	}

	protected static function _abspath($path, $base=""){
		$sep = "/";
		$base = $base ? $base : self::_path();
		$base = self::_parentpath($base);
		// 解决 ./
		$reg = "/^\\.\//";
		$path = preg_replace($reg, $base.$sep, $path);

		// 判断是否绝对路径
		if( $path[0]!="/"){
			$path = $base.$sep.$path;
		}
		$path = preg_replace("/\/{2,}/", $sep, $path );
		// 解决 ../
		if(strstr($path, "../")){
			$paths = explode("../", $path, 2);
			return self::_abspath( self::_parentpath($paths[0]).$sep.$paths[1] );
		}

		return $path;
	}

	/**
	 * [__toString Magic]
	 * @return string url
	 */
	public function __toString(){
		$auth = $this->auth? $this->auth."@":"";
		$port = $this->port? ":".$this->port:"";
		$query = "{$this->query}";
		$query = $query?"?$query":"";
		$protocol = $this->hostname? "{$this->protocol}://":"";
		return "{$protocol}{$auth}{$this->hostname}{$port}{$this->path}{$query}{$this->hash}";
	}
}


class UrlQuery{
	// store query params data;
	private $data;

	/**
	 * UrlQuery Parser
	 * @param [mix] query string or query object
	 */
	public function __construct($data){
		if(is_string($data)){
			$q = self::fromString($data); 
			$data = $q->params();
		}
		$this->data = is_array($data)?$data:array();
	}

	/**
	 * parse from query string
	 */
	public static function fromString($qstring){
		$parts = $qstring?explode("&",$qstring):array();
		$data = array();
		foreach ($parts as $kv) {
			$kv = explode("=",$kv);
			$data[$kv[0]] = isset($kv[1])? urldecode($kv[1]):'';
		}
		return new self($data);
	}

	/**
	 * extend the query data.
	 * @param  [mix] $data , query string or dictionay data;
	 * @return [void]
	 */
	public function extend($data){
		$ext = new self($data);
		$this->data = array_merge( $this->params(), $ext->params() );
	}

	/**
	 * returns params object
	 * @return [dict] $params
	 */
	public function params(){
		return $this->data;
	}

	/**
	 * params Getter, with param names
	 */
	public function __get($n){
		$data = $this->data;
		return isset($data[$n])?$data[$n]:null;
	}

	/**
	 * toString
	 */
	public function toString(){
		return $this->__toString();
	}

	/**
	 * [__toString Magic]
	 * @return string
	 */
	public function __toString(){
		$qs = array();
		foreach ($this->data as $k => $v) {
			$v = urlencode($v);
			$qs[] = "{$k}={$v}";
		}
		return count($qs) ? implode("&",$qs) : "";
	}
}

