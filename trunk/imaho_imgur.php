<?php
/**
*/

//////////

/**
*/
class imaho_imgur extends imaho {

	Const SUPPORTS_TITLE = true;
	Const SUPPORTS_CAPTION = true;

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
	public function put($url, array $options = null) {
		
		$options += array(
			'title' => '',
			'capion' => '',
			);
		
		$x = self::http(array(
			'method' => 'POST', 
			'url' => 'http://api.imgur.com/2/upload.json',
			'post_data' => array(
				'key' => $this->_settings['key'],
				'image' => $url,
				'type' => 'url',
				'name' => basename($url),
				'title' => $options['title'],
				'caption' => $options['caption'],
				),
			));
 	
		$r = json_decode($x[1], true);
		return $r['upload']['links']['original'];
		}
		
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
	public function upload($filename, array $options = null) {

		$options += array(
			'title' => '',
			'capion' => '',
			);
 
		$x = self::http(array(
			'method' => 'POST', 
			'url' => 'http://api.imgur.com/2/upload.json',
			'post_data' => array(
				'key' => $this->_settings['key'],
				'image' => '@' . realpath($filename),
				'type' => 'image',
				
				//'image' => base64_encode(file_get_contents($filename)),
				//'type' => 'base64',
				
				'name' => basename($url),
				'title' => $options['title'],
				'caption' => $options['caption'],
				),
			));
 
		$r = json_decode($x[1], true);
		return $r['upload']['links']['original'];
		}

/* stdClass Object
(
    [upload] => stdClass Object
        (
            [image] => stdClass Object
                (
                    [name] => 175515_435591043170627_325655553_o.jpg?dl=1
                    [title] => Rousse, Bulgaria
                    [caption] => Rousse: an aerial look
                    [hash] => tuKfV
                    [deletehash] => 4f98dSdvlXdWE8z
                    [datetime] => 2012-11-11 21:13:54
                    [type] => image/jpeg
                    [animated] => false
                    [width] => 2048
                    [height] => 1536
                    [size] => 385237
                    [views] => 0
                    [bandwidth] => 0
                )

            [links] => stdClass Object
                (
                    [original] => http://i.imgur.com/tuKfV.jpg
                    [imgur_page] => http://imgur.com/tuKfV
                    [delete_page] => http://imgur.com/delete/4f98dSdvlXdWE8z
                    [small_square] => http://i.imgur.com/tuKfVs.jpg
                    [large_thumbnail] => http://i.imgur.com/tuKfVl.jpg
                )

        )

) */

	////--end-of-class----
	}