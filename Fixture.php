<?php

namespace ShopifyAPI\Mock;

use \Exception;

class Fixture {

	public $name;
	public $ext;
	public $path;
	public $data;

	public $parsed;

	public function __construct($file)
	{
		if($this->_file_exists($file))
		{
			list($name, $ext) = explode('.', substr($file, strrpos($file, '/')+1));

			$this->name = $name;
			$this->ext  = $ext;
			$this->path = $file;
		}
		else
		{
			throw new Exception('Fixture file does not exist: '. $file);
		}
	}

	public function getData()
	{
		if(empty($this->data))
		{
			$this->data = $this->_file_get_contents($this->path);
		}

		return $this->data;
	}

	/**
	 * Abstracting functionality to mock during testing
	 */
	protected static function _file_get_contents($file)
	{
		return file_get_contents($file);
	}

	/**
	 * Abstracting functionality to mock during testing
	 */
	protected static function _file_exists($file)
	{
		return file_exists($file);
	}

	/**
	 * Abstracting functionality to mock during testing
	 */
	protected static function _scandir($dir)
	{
		return scandir($dir);
	}

	/**
	 * Abstracting functionality to mock during testing
	 */
	protected static function _is_dir($dir)
	{
		return is_dir($dir);
	}

	/**
	 * JSON only for now.
	 * TODO: Implement JSON parsing
	 */
	public static function all($path = false)
	{

		if(!$path)
		{
			$path = realpath(__DIR__ .'/fixtures');
		}

		$dir = self::_scandir($path);

		$files = [];

		foreach($dir as $file)
		{
			if($file == '.' || $file == '..' || $file == 'xml') continue;

			$full_path = $path . '/' . $file;

			if(self::_is_dir($full_path))
			{
				$files = array_merge($files, self::all($full_path));
			}
			else if(self::_file_exists($full_path))
			{
				$f = new Fixture($full_path);
				$files[$f->ext][$f->name] = $f;
			}
		}

		return $files;
	}

	public function parseData()
	{
		if(is_null($this->data))
		{
			$this->getData();
		}

		if(!empty($this->parsed))
		{
			return $this->parsed;
		}

		if($this->ext == 'json')
		{
			$this->parsed = $this->parseJson();
			return $this->parsed;
		}

		return $this->data;
	}

	public function parseJson()
	{
		return json_decode($this->data);
	}

	/**
	 * TODO: implement xml parsing, but srsly?
	 */
	public function parseXML()
	{

	}

}
