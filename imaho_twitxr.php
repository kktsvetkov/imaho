<?php
/**
*/

//////////

/**
*/
class imaho_twitxr extends imaho {

	Const SUPPORTS_TITLE = false;
	Const SUPPORTS_CAPTION = true;

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
		return $this->put_pseudo($url, $options);
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

		$options += array( 
			'capion' => '',
			);
 
		$x = self::http(array(
			'method' => 'POST', 
			'url' => 'http://twitxr.com/api/rest/postUpdate',
			'auth_username' => $this->_settings['username'],
			'auth_password' => md5($this->_settings['password']),
			'post_data' => array(
				'image' => '@' . ($_ = realpath($filename)),
				'text' => md5($_) . ' ' . $options['caption'],
				'place' => 'xxxx', //'New York City, New York, U.S.'
				),
			));
			
		if ('<?xml version="1.0" encoding="UTF-8"?><result code="done" />' != trim($x[1])) {
			$xml = simplexml_load_string($x[1]);
			throw new Exception($xml->error 
					? $xml->error 
					: 'Operation failed.', 
				self::ERROR_FAILED);
			}
			
		// once the message is placed, get it from the timeline
		//
 		$y = self::http(array(
			'method' => 'GET', 
			'url' => 'http://twitxr.com/api/rest/getUserTimeline?user=' 
				. $this->_settings['username'],
			));
			
		$xml = simplexml_load_string($y[1]);
		foreach ($xml->update as $u) {
			list($md5) = explode(' ', $u->text);
			if ($md5 == md5($_)) {
				return preg_replace('~/th$~', '', $u->picture);
				}
			}
			
		throw new Exception('Cannot find the uploaded image', self::ERROR_CANNOT_FIND);
		}

	////--end-of-class----
	}