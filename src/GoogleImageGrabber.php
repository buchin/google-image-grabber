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

    public static function grab($keyword, $proxy = "", $options = [])
    {
        $url =
            "https://www.google.com/search?q=" .
            urlencode($keyword) .
            "&source=lnms&tbm=isch&tbs=";

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

        $exploded = explode(
            "AF_initDataCallback({key: 'ds:1', isError:  false , hash: '2', data:",
            $response
        );

        $data = isset($exploded[1]) ? $exploded[1] : "";

        $data = explode(", sideChannel: {}});</script>", $data);
        $data = $data[0];

        $data = json_decode($data, true);

        $rawResults = [];
        $results = [];

        if (isset($data[31][0][12][2])) {
            $rawResults = $data[31][0][12][2];
        }

        foreach ($rawResults as $rawResult) {
            $result = [];

            self::filterResult($rawResult, $result);
            $data = self::getValues($result);

            $result = [];

            if (count($data) >= 11) {
                $result["keyword"] = $keyword;
                $result["slug"] = __::slug($keyword);

                $result["title"] = isset($data[13])
                    ? ucwords(__::slug($data[13], ["delimiter" => " "]))
                    : "";
                $result["alt"] = isset($data[19])
                    ? __::slug($data[19], ["delimiter" => " "])
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

                $results[] = $result;
            }
        }

        return $results;
    }

    public static function getFileType($url)
    {
        $url = strtolower($url);

        switch ($url) {
            case strpos($url, ".jpg") || strpos($url, ".jpeg"):
                return "jpg";
                break;

            case strpos($url, ".png"):
                return "png";
                break;

            case strpos($url, ".bmp"):
                return "bmp";
                break;

            case strpos($url, ".gif"):
                return "gif";
                break;

            default:
                return "jpg";
                break;
        }
    }
}
