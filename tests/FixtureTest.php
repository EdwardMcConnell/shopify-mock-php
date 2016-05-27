<?php

use StubFixture as Fixture;

class FixtureTest extends TestCase
{
	/**
	 * @expectedException			Exception
	 * @expectedExceptionMessage	not exist
	 */
	public function test_construct_throwsException()
	{
		$f = new Fixture('some_dir/not_found.json');
	}

	public function test_construct_setsParams()
	{
		$f = new Fixture('some_dir/file1.json');

		$this->assertEquals('some_dir/file1.json', $f->path);
		$this->assertEquals('file1', $f->name);
		$this->assertEquals('json', $f->ext);
	}

	public function test_getData_extractsFileContents()
	{
		$f = new Fixture('file1.json');

		$this->assertNull($f->data);

		$this->assertEquals('file1 contents', $f->getData());
	}

	public function test_all_createsFixtures()
	{
		$all = Fixture::all();

		$this->assertArrayHasKey('json', $all);

		$this->assertArrayHasKey('transactions', $all['json']);
	}

	public function test_parseData_getsObject()
	{
		$f = new Fixture('fixtures/json/count.json');

		$this->assertNull($f->parsed);
		$this->assertNull($f->data);

		$this->assertTrue(is_object($f->parseData()));

		$this->assertObjectHasAttribute('count', $f->parsed);
	}
}