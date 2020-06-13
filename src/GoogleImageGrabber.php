<?php namespace Buchin\GoogleImageGrabber;

use PHPHtmlParser\Dom;
use __;
/**
* 
*/
class GoogleImageGrabber
{	
	public static function getValues($array)
	{
		$return = [];
		foreach ($array as $key => $value) {
			if(is_array($value)){
				foreach ($value as $vk => $vv) {
					$return[] = $vv;
				}
			}

			else{
				$return[] = $value;
			}
		}

		return $return;
	}

	public static function array_flatten($array) {

	   $return = array();
	   foreach ($array as $key => $value) {
	       if (is_array($value)){ $return = array_merge($return, self::array_flatten($value));}
	       else {$return[$key] = $value;}
	   }
	   return $return;
	}

	public static function filterResult($array, &$result)
	{
		$array = array_filter($array);

		foreach ($array as $key => $value) {
			$data = [];

			if(filter_var($value, FILTER_VALIDATE_URL)){
				$result[] = array_filter(self::array_flatten($array));
			}


			if(is_string($value)){
				$result[] = $value;
			}

			
			if(is_array($value)){
				self::filterResult($value, $result);
			}
		}
	}

	public static function grab($keyword, $options = [])
	{
		$url = "https://www.google.com/search?q=" . urlencode($keyword) . "&source=lnms&tbm=isch&tbs=";

		$ua = \Campo\UserAgent::random([
		    'os_type' => ['Windows', 'OS X'],
		    'device_type' => 'Desktop'
		]);

		$options  = [
			'http' => [
				'method'     =>"GET",
				'user_agent' =>  $ua,
			],
			'ssl' => [
				"verify_peer"      => FALSE,
				"verify_peer_name" => FALSE,
			],
		];

		$context  = stream_context_create($options);

		$response = file_get_contents($url, FALSE, $context);

		$data = explode("AF_initDataCallback({key: 'ds:1', isError:  false , hash: '2', data:", $response);

		$data = isset($data[1]) ? $data[1] : '';
		$data = explode('});</script>', $data);
		$data = $data[0];

		$data = json_decode($data, true);


		$rawResults = [];
		$results = [];

		if(isset($data[31][0][12][2])){
			$rawResults = $data[31][0][12][2];
		}

		foreach ($rawResults as $rawResult) {
			$result = [];

			self::filterResult($rawResult, $result);
			$data = self::getValues($result);


			$result = [];

			if(count($data) >= 11){
			    $result['keyword'] = $keyword;
			    $result['slug'] = __::slug($keyword);

			    $result['title'] = ucwords(__::slug($data[13], ['delimiter' => ' ']));
			    $result['alt'] = __::slug($data[19], ['delimiter' => ' ']);
			    
			    $result['url'] = $data[8];
			    $result['filetype'] = self::getFileType($data[8]);
			    $result['width'] = $data[6];
			    $result['height'] = $data[7];
			    $result['source'] = $data[12];
			    $result['domain'] = $data[20];

				$results[] = $result;
			}

		}

		return $results;
	}

	public static function getFileType($url)
	{
		$url = strtolower($url);

		switch ($url) {
			case strpos($url, '.jpg') || strpos($url, '.jpeg'):
				return 'jpg';
				break;

			case strpos($url, '.png'):
				return 'png';
				break;

			case strpos($url, '.bmp'):
				return 'bmp';
				break;

			case strpos($url, '.gif'):
				return 'gif';
				break;
			
			default:
				return 'jpg';
				break;
		}
	}
}