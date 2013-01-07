<?php namespace ActionFilter;

class Filter
{
	protected static $available_scopes = array('only', 'except');
	protected $callback;
	protected $actions;
	protected $scope;

	public function __construct($callback)
	{
		$this->callback = $callback;
	}

	public function callback()
	{
		return $this->callback;
	}

	public function scope($scope, $actions)
	{
		if ($actions and in_array($scope, static::$available_scopes))
		{
			$this->scope = $scope;
			$this->actions = (array) $actions;
		}

		return $this;
	}

	public function only($actions)
	{
		return $this->scope('only', $actions);
	}

	public function except($actions)
	{
		return $this->scope('except', $actions);
	}

	public function scoped()
	{
		return $this->scope or $this->actions;
	}

	public function relevant($action)
	{
		return ! $this->scoped() || $this->in_only($action) || $this->in_except($action);
	}

	public function in_only($action)
	{
		return $this->scope == 'only' and in_array($action, $this->actions);
	}

	public function in_except($action)
	{
		return $this->scope == 'except' and ! in_array($action, $this->actions);
	}

}
