<?php

namespace ShopifyAPI\Mock;

class Response {

	public $fixture;
	public $method;
	public $url;
	
	public function __construct($method, $url, Fixture $fixture)
	{
		$this->fixture = $fixture;
		$this->method = $method;
		$this->url = $this->clean($url);
	}

	public function parseData()
	{
		return $this->fixture->parseData();
	}

	public function clean($url)
	{
		if(strpos($url, '?') !== false)
		{
			return substr($url, 0, strpos($url, '?'));
		}

		return $url;
	}
}