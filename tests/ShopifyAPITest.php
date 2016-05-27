<?php

use StubShopifyAPI as ShopifyAPI;

class ShopifyAPITest extends TestCase {
	

	public function test_construct_createsResponseCollection()
	{
		$s = new ShopifyAPI();
		$this->assertInstanceOf('\\ShopifyAPI\\Mock\\ResponseCollection', $s->responses);
	}

	public function test_call_getCount()
	{
		$s = new ShopifyAPI($this->getConfig());

		$res = $s->call([
	      'URL' => 'products/count.json',
	      'METHOD' => 'get',
	      'DATA' => []
	    ]);

		$this->assertObjectHasAttribute('count', $res);
	}


	protected function getConfig()
	{
		return [
			'API_KEY' => 'mock_key', 
			'API_SECRET' => 'mock_secret', 
			'ACCESS_TOKEN' => 'mock_token', 
			'SHOP_DOMAIN' => 'mock_domain'
		];
	}
}