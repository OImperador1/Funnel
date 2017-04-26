<?php

/**
 *  ______                      _
 * |  ____|                    | |
 * | |__ _   _ _ __  _ __   ___| |
 * |  __| | | | '_ \| '_ \ / _ \ |
 * | |  | |_| | | | | | | |  __/ |
 * |_|  \___,_|_| |_|_| |_|\___|_|
 *
 *
 * Copyright (c) 2017 SOFe and contributors
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

namespace Funnel;

use pocketmine\plugin\Plugin;
use pocketmine\scheduler\PluginTask;

/**
 * This clas was created only intended for internal use, but other plugins can use this too.
 *
 * @since 0.1.0
 */
class CallbackPluginTask extends PluginTask{
	private $callback;
	private $args;

	/**
	 * @param Plugin   $owner    the plugin that is truly responsible for the task (not necessarily Funnel)
	 * @param callable $callback the callback to execute, accepting parameters <code>$currentTick, ...$args</code>, where
	 *                           <code>$currentTick</code> is the server tick when the task is executed, and
	 *                           <code>...$args</code> is the third argument onwards to this constructor, passed variadically
	 * @param array    ...$args  the arguments to pass to the callback, in form of varargs.
	 */
	public function __construct(Plugin $owner, callable $callback, ...$args){
		parent::__construct($owner);
		$this->callback = $callback;
		$this->args = $args;
	}

	public function onRun($currentTick){
		$c = $this->callback;
		$c($currentTick, ...$this->args);
	}
}
