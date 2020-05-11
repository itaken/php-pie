<?php 

/**
 * 依赖注入容器
 * 
 * @link https://github.com/fabpot-graveyard/twittee
 * @see http://twittee.org/
 */
class Container
{
	private $s = array();

	function __set($k, $c)
	{
		$this->s[$k] = $c;
	}

	function __get($k)
	{
		return $this->s[$k]($this);
	}
}
