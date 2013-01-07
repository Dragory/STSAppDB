<?php namespace ActionFilter;

use Request;
use Response;

trait Filterable
{
	protected $before_filters = [];
	protected $after_filters = [];

	protected function before_filter($callback)
	{
		return $this->before_filters[] = Filter::make($callback);
	}

	protected function after_filter($callback)
	{
		return $this->after_filters[] = Filter::make($callback);
	}

	protected function execute_filter($filter)
	{
		$callback = $filter->callback();
		return is_callable($callback) ? $callback() : $this->$callback();
	}

	// Add before filters here
	protected function before_filters($action) {
		foreach ($this->before_filters as $filter)
		{
			if ($filter->relevant($action))
			{
				$result = $this->execute_filter($filter);

				if ($result !== null)
				{
					return $result;
				}
			}
		}
	}

	// Add after filters here
	protected function after_filters($action) {
		foreach ($this->after_filters as $filter)
		{
			if ($filter->relevant($action))
			{
				$result = $this->execute_filter($filter);
			}
		}
	}

	protected function param($param = null)
	{
		return array_get(Request::route()->parameters, $param, null);
	}


	/**
	 * Execute a controller method with the given parameters.
	 *
	 * @param  string    $method
	 * @param  array     $parameters
	 * @return Response
	 */
	public function execute($method, $parameters = array())
	{
		$filters = $this->filters('before', $method);

		// Again, as was the case with route closures, if the controller "before"
		// filters return a response, it will be considered the response to the
		// request and the controller method will not be used.
		$response = \Filter::run($filters, array(), true);

		if (is_null($response))
		{
			$this->before();
			$response = $this->before_filters($method);

			if (is_null($response))
			{
				$response = $this->response($method, $parameters);
			}
		}

		$response = Response::prepare($response);

		// The "after" function on the controller is simply a convenient hook
		// so the developer can work on the response before it's returned to
		// the browser. This is useful for templating, etc.
		$this->after($response);

		\Filter::run($this->filters('after', $method), array($response));

		$this->after_filters($method);

		return $response;
	}
}

