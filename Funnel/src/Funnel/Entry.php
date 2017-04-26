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

use Funnel\builtin\economy\EconomyFiltrateDummy;
use Funnel\builtin\economy\EconomyFiltrateEconomyS;
use pocketmine\plugin\PluginBase;
use pocketmine\Server;

/**
 * Plugin main class.
 */
class Entry extends PluginBase{
	private static $PLUGIN_NAME = "Funnel";

	private $depsInitialized;
	private $registeredFiltrates = [];
	private $filtrates = [];

	/**
	 * Retrieves an instance of this plugin using the {@link Server} context.
	 *
	 * @param Server $server
	 * @return Entry
	 */
	public static function getInstance(Server $server) : Entry{
		return $server->getPluginManager()->getPlugin(self::$PLUGIN_NAME);
	}

	/**
	 * @return bool
	 */
	public function getDepsInitialized() : bool{
		return $this->depsInitialized;
	}

	/**
	 * Registers a "filtrate", i.e. a dependency support
	 *
	 * The validity of the class will not be checked here, nor will Funnel attempt to load the class.
	 *
	 * @param string $category
	 * @param string $className
	 */
	public function registerFiltrate(string $category, string $className){
		$this->registeredFiltrates[$category][] = $className;
	}


	private function registerDefaultFiltrates(){
		$this->registerFiltrate(EconomyFiltrate::FILTRATE_CATEGORY, EconomyFiltrateDummy::class);
		$this->registerFiltrate(EconomyFiltrate::FILTRATE_CATEGORY, EconomyFiltrateEconomyS::class);
	}

	/**
	 * @internal Only to be called by Funnel internally.
	 */
	public function initDeps(){
		$this->initDep(EconomyFiltrate::class);
	}

	private function initDep(string $parentName){
		$category = constant("$parentName::FILTRATE_CATEGORY");
		$writtenName = $this->getConfig()->get($category, "dummy");

		load_filtrate:
		$className = $this->registeredFiltrates[$category][$writtenName] ?? "";

		if(!class_exists($className, true) or !is_subclass_of($className, $parentName)){
			$this->getLogger()->warning("Failed to load $category support \"$writtenName\"");
			$writtenName = "dummy";
			goto load_filtrate;
		}

		$this->filtrates[$category] = new $className($this);
	}


	/**
	 * @internal Only to be called by the PocketMine API
	 */
	public function onLoad(){
		self::$PLUGIN_NAME = $this->getName();
	}

	/**
	 * @internal Only to be called by the PocketMine API
	 */
	public function onEnable(){
		$fh = $this->getResource("config.yml");
		$defaultText = stream_get_contents($fh);
		fclose($fh);

		$default = yaml_parse($defaultText);
		$set = ($hasOld = is_file($this->getDataFolder() . "config.yml")) ? yaml_parse_file($this->getDataFolder() . "config.yml") : [];

		if(array_keys($default) != array_keys($set)){ // != for no order checking
			foreach($default as $key => $placeHolder){
				$defaultText = str_replace($placeHolder, $set[$key] ?? "dummy", $defaultText);
			}
			file_put_contents($this->getDataFolder() . "config.yml", $defaultText);

			$this->getLogger()->notice($hasOld ?
				"New config keys have been added to Funnel. Check the updated config file in " . realpath($this->getDataFolder() . "config.yml") :
				"Thank you for using Funnel. Please setup Funnel by editing " . realpath($this->getDataFolder() . "config.yml"));
		}

		///////////////////////////////////////////////////////
		// getConfig() should not be called before this line //
		///////////////////////////////////////////////////////

		$this->getServer()->getScheduler()->scheduleTask(new CallbackPluginTask($this, [$this, "initDeps"]));
		$this->registerDefaultFiltrates();
	}
}
