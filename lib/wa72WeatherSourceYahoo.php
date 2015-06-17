<?php
class wa72WeatherSourceYahoo {
	static private $baseurl = 'http://weather.yahooapis.com/forecastrss?u=c&w=';
	static public $proxy;
	static public $default_location = '20066864';

	static public $icon_url = 'http://l.yimg.com/a/lib/ywc/img/wicons.png';

	/**
	 * Get weather data for a location given by $yahoo_location_code
	 * @param string $yahoo_location_code
	 * @return wa72WeatherData
	 */
	static public function getWeatherData($yahoo_location_code = null) {
		if ($yahoo_location_code == false) $yahoo_location_code = self::$default_location;
		$url = self::$baseurl . $yahoo_location_code;
		// TODO: Caching!
		try {
			$w = self::parseRss($url);
		} catch (Exception $e) {
			// TODO: log error somewhere, needs logger
			$w = null;
		}
		return $w;
	}
	/**
	 * Function for parsing Yahoo Wheater RSS Feed
	 * @param string $url
	 * @return wa72WeatherData
	 * @throws Exception
	 */
	static private function parseRss($url) {
		$wd = new wa72WeatherData;
		if (self::$proxy) {
			$context = stream_context_create(array(
				'http' => array(
					'proxy' => 'tcp://' . self::$proxy
				)
			));
			$rss = @file_get_contents($url, false, $context);
		}  else {
			$rss = @file_get_contents($url);
		}

		if ($rss == '') {
			throw new Exception('Could not fetch RSS feed from Yahoo! Weather');
		}
		$xml = @simplexml_load_string($rss, 'SimpleXMLElement', LIBXML_NOCDATA);
		if (!$xml instanceof SimpleXMLElement) {
			throw new Exception('Error parsing RSS feed from Yahoo! Weather');
		}
		$namespaces = $xml->getNamespaces(true);
		//Register them with their prefixes
		foreach ($namespaces as $prefix => $ns) {
			$xml->registerXPathNamespace($prefix, $ns);
		}
		$channel = $xml->xpath('//channel');
		if (is_array($channel)) $channel = $channel[0];
		$units = $channel->children('http://xml.weather.yahoo.com/ns/rss/1.0')->units->attributes();
		$wind = $channel->children('http://xml.weather.yahoo.com/ns/rss/1.0')->wind->attributes();
		$atmosphere = $channel->children('http://xml.weather.yahoo.com/ns/rss/1.0')->atmosphere->attributes();
		$astronomy = $channel->children('http://xml.weather.yahoo.com/ns/rss/1.0')->astronomy->attributes();
		$item = $xml->xpath('//item');
		if (is_array($item)) $item = $item[0];
		$desc = (string) $item->description[0];
		$weatherdata = $item->children('http://xml.weather.yahoo.com/ns/rss/1.0')->condition->attributes();
		$wd->temp = (string) $weatherdata['temp'];
		$wd->timestamp = strtotime((string)$weatherdata['date']);
		$wd->code = (string) $weatherdata['code'];
		$wd->text = (string) $weatherdata['text'];
		$wd->wind_chill = (string) $wind['chill'];
		$wd->wind_direction = (string) $wind['direction'];
		$wd->wind_speed = (string) $wind['speed'];
		$wd->humidity = (string) $atmosphere['humidity'];
		$wd->pressure = (string) $atmosphere['pressure'];
		$wd->rising = (string) $atmosphere['rising'];
		$wd->visibility = (string) $atmosphere['visibility'];
		$wd->ts_sunrise = strtotime((string)$astronomy['sunrise']);
		$wd->ts_sunset = strtotime((string)$astronomy['sunset']);
		$m = array();
		if (preg_match('/<img src="(.*?)"/', $desc, $m)) {
			$wd->image = $m[1];
		}
		$ye = $item->children('http://xml.weather.yahoo.com/ns/rss/1.0');
		foreach ($ye->forecast as $forecast) {
			$a = $forecast->attributes();
			$fc = new wa72WeatherForecast();
			$fc->timestamp = strtotime((string)$a['date']);
			$fc->code = (string) $a['code'];
			$fc->high = (string) $a['high'];
			$fc->low = (string) $a['low'];
			$fc->text = (string) $a['text'];
			$wd->forecasts[] = $fc;
		}
		return $wd;
	}

	static public function getIconCssCode($statuscode) {
		if ($statuscode >= 0 && $statuscode <= 47) {
			$pos = $statuscode  * 61;
		} else {
			$pos = 2684;
		}
		return 'background-image: url(' . self::$icon_url . '); width: 61px; height: 34px; overflow: hidden; background-position: -' . $pos . 'px 0px;';
	}
}