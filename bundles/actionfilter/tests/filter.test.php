<?php

Bundle::start('actionfilter');

use ActionFilter\Filter;

class Test_Filter extends PHPUnit_Framework_TestCase
{
	public function setup()
	{
		$this->filter = new Filter('test');
	}

	public function test_can_store_callback_as_string()
	{
		$this->assertTrue(is_string($this->filter->callback()));
	}

	public function test_can_store_callback_as_closure()
	{
		$filter = new Filter(function() { return 'hello'; });
		$this->assertTrue(is_callable($filter->callback()));
	}

	public function test_can_apply_only_scope()
	{
		$this->filter->only('new');
		$this->assertTrue($this->filter->scoped());
	}

	public function test_can_apply_except_scope()
	{
		$this->filter->except('new');
		$this->assertTrue($this->filter->scoped());
	}

	public function test_can_determine_relevance_of_no_scope()
	{
		$this->assertTrue($this->filter->relevant('new'));
	}

	public function test_can_determine_relevance_of_only()
	{
		$this->filter->only('new');
		$this->assertTrue($this->filter->relevant('new'));
		$this->assertFalse($this->filter->relevant('create'));
	}

	public function test_can_determine_relevance_of_except()
	{
		$this->filter->except('new');
		$this->assertTrue($this->filter->relevant('create'));
		$this->assertFalse($this->filter->relevant('new'));
	}
}
