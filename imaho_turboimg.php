<?php
/**
*/

//////////

/**
*/
class imaho_turboimg extends imaho {

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
 
		$x = self::http(array(
			'method' => 'POST', 
			'url' => 'http://turboimg.com/',
			'post_data' => array(
				'userfile' => self::f($filename),
				'upload' => '+',
				),
			));

		if (!preg_match('~\<form name\="links" class\="formlinks"\>~', $x[1])) {
			throw new Exception('Cannot upload the file.', self::ERROR_CANNOT_FIND);
			}

		preg_match('~\<p\>Direct Link\:\</p\>\s*\<input[^>]+value\=\"(http://turboimg.com/p/.+)\"~Uis', $x[1], $R);

		if ($R[1]) {
			return $R[1];
			}
		
		throw new Exception('Cannot upload the file.', self::ERROR_CANNOT_FIND);	
		}

/*
  <form name="links" class="formlinks">
	<div class="spacer">
  	<p>Forum Link 1:</p>
    <input style="clear:none; width: 187px;" type="text" name="textfield" class="textfield" onClick='highlight(this);' value="[URL=http://turboimg.com][IMG]http://turboimg.com/p/wjo1352699686m.jpg[/IMG][/URL]"/>
    </div>
    <div class="spacer">
    <p>Forum Link 2:</p>
    <input style="clear:none; width: 187px;" type="text" name="textfield" class="textfield" onClick='highlight(this);' value="[url=http://turboimg.com][img]http://turboimg.com/p/wjo1352699686m.jpg[/img][/url]"/>
    </div>
    <div class="spacer">
    <p>Web Html:</p>
    <input style="clear:none; width: 187px; margin-left: 14px;" type="text" name="textfield2" class="textfield" onClick='highlight(this);' value='<a href="http://turboimg.com"><img src="http://turboimg.com/p/wjo1352699686m.jpg" border="0" alt=""/><a/>'/>
    </div>
    <div class="spacer">
    <p>Direct Link:</p>
    <input style="clear:none; width: 187px; float: left; margin-left: 9px;" type="text" name="textfield" class="textfield" onClick='highlight(this);' value="http://turboimg.com/p/wjo1352699686m.jpg"/>
    </div>
  </form> 
*/

	////--end-of-class----
	}