<?php

namespace ShopifyAPI\Mock;

class ResponseCollection {
	
	public $responses;

	public function add(Response $r)
	{
		if(!isset($this->responses[$r->method]))
		{
			$this->responses[$r->method] = [];
		}

		$this->responses[$r->method][$r->url] = $r;
	}

	public function find($method, $url)
	{
		return $this->responses[$method][$url] ?: false;
	}
}