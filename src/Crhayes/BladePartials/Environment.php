<?php namespace Crhayes\BladePartials;

class Environment extends \Illuminate\View\Environment {

	/**
	 * Storage for our blocks.
	 * 
	 * @var array
	 */
	protected $blocks = array();

	/**
	 * Render a partial by echoing its contents.
	 * The variables defined outside the scope of this block
	 * (i.e. within our template) are passed in so we can use them.
	 * 
	 * @param  string 	$file
	 * @param  array 	$vars
	 * @param  closure 	$callback
	 * @return void
	 */
	public function renderPartial($file, $vars, $callback)
	{
		$callback($file, $vars);

		$this->flushBlocks();
	}

	/**
	 * Start injecting content into a block.
	 * 
	 * @param  string 	$block
	 * @param  string 	$content
	 * @return void
	 */
	public function startBlock($block, $content = '')
	{
		if ($content === '')
		{
			ob_start() && $this->blocks[] = $block;
		}
		else
		{
			$this->blocks[$block] = $content;
		}
	}

	/**
	 * Stop injecting content into a block.
	 * 
	 * @return void
	 */
	public function stopBlock()
	{
		$last = array_pop($this->blocks);

		$this->blocks[$last] = ob_get_clean();
	}

	/**
	 * Get the string contents of a block.
	 * 
	 * @param  string 	$block
	 * @param  string 	$default
	 * @return string
	 */
	public function renderBlock($block, $default = '')
	{
		return isset($this->blocks[$block]) ? $this->blocks[$block] : $default;
	}

	/**
	 * Get the entire array of blocks.
	 * 
	 * @return array
	 */
	public function getBlocks()
	{
		return $this->blocks;
	}

	/**
	 * Flush all the block contents.
	 * 
	 * @return void
	 */
	public function flushBlocks()
	{
		$this->blocks = [];
	}

}