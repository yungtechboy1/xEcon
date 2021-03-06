<?php

namespace xecon\cmd;

use pocketmine\command\CommandSender;
use pocketmine\Player;
use xecon\entity\PlayerEnt;
use xecon\entity\Service;
use xecon\XEcon;

class SetMoneyCommand extends XEconCommand{
	private $tarAccName, $accGenName, $cmdName;
	/**
	 * @param XEcon $main
	 * @param string $tarAccName
	 * @param string $accGenName
	 * @param $cmdName
	 */
	public function __construct(XEcon $main, $tarAccName, $accGenName, $cmdName){
		$this->tarAccName = $tarAccName;
		$this->accGenName = $accGenName;
		$this->cmdName = $cmdName;
		parent::__construct($main);
	}
	public function getName_(){
		return "$this->cmdName";
	}
	public function getDesc_(){
		return "Set the player's {$this->accGenName} to a specified amount";
	}
	public function getUsage_(){
		return "/{$this->getName_()} <player> <amount> [.e] [details ...](add '.e' if the player might be offline)";
	}
	public function getAliases_(){
		return $this->cmdName === "setcash" ? ["set$"]:[];
	}
	public function execute_(CommandSender $sender, array $args){
		if(!isset($args[1])){
			return false;
		}
		$amount = $args[1];
		if(!is_numeric($amount)){
			return false;
		}
		$amount = floatval($amount);
		$e = false;
		if(isset($args[2]) and $args[2] === ".e"){
			$e = true;
		}
		if(!$e){
			$p = $this->getPlugin()->getServer()->getPlayer($args[0]);
			if(!($p instanceof Player)){
				return "Player $args[0] is not online! Try adding 'e' at the end of the command if he is offline.";
			}
			$ent = $this->getPlugin()->getPlayerEnt($p->getName(), false);
		}
		else{
			$ent = $this->getPlugin()->getPlayerEnt($args[0]);
			if(!($ent instanceof PlayerEnt)){
				return "Player $args[0] has not been registered!";
			}
		}
		$acc = $ent->getAccount($this->tarAccName);
		$src = $this->getPlugin()->getService()->getService(Service::ACCOUNT_OPS);
		$details = implode(" ", array_slice($args, 2 + ($e ? 1:0)));
		if($acc->transactWithAccountTo($amount, $src, strlen(trim($details)) > 0 ? trim($details):"{$this->accGenName} set by a command", $failureReason)){
			return ucfirst($this->accGenName) . " of {$ent->getName()} has been set to \$$amount";
		}
		return "Failed to set money of $this->accGenName of {$ent->getName()} to \$$amount because $failureReason";
	}
}
