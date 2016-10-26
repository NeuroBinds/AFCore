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

   class weakTask extends PluginTask{
	private $plugin;
	private $player;
    public function __construct(Plugin $plugin, $player, $amp, $time){
        parent::__construct($plugin);
		$this->p = $plugin;
		$this->player = $player;
		$this->amp = $amp;
		$this->time = $time;
	}
	public function onRun($tick) {
		$weak = Effect::getEffectByName("Weakness");
		$weak->setDuration($this->time);
		$weak->setAmplifier($this->amp);
		$weak->setVisible(false);
		$this->player->addEffect($weak);
	}
   }