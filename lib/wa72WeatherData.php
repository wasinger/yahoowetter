<?php
class wa72WeatherData {
	public $temp;
	public $code;
	public $timestamp;
	public $text;
	public $image;
	public $wind_speed;
	public $wind_chill;
	public $wind_direction;
	public $ts_sunrise;
	public $ts_sunset;
	public $humidity;
	public $visibility;
	public $pressure;
	public $rising;

	/**
	 * @var wa72WeatherForecast[] array of wa72WeatherForecast objects
	 */
	public $forecasts;

	/**
	 * @var string Classname of class implementing wa72WeatherDataTranslatorInterface
	 */
	public static $TranslatorClass;

	static public function setTranslatorClass($classname) {
		self::$TranslatorClass = $classname;
	}

	static public function tr($string) {
		if (class_exists(self::$TranslatorClass)) {
			return call_user_func(array(self::$TranslatorClass, 'translate'), $string);
		} else return $string;
	}
	/**
	 * get raw temperature value
	 * @return int
	 */
	public function getTemp() {
		return $this->temp;
	}
	/**
	 * get formatted Temperature
	 * @param string $suffix
	 * @return string
	 */
	public function getTemperature($suffix = '°C') {
		return $this->temp . $suffix;
	}
	/**
	 * Get formatted date
	 * @param string $date_format formatting string for php date() function
	 * @return string
	 */
	public function getDatetime($date_format) {
		return date($date_format, $this->timestamp);
	}
	public function getImageUrl() {
		return $this->image;
	}
	public function getWindSpeed() {
		return round(floatval($this->wind_speed));
	}
	public function getWindDirectionDegrees() {
		return $this->wind_direction;
	}
	public function getWindDirectionPointOfCompass() {
		$d = (int) $this->wind_direction;
		if ($d >= 23 && $d < 68) $p = self::tr('NE');
		elseif ($d >= 68 && $d < 113) $p = self::tr('E');
		elseif ($d >= 113 && $d < 158) $p = self::tr('SE');
		elseif ($d >= 158 && $d < 203) $p = self::tr('S');
		elseif ($d >= 203 && $d < 248) $p = self::tr('SW');
		elseif ($d >= 248 && $d < 293) $p = self::tr('W');
		elseif ($d >= 293 && $d < 338) $p = self::tr('NW');
		else $p = self::tr('N');
		return $p;
	}
	public function getPressure() {
		return round(floatval($this->pressure));
	}
	public function getPressureRising() {
		switch ($this->rising) {
			case 0:
				return self::tr('steady');
			case 1:
				return self::tr('rising');
			case 2:
				return self::tr('falling');
			default:
				return self::tr('n/A');
		}
	}
	public function getVisibility() {
		return round(floatval($this->visibility));
	}
	public function getSunrise($format = 'H:i') {
		return date($format, $this->ts_sunrise);
	}
	public function getSunset($format = 'H:i') {
		return date($format, $this->ts_sunset);
	}
}

class wa72WeatherForecast {
	public $timestamp;
	public $low;
	public $high;
	public $text;
	public $code;

	public function getDate($date_format = 'd.m.') {
		return date($date_format, $this->timestamp);
	}
}