<?php

namespace ShopifyAPI\Mock;

use \Exception;
use RocketCode\Shopify\API as BaseShopifyAPI;

class ShopifyAPI extends BaseShopifyAPI {

	public $responses = null;

	// These are here because Rocket Code didn't build for testing...
	// This is bad and it makes me feel bad.
	private $_API = array();
	private static $_KEYS = array('API_KEY', 'API_SECRET', 'ACCESS_TOKEN', 'SHOP_DOMAIN');
	
	public function __construct($data = FALSE)
	{
		$this->responses = new ResponseCollection();
		$this->registerFixedResponses();
		parent::__construct($data);
	}

    protected function registerFixedResponses()
	{
        $fixtures = Fixture::all();

        foreach($fixtures as $ext => $ext_fixtures)
        {
        	foreach($ext_fixtures as $fixture)
        	{
	        	//skip count
	        	if($fixture->name == 'count') continue;

	        	# register the count fixture for this resource, if it exists
	        	$this->responses->add(
	        		new Response('get', '/admin/'. $fixture->name .'/count.'. $ext, $fixtures[$ext]['count'])
	        	);

	        	# register the resource fixture
	        	$r = new Response('get', '/admin/'. $fixture->name . '.' . $ext, $fixture);
	        	$this->responses->add($r);

	        	if($ext == 'json')
	        	{
	        		$data = $r->parseData();

	        		if(is_array($data))
	        		{

		        		foreach($data as $datum)
		        		{
		        			if(!is_object($datum) || !property_exists($datum, 'id')) continue;

		        			$f = clone $fixture;
		        			$f->parsed = [$this->singularize($f->name) => $datum];

		        			$this->responses->add(
			        			new Response('get', '/admin/'. $fixture->name .'/'. $datum->id .'.'. $ext, $f)
			        		);

			        		$this->responses->add(
			        			new Response('get', '/admin/'. $fixture->name .'/'. $datum->id .'/metafields.'. $ext, $fixtures[$ext]['metafields'])
			        		);
		        		}

		        	}
	        	}
	        }
        }
    }

    /**
	* Singularize a string.
	* Converts a word to english singular form.
	*
	* Usage example:
	* {singularize "people"} # person
	*/
    private function singularize ($params)
	{
	    if (is_string($params))
	    {
	        $word = $params;
	    } else if (!$word = $params['word']) {
	        return false;
	    }

	    $singular = array (
	        '/(quiz)zes$/i' => '\\1',
	        '/(matr)ices$/i' => '\\1ix',
	        '/(vert|ind)ices$/i' => '\\1ex',
	        '/^(ox)en/i' => '\\1',
	        '/(alias|status)es$/i' => '\\1',
	        '/([octop|vir])i$/i' => '\\1us',
	        '/(cris|ax|test)es$/i' => '\\1is',
	        '/(shoe)s$/i' => '\\1',
	        '/(o)es$/i' => '\\1',
	        '/(bus)es$/i' => '\\1',
	        '/([m|l])ice$/i' => '\\1ouse',
	        '/(x|ch|ss|sh)es$/i' => '\\1',
	        '/(m)ovies$/i' => '\\1ovie',
	        '/(s)eries$/i' => '\\1eries',
	        '/([^aeiouy]|qu)ies$/i' => '\\1y',
	        '/([lr])ves$/i' => '\\1f',
	        '/(tive)s$/i' => '\\1',
	        '/(hive)s$/i' => '\\1',
	        '/([^f])ves$/i' => '\\1fe',
	        '/(^analy)ses$/i' => '\\1sis',
	        '/((a)naly|(b)a|(d)iagno|(p)arenthe|(p)rogno|(s)ynop|(t)he)ses$/i' => '\\1\\2sis',
	        '/([ti])a$/i' => '\\1um',
	        '/(n)ews$/i' => '\\1ews',
	        '/s$/i' => ''
	    );

	    $irregular = array(
	        'person' => 'people',
	        'man' => 'men',
	        'child' => 'children',
	        'sex' => 'sexes',
	        'move' => 'moves'
	    );	

	    $ignore = array(
	        'equipment',
	        'information',
	        'rice',
	        'money',
	        'species',
	        'series',
	        'fish',
	        'sheep',
	        'press',
	        'sms',
	    );

	    $lower_word = strtolower($word);
	    foreach ($ignore as $ignore_word)
	    {
	        if (substr($lower_word, (-1 * strlen($ignore_word))) == $ignore_word)
	        {
	            return $word;
	        }
	    }

	    foreach ($irregular as $singular_word => $plural_word)
	    {
	        if (preg_match('/('.$plural_word.')$/i', $word, $arr))
	        {
	            return preg_replace('/('.$plural_word.')$/i', substr($arr[0],0,1).substr($singular_word,1), $word);
	        }
	    }

	    foreach ($singular as $rule => $replacement)
	    {
	        if (preg_match($rule, $word))
	        {
	            return preg_replace($rule, $replacement, $word);
	        }
	    }

	    return $word;
	}

	/**
	 * Executes the actual cURL call based on $userData
	 * @param array $userData
	 * @return mixed
	 * @throws \Exception
	 */
    public function call($userData = array(), $verifyData = TRUE)
    {
    	if ($verifyData)
	    {
		    foreach (self::$_KEYS as $k)
		    {
			    if ((!array_key_exists($k, $this->_API)) || (empty($this->_API[$k])))
			    {
				    throw new Exception($k . ' must be set.');
			    }
		    }
	    }

	    $defaults = array(
		    'CHARSET'       => 'UTF-8',
		    'METHOD'        => 'GET',
		    'URL'           => '/',
			'HEADERS'       => array(),
	        'DATA'          => array(),
	        'FAILONERROR'   => TRUE,
	        'RETURNARRAY'   => FALSE,
	        'ALLDATA'       => FALSE
	    );

	    if ($verifyData)
	    {
		    $request = $this->setupUserData(array_merge($defaults, $userData));
	    }
	    else
	    {
		    $request = array_merge($defaults, $userData);
	    }
	    
		$url = $request['URL'];

		$method = strtolower($request['METHOD']);

		if($method != 'get')
		{
			throw new Exception("Test Failure: Cannot test non-GET calls at this time.");
		}

		$response = $this->responses->find($method, $url);

		if(empty($response))
		{
			throw new Exception("Test Failure: Failed to find a response for: ". $request['URL']);
		}

		// Data returned
        $result = $response->parseData();

        // Headers
     //    $info = array_filter(array_map('trim', explode("\n", substr($response, 0, $headerSize))));

     //    foreach($info as $k => $header)
     //    {
	    //     if (strpos($header, 'HTTP/') > -1)
	    //     {
     //            $_INFO['HTTP_CODE'] = $header;
     //            continue;
     //        }

     //        list($key, $val) = explode(':', $header);
     //        $_INFO[trim($key)] = trim($val);
     //    }


     //    // cURL Errors
     //    $_ERROR = array('NUMBER' => curl_errno($ch), 'MESSAGE' => curl_error($ch));

     //    curl_close($ch);

	    // if ($_ERROR['NUMBER'])
	    // {
		   //  throw new \Exception('ERROR #' . $_ERROR['NUMBER'] . ': ' . $_ERROR['MESSAGE']);
	    // }


	    // Send back in format that user requested
	    // if ($request['ALLDATA'])
	    // {
		   //  if ($request['RETURNARRAY'])
		   //  {
			  //   $result['_ERROR'] = $_ERROR;
			  //   $result['_INFO'] = $_INFO;
		   //  }
		   //  else
		   //  {
			  //   $result->_ERROR = $_ERROR;
			  //   $result->_INFO = $_INFO;
		   //  }
		   //  return $result;
	    // }
	    // else
	    // {
		   //  return $result;
	    // }

	    return $result;


    }

    /**
	 * Loops over each of self::$_KEYS, filters provided data, and loads into $this->_API
	 * @param array $data
	 */
	public function setup($data = array())
	{

		foreach (self::$_KEYS as $k)
		{
			if (array_key_exists($k, $data))
			{
				$this->_API[$k] = $data[$k];
			}
		}
	}

	/**
	 * Checks that data provided is in proper format
	 * @example Checks for presence of /admin/ in URL
	 * @param array $userData
	 * @return array
	 */
	private function setupUserData($userData = array())
	{
		$returnable = array();

		foreach($userData as $key => $value)
		{
			switch($key)
			{
				case 'URL':
					// Remove shop domain
					$url = str_replace($this->_API['SHOP_DOMAIN'], '', $value);

					// Verify it contains /admin/
					if (strpos($url, '/admin/') !== 0)
					{
						$url = str_replace('//', '/', '/admin/' . preg_replace('/\/?admin\/?/', '', $url));
					}
					$returnable[$key] = $url;
					break;

				default:
					$returnable[$key] = $value;

			}
		}

		return $returnable;
	}

}