<?php
/**
* @package Imago
* @version 0.1
* @license LGPL
* @author Kaloyan K. Tsvetkov <kaloyan@kaloyan.info>
* @link http://kaloyan.info/blog/imago
*/

/////

/**
* Abstract parent class for all Imaho implementations. In addition to the abstract
* definitions it also contains common functionality shared with the rest of the
* adapters.
*/
abstract class imaho {

	/**
	* @var array settings, e.g. API code, login credentials, tmp folder, etc.
	*/
	protected $_settings = array();

	/**
	* Constructor
	* @param array $settings API code, login credentials, tmp folder, etc.
	*/
	public function __construct(array $settings = null) {
		$this->_settings = (array) $settings + array(
			'tmp' => self::tmp(),
			);
		}

	/**
	* Returns the path to the temporary folder
	* @return string
	*/
	private static function tmp() {
		if ($x = ini_get('upload_tmp_dir')) {
			return $x;
			}
		if ($x = ini_get('session.save_path')) {
			return $x;
			}
		if (!empty($_ENV['TEMP'])) {
			return $_ENV['TEMP'];
			}
		if (!empty($_ENV['TMP'])) {
			return $_ENV['TMP'];
			}
		return '/tmp';
		}

	/**
	* Places an HTTP request (GET, POST, whatever)
	* @param array $request details of the actual HTTP request
	* <pre>
	*	- "method" (string)
	*	- "url" (string)
	*	- "port" (integer) optional, by default it is 80
	*	- "referrer" (string) optional, empty by default
	*	- "content_type" (string) optional, empty by default
	*	- "user-agent" (string) optional, by default it is "Imaho"
	*	- "username" (string) optional, empty by default: this is the HTTP Auth username
	*	- "password" (string) optional, empty by default: this is the HTTP Auth password
	*	- "proxy_host" (string) optional, empty by default: address of the proxy server
	*	- "proxy_port" (integer) optional, empty by default: port of the proxy server
	*	- "proxy_username" (string) optional, empty by default: username of the proxy server 
	*	- "proxy_password" (string) optional, empty by default: password of the proxy server 
	*	- "post_data" (string) optional, empty bu default: data for the body of the request
	* </pre>
	* @return array array w/ the HTTP response
	*/
	protected static function http(array $request) {
		
		// put all the default values
		//
		$request += array(
			'method' => 'GET',
			'url' => null,
			'port' => 80,
			'referrer' => null,
			'user-agent' => 'Imaho <imaho@kaloyan.info>',			
			
			'content_type' => null,
			
			'auth_type' => 'basic',
			'auth_username' => null,
			'auth_password' => null,			
			
			'proxy_host' => null,
			'proxy_port' => null,
			'proxy_username' => null,
			'proxy_password' => null,
			
			'post_data' => null,
			);
			
		$result = array();
		
		// no URL ?
		//
		if (!trim($request['url'])) {
			throw new Exception('Empty URL', self::ERROR_EMPTY_URL);
			}
		
		return extension_loaded('curl')
			? self::http_curl($request)
			: self::http_fsockopen($request);
		}

	Const ERROR_EMPTY_URL = 1001;
	Const ERROR_NOT_SUPPORTED = 1002;
	Const ERROR_CANNOT_CONNECT = 1003;
	Const ERROR_FAILED = 1004;
	Const ERROR_CANNOT_FIND = 1005;
	Const ERROR_EMPTY_FILE = 1006;

	/**
	* Do the HTTP request w/ the cURL extension (if available)
	* @param array $request
	* @return array the HTTP response
	* @link http://curl.haxx.se/libcurl/c/libcurl-errors.html
	*/
	private static function http_curl(array $request) {
	
		$ch = curl_init();

		curl_setopt($ch, CURLOPT_URL, $request['url']);
		curl_setopt($ch, CURLOPT_PORT, $request['port']);

		$url = parse_url($request['url']);
		$cookie_txt = self::tmp() . '/' . strToUpper($url['host']) . '.txt';
		curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie_txt);
    	curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie_txt);

		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLINFO_HEADER_OUT, true);
		
		switch (strToUpper($request['method'])) {
			case 'POST' :
				curl_setopt($ch, CURLOPT_POST, true);
				break;
			case 'PUT' :
				curl_setopt($ch, CURLOPT_PUT, true);
				break;
			default:
				curl_setopt($ch, CURLOPT_HTTPGET, true);
				break;
			}
			
		// HTTP Auth ?
		//
		if ($request['auth_username']) {			
			curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
			curl_setopt($ch, CURLOPT_USERPWD,  
					"{$request['auth_username']}:{$request['auth_password']}");
			}
		
		// Proxy ? 
		//
		if ($request['proxy_host']) {
			curl_setopt($ch, CURLOPT_HTTPPROXYTUNNEL, true);
			curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_HTTP);
			curl_setopt($ch, CURLOPT_PROXY, $request['proxy_host']);
			
			if ($request['proxy_port']) {
				curl_setopt($ch, CURLOPT_PROXYPORT, $request['proxy_port']);			
				}
			
			if ($request['proxy_username']) {
				curl_setopt($ch, CURLOPT_PROXYUSERPWD, 
					"{$request['proxy_username']}:{$request['proxy_password']}");			
				}
			}
		
		curl_setopt($ch, CURLOPT_USERAGENT, $request['user-agent']);
		if ($request['referrer']) {
			curl_setopt($ch, CURLOPT_REFERER, $request['referrer']);
			}

		if ($request['content_type']) {
			curl_setopt($ch, CURLOPT_HTTPHEADER, array(
				'Content-type: ' . $request['content_type']
				));
			}

		if ($request['post_data']) {
			curl_setopt($ch, CURLOPT_POSTFIELDS, $request['post_data']);
			}

		$response = curl_exec($ch);

//echo curl_getinfo($ch, CURLINFO_HEADER_OUT);
 
		if (false === $response) {
			throw new Exception(curl_error($ch) . ' (' . curl_errno($ch) . ')', 
				self::ERROR_FAILED);
			}

		$result = array(
				'url' => curl_getinfo($ch, CURLINFO_EFFECTIVE_URL),
				'code' => curl_getinfo($ch, CURLINFO_HTTP_CODE), 
				'content_type' => curl_getinfo($ch, CURLINFO_CONTENT_TYPE), 
				);

		curl_close($ch);	
		
		// the response is an array with two elements, first one is with 
		// details about the response, and the second one is the returned
		// content
		//
		return array(
			$result,
			$response
			);
		}

	/**
	* Do the HTTP request with {@link fsockopen()}
	* @param array $request
	* @return array the HTTP response
	*/
	private static function http_fsockopen(array $request) {
		
		$url = parse_str($request['url']);
		
		if ('http' != $url['scheme']) {
			throw new Exception("The '{$url['scheme']}' is not supported.", self::ERROR_NOT_SUPPORTED);
			}

		if ((!$request['port'] || $request['port'] == 80) && !empty($url['port'])) {
			$request['port'] = $url['port'];
			}
		
		// proxy connect ?
		//
		;
		;
		;
		;
		
		// connect
		//
		if (!$fp = fsockopen($url['host'], $request['port'])) {
			throw new Exception(
				"Cannot connect to '{$url['host']}' on port {$request['port']}", 
				self::ERROR_CANNOT_CONNECT
				);
			}
		
		$r = strToUpper($request['method']) 
			. " {$url['path']}{$url['query']} HTTP/1.1\r\n"
			. "Host: {$url['host']}\r\n" 
			. "Connection: Close\r\n";
		
		// http auth ?
		//
		if (!$request['auth_username'] && !empty($url['user'])) {
			$request['auth_username'] = $url['user'];
			}
			
		if (!$request['auth_password'] && !empty($url['pass'])) {
			$request['auth_password'] = $url['pass'];
			}
		;
		;
		;
		;
			
		// referrer
		//
		if ($request['referrer']) {
			$r .= "Referrer: {$request['referrer']}\r\n";
			}
		
		// user-agent ?
		//
		if ($request['user-agent']) {
			$r .= "User-Agent: {$request['user-agent']}\r\n";
			}
		
		if ($x = trim($request['post_data'])) {
			$request['post_data'] = $x;
			$r .= "Content-Length: " . strlen($x) . "\r\n";
			}	
		
		$r .= "\r\n";
		
		if ($request['post_data']) {
			$r .= (!is_scalar($request['post_data'])
					? http_build_query((array) $request['post_data'])
					: $request['post_data'] ) 
				. "\r\n";
			}
		
		fwrite($fp, $r);
		
		$response = '';
		while (!feof($fp)) {
        	$response .= fgets($fp, 128);
    		}
		
		fclose($fp);

		return explode("\r\n\r\n", $response);
		}

	/**
	* Shortcut method for emulating {@link imaho::put()}
	* @param string $url
	* @param array $options
	* <pre>
	*	- "title" of the image
	*	- "caption" of the image
	* </pre>
	* @return string the URL to the uploaded image
	*/
	final protected function put_pseudo($url, array $options = null) {
		
		$f = self::http(array(
			'url' => $url
			));
 
 		if (!$f[1] || ($f[0]['code'] != 200)) {
			throw new Exception('Cannot find the URL', self::ERROR_CANNOT_FIND);
			}

		$t = tempnam($this->_settings['tmp'], 'imaho');
		file_put_contents($t, $f[1]);
		
		$r = $this->upload($t, $options);
		
		unlink($t);
		return $r;
		}

	/**
	* Shortcut method for formatting correctly files for upload
	* @param string $filename
	* @return string
	*/
	final protected function f($filename) {
		
		if (!$f = realpath($filename)) {
			throw new Exception('Cannot find the file to upload', self::ERROR_EMPTY_FILE);
			}
		
		$m = getImageSize($f);
		return '@' . $f . (
			!empty($m['mime'])
				? ";type={$m['mime']}"
				: ''
			);
		}

	/* support contants -=- -=- -=- -=- -=- -=- -=- -=- -=- -=- -=- -=- -=- -=- */

	/** Whether supports sending title for the photo */
	Const SUPPORTS_TITLE = false;
	
	/** Whether supports sending caption for the photo */
	Const SUPPORTS_CAPTION = false;

	/* abstract methods -=- -=- -=- -=- -=- -=- -=- -=- -=- -=- -=- -=- -=- -=- */
	
	/**
	* Directly put an image from an URL
	* @param string $url
	* @param array $options
	* <pre>
	*	- "title" of the image
	*	- "caption" of the image
	* </pre>
	* @return string the URL to the uploaded image
	*/
	abstract public function put($url, array $options = null);
		
	/**
	* Upload an image from a local filename
	* @param string $filename
	* @param array $options
	* <pre>
	*	- "title" of the image
	*	- "caption" of the image
	* </pre>
	* @return string the URL to the uploaded image
	*/
	abstract public function upload($filename, array $options = null);

	////--end-of-class----
	}