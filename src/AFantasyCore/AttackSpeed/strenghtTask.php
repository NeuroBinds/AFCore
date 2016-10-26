<?php
namespace AFantasyCore\AttackSpeed; 

use pocketmine\server;
use pocketmine\scheduler\PluginTask;
use pocketmine\scheduler\Task;
use pocketmine\scheduler\ServerScheduler;
use pocketmine\entity\Effect;
use pocketmine\event\Listener;
use pocketmine\plugin\Plugin;
use pocketmine\level\Level;
use pocketmine\block\Block;
use pocketmine\Player;
use pocketmine\utils\TextFormat as C;
use pocketmine\IPlayer;
use pocketmine\math\Vector3;

   class strenghtTask extends PluginTask{
	private $plugin;
	private $player;
    public function __construct(Plugin $plugin, $player){
        parent::__construct($plugin);
		$this->p = $plugin;
		$this->player = $player;
		$this->playername = $player->getName();
	}
	public function onRun($tick) {
		if($this->p->getServer()->getPlayer($this->playername) instanceof Player) {
		if($this->player->getInventory()->getItemInHand()->getId() === 270 or $this->player->getInventory()->getItemInHand()->getId() === 258 or $this->player->getInventory()->getItemInHand()->getId() === 275 or $this->player->getInventory()->getItemInHand()->getId() === 279 or $this->player->getInventory()->getItemInHand()->getId() === 286) {
			$weak = Effect::getEffect(5);
			$weak->setDuration(6);
			$weak->setAmplifier(1);
			$weak->setVisible(false);
			$this->player->addEffect($weak);
		}
		}
	}
   }