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
		$this->url = $url;
	}

	public function parseData()
	{
		return $this->fixture->parseData();
	}
}