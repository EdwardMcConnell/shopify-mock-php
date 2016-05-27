<?php

class StubFixture extends \ShopifyAPI\Mock\Fixture
{
	protected static function _file_get_contents($file)
	{
		if($file == 'file1.json')
		{
			return 'file1 contents';
		}

		return parent::_file_get_contents($file);
	}

	protected static function _file_exists($file)
	{
		if(strpos($file, 'not_found') !== false)
		{
			return false;
		}

		if(strpos($file, 'file1.json') !== false)
		{
			return true;
		}

		return parent::_file_exists($file);
	}
}
