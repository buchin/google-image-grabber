<?php

namespace Buchin\GoogleImageGrabber;

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
            if (is_array($value)) {
                foreach ($value as $vk => $vv) {
                    $return[] = $vv;
                }
            } else {
                $return[] = $value;
            }
        }

        return $return;
    }

    public static function array_flatten($array)
    {
        $return = [];
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $return = array_merge($return, self::array_flatten($value));
            } else {
                $return[$key] = $value;
            }
        }
        return $return;
    }

    public static function filterResult($array, &$result)
    {
        $array = array_filter($array);

        foreach ($array as $key => $value) {
            $data = [];

            if (filter_var($value, FILTER_VALIDATE_URL)) {
                $result[] = array_filter(self::array_flatten($array));
            }

            if (is_string($value)) {
                $result[] = $value;
            }

            if (is_array($value)) {
                self::filterResult($value, $result);
            }
        }
    }


    public static function getSearchURLParams($queryParams = [])
    {
        $paramsAux = [
            'q' => '',
            'source' => 'lnms',
            'tbm' => 'isch',
            'tbs' => [],
        ];
        if (!is_array($queryParams)) {
            $paramsAux['q'] = strval($queryParams);
            $queryParams = [];
        }
        $sizes = [
            'l' => ['large', 'big', 'l'], // isz:l
            'm' => ['medium', 'm'], // isz:m
            'i' => ['icon', 'i', 's', 'small']   // isz:i
        ];
        $licenses = [
           'ol' => ['commercial_license', 'commercial', 'ol'], // il:ol
           'cl' => ['creative_commons', 'cc', 'cl'] //'il:cl',
        ];
        foreach ($queryParams as $k => $v) {
            if (in_array($k, ['keyword', 'search'])) {
                $paramsAux['q'] = $v;
                continue;
            }
            if (in_array($k, ['lang', 'language', 'locate', 'loc'])) {
                $paramsAux['hl'] = $v;
                continue;
            }
            if ($k === 'size') {
                foreach ($sizes as $s_key => $s_viables_keys) {
                    if (in_array($v, $s_viables_keys)) {
                        $paramsAux['tbs'][] = 'isz:' . $s_key;
                        break;
                    }
                }
                continue;
            }
            if ($k === 'license') {
                foreach($licenses as $l_key => $l_viables_keys) {
                    if (in_array($v, $l_viables_keys)) {
                        $paramsAux['tbs'][] = 'il:' . $l_key;
                        break;
                    }
                }
                continue;
            }
            $paramsAux[$k] = $v;
        }
        $paramsAux['tbs'] = implode(',', $paramsAux['tbs']);
        return $paramsAux;
    }

    public static function grab($keyword, $proxy = "", $options = [])
    {
        $urlParams = static::getSearchURLParams($keyword);
        $url = "https://www.google.com/search?" . http_build_query($urlParams);

        $uas = [
            "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/88.0.4324.182 Safari/537.36 Edg/88.0.705.68",
            "Mozilla/5.0 (Windows NT 10.0; WOW64; Trident/7.0; rv:11.0) like Gecko",
            "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/88.0.4324.182 Safari/537.36",
            "Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/88.0.4324.182 Safari/537.36",
            "Mozilla/5.0 (Windows NT 10.0) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/88.0.4324.182 Safari/537.36",
            "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:85.0) Gecko/20100101 Firefox/85.0",
            "Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/88.0.4324.182 Safari/537.36 Vivaldi/3.6",
            "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/88.0.4324.182 Safari/537.36 Vivaldi/3.6",
            "Mozilla/5.0 (Macintosh; Intel Mac OS X 11_2_1) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/14.0.2 Safari/605.1.15",
            "Mozilla/5.0 (Macintosh; Intel Mac OS X 11.2; rv:85.0) Gecko/20100101 Firefox/85.0",
            "Mozilla/5.0 (Macintosh; Intel Mac OS X 11_2_1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/88.0.4324.182 Safari/537.36",
            "Mozilla/5.0 (Macintosh; Intel Mac OS X 11_2_1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/88.0.4324.182 Safari/537.36 Vivaldi/3.6",
            "Mozilla/5.0 (Macintosh; Intel Mac OS X 11_2_1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/88.0.4324.182 Safari/537.36 Edg/88.0.705.63",
        ];

        $ua = $uas[array_rand($uas)];
        if (!empty($proxy)) {
            $proxy = "tcp://$proxy";
        }

        $options = [
            "http" => [
                "method" => "GET",
                "proxy" => "$proxy",
                "user_agent" => $ua,
            ],
            "ssl" => [
                "verify_peer" => false,
                "verify_peer_name" => false,
            ],
        ];

        $context = stream_context_create($options);

        $response = file_get_contents($url, false, $context);

        $re =
            '/AF_initDataCallback\({key: \'ds:1\', hash: \'\d\', data:(.*), sideChannel: {}}\);<\/script>/m';
        preg_match_all($re, $response, $matches);

        $data = isset($matches[1][0]) ? json_decode($matches[1][0], true) : [];

        $rawResults = [];
        $results = [];

        // Old (sometime works and some times not)
        if (!empty($data[31])) {

            if (isset($data[31][0][12][2])) {
                $rawResults = $data[31][0][12][2];
            }

        // New
        } else {

            if (isset($data[56][1][0][0][1][0])) {
                $rawResults = $data[56][1][0][0][1][0];
            }
        }

        foreach ($rawResults as $key => $rawResult) {

            $result = [];

            self::filterResult($rawResult, $result);
            $data = self::getValues($result);

            $result = [];

            if (count($data) >= 11) {
                $result["keyword"] = $urlParams['q'];
                $result["slug"] = __::slug($urlParams['q']);
                $result["title"] = isset($data[13])
                    ? ucwords(__::slug(strval($data[13]), ["delimiter" => " "]))
                    : "";
                $result["alt"] = isset($data[19])
                    ? __::slug(strval($data[19]), ["delimiter" => " "])
                    : "";

                $result["url"] = $data[8];
                $result["filetype"] = self::getFileType($data[8]);
                $result["width"] = $data[6];
                $result["height"] = $data[7];
                $result["source"] = isset($data[12]) ? $data[12] : "";
                $result["domain"] = isset($data[20]) ? $data[20] : "";

                $result["thumbnail"] = isset($data[26]) ? $data[26] : $data[1];

                if (strpos($result["url"], "http") !== false) {
                    $results[] = $result;
                }

            }

        }

        return $results;
    }

    public static function getFileType($url)
    {
        $url = strtolower($url);
        $types = [
            'bmp', 'eps', 'gif', 'heif', 'indd',
            'png', 'svg', 'tiff', 'psd', 'raw', 'webp'
        ];
        foreach ($types as $t) {
            if (strpos($url, '.' . $t) > 1){
                return $t;
            }
        }
        return "jpg";
    }
}
