<?php
/**
*/

//////////

/**
*/
class imaho_imgjoe extends imaho {

	Const SUPPORTS_TITLE = false;
	Const SUPPORTS_CAPTION = false;

	/**
	* Directly put an image from an URL
	* @param string $url
	* @param array $options
	* <pre>
	*	- "title" of the image
	*	- "caption" of the image
	* </pre>
	* @return array with two elements, first one is the URL, second is the output
	*/
	public function put($url, array $options = null) {
	
		$x = self::http(array(
			'method' => 'POST', 
			'url' => 'http://www.imgjoe.com/',
			'post_data' => array(
				'remota' => $url,
				'fileup' => null,
				'resize' => null,
				'x' => 32,
				'y' => 43
				),
			));

		preg_match('~\<div class="ctninput"\>\s*\<div class="codex"\>\<a href="([^>"]+)" target="_blank"\>URL\:\</a\>~Uis', $x[1], $R);

		if ($R[1]) {
			return $R[1];
			}
		
		throw new Exception(
			'Cannot locate the uploaded file.', 
			self::ERROR_CANNOT_FIND);	
		}
		
	/**
	* Upload an image from a local filename
	* @param string $filename
	* @param array $options
	* <pre>
	*	- "title" of the image
	*	- "caption" of the image
	* </pre>
	* @return array with two elements, first one is the URL, second is the output
	*/
	public function upload($filename, array $options = null) {
 
		$x = self::http(array(
			'method' => 'POST', 
			'url' => 'http://www.imgjoe.com/',
			'post_data' => array(
				'fileup' => self::f($filename),
				'remota' => null,
				'resize' => null,
				'x' => 32,
				'y' => 43
				),
			));

		preg_match('~\<div class="ctninput"\>\s*\<div class="codex"\>\<a href="([^>"]+)" target="_blank"\>URL\:\</a\>~Uis', $x[1], $R);

		if ($R[1]) {
			return $R[1];
			}
		
		throw new Exception(
			'Cannot locate the uploaded file.', 
			self::ERROR_CANNOT_FIND);	
		}

/*
            <div class="ctninput">
                <div class="codex"><a href="http://www.imgjoe.com/x/201009221500.jpg" target="_blank">URL:</a></div>
                <div class="inputshare"><input tabindex="5"value="http://www.imgjoe.com/x/201009221500.jpg" onclick="this.focus();this.select();" /></div>
            </div>

*/

	////--end-of-class----
	}