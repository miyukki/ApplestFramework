<?php

require_once(dirname(__FILE__).'/Core.php');

class ConsoleController extends Controller
{
	public $user_vars = array();
	public function exec()
	{
		// term terminal
		// make c
		// make t
		$this->console();
	}

	public function vars_completion($input, $index) {
		if(empty($input))
		{
			return array();
		}

		$matches = array();

		$keys = array_keys($this->user_vars);
		$functions = get_defined_functions();
		$keys = array_merge($keys, array_values($functions['internal']), array_values(get_declared_classes()));

		foreach ($keys as $key) {
			if(strpos($key, $input) === 0)
			{
				$matches[] = $key;
			}
		}
		
		return $matches;
	}

	private function console()
	{
		readline_completion_function(array($this, 'vars_completion'));
		while(1)
		{
			$_line = readline('>>> ');
			if(empty($_line))
			{
				continue;
			}

			readline_add_history($_line);

			extract($this->user_vars, EXTR_SKIP);

			$_code = 'return '.trim($_line, ';').';';
			echo '<<< ' . var_export(@eval($_code), true) . PHP_EOL;

			$this->user_vars = array();
			$_vars = get_defined_vars();
			foreach ($_vars as $_key => $_value)
			{
				if(strpos($_key, '_') === 0)
				{
					continue;
				}
				$this->user_vars[$_key] = $_value;
			}

			echo PHP_EOL;
		}
	}
}
dispatchAction('ConsoleController');