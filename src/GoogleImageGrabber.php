<?php namespace Buchin\GoogleImageGrabber;

use PHPHtmlParser\Dom;
use __;
/**
* 
*/
class GoogleImageGrabber
{
	
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


		$htmldom = new Dom;
		$htmldom->loadStr($response, []);

		$results = [];
		
		foreach ($htmldom->find('.rg_di > .rg_meta') as $n => $dataset) {

			$jsondata = $dataset->text;
			$data = json_decode($jsondata);

		    $results[$n]['keyword'] = $keyword;
		    $results[$n]['slug'] = __::slug($keyword);

		    $results[$n]['title'] = ucwords(__::slug($data->pt, ['delimiter' => ' ']));
		    $results[$n]['alt'] = __::slug($data->s, ['delimiter' => ' ']);
		    
		    $results[$n]['url'] = $data->ou;
		    $results[$n]['filetype'] = $data->ity;
		    $results[$n]['width'] = $data->ow;
		    $results[$n]['height'] = $data->oh;
		    $results[$n]['source'] = $data->ru;
		}

		return $results;
	}
}