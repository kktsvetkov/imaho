<?php
/**
*/

//////////

/**
*/
class imaho_uploadhouse extends imaho {

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
 
 		self::http(array(
			'url' => 'http://www.uploadhouse.com/index.php',
			));
 
		$x = self::http(array(
			'method' => 'POST', 
			'url' => 'http://www.uploadhouse.com/index.php',
			'post_data' => array(
				'uploadfilemain[0]' => self::f($filename),
				'MAX_FILE_SIZE' => '40000000',
				'action' => 'upload',
				'resize' => '',
				'categoryid' => '10',
				'subcategory' => '',
				'word1' => '',
				'word2' => '',
				'word3' => '',
				'passwordyn' => 'no',
				'password' => '',
				'upload1' => 'Upload Images Now!', 
				),
			));

		// redirect to the next page
		//
		$redirects = 0;
		while (preg_match('~\'(processing\.php\?PHPSESSID=\w+)\'~Uis', $x[1], $R)) {

			$x = self::http(array(
				'url' => 'http://www.uploadhouse.com/' . $R[1],
				'referrer' => 'http://www.uploadhouse.com/index.php',
				));

			if (++$redirects >= 100) {
				throw new Exception(
					'Cannot find the uploaded file', 
					self::ERROR_CANNOT_FIND
					);
				}
			usleep(750);
			}

		if (!preg_match('~Direct link for layouts~', $x[1])) {
			throw new Exception(
				'Cannot locate the uploaded file', 
				self::ERROR_CANNOT_FIND
				);
			}

		preg_match('~Direct link for layouts\)\</strong\>\<br /\>\s*\<input[^>]* value\="\[img\](http\://\w+\.uploadhouse\.com/fileuploads/\d+/.+)\[/img\]"~Uis', $x[1], $R);

		if ($R[1]) {
			return $R[1];
			}
		
		throw new Exception('Cannot upload the file.', self::ERROR_CANNOT_FIND);	
		}

/*
        <tr>
	        <td style="padding:10px; vertical-align:middle;background-color:#cecece;"><a href="viewfile.php?id=16893043"><img src="/fileuploads/16893/16893043-100x100-1db746886ff29e4863f2ff0fbd7c8ae6.jpg" border="0" alt="" /></a></td>
                <td style="vertical-align:top;padding:10px;background-color:#cecece;">
                       <strong>(Thumb Preview) **MOST POPULAR** To post on a message board using BB Code, copy and paste this code:</strong><br />
                        <input size="82" onclick="this.select()" value="[url=http://www.uploadhouse.com/viewfile.php?id=16893043&showlnk=0][img]http://img1.uploadhouse.com/fileuploads/16893/16893043-holder-1db746886ff29e4863f2ff0fbd7c8ae6.jpg[/img][/url]" type="text"><br /><br />
                        <strong>(Thumb Preview) To post on a website that does NOT use BB Code, copy and paste the following code:</strong><br />
                        <input size="82" onclick="this.select()" value='<a href="http://www.uploadhouse.com/viewfile.php?id=16893043&showlnk=0" target="_blank"><img src="http://img1.uploadhouse.com/fileuploads/16893/16893043-holder-1db746886ff29e4863f2ff0fbd7c8ae6.jpg" alt="Image Hosted by UploadHouse.com" border="0" /></a>' type="text"><br /><br />
                        <strong>(View URL) To send this image to friends and family, copy and paste this code:</strong><br />
                        <input size="82" onclick="this.select()" value="http://www.uploadhouse.com/viewfile.php?id=16893043" type="text"><br /><br />
                        <strong>(Image URL) To embed this image into webpages or forums, copy and paste this code:</strong><br />
                        <input size="82" onclick="this.select()" value="http://img2.uploadhouse.com/fileuploads/16893/168930431db746886ff29e4863f2ff0fbd7c8ae6.jpg" type="text"><br /><br />
                        <strong>(BB Code) To insert this image in a message board post copy and paste the following code:</strong><br />
                        <input size="82" onclick="this.select()" value="[url=http://www.uploadhouse.com/viewfile.php?id=16893043&showlnk=0][img]http://img3.uploadhouse.com/fileuploads/16893/168930431db746886ff29e4863f2ff0fbd7c8ae6.jpg[/img][/url]" type="text"><br /><br />
                        <strong>(HTML Code) To insert this image using HTML, copy and paste the following code:</strong><br />
                        <textarea onclick="this.select()" cols="62" rows="3"><a href="http://www.uploadhouse.com/viewfile.php?id=16893043&showlnk=0" target="_blank"><img alt="Image Hosted by UploadHouse.com" src="http://img5.uploadhouse.com/fileuploads/16893/168930431db746886ff29e4863f2ff0fbd7c8ae6.jpg" border="0"></a></textarea><br />
                        <strong>(Direct link for layouts)</strong><br />
                        <input size="82" onclick="this.select()" value="[img]http://img6.uploadhouse.com/fileuploads/16893/168930431db746886ff29e4863f2ff0fbd7c8ae6.jpg[/img]" type="text"><br /><br />
                </td>
        </tr>
*/

	////--end-of-class----
	}