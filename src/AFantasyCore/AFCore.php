<?php

namespace AFantasyCore;

use pocketmine\plugin\PluginBase;
use pocketmine\event\listener;
use pocketmine\{Player, Server};
use pocketmine\command\{Command, CommandSender, ConsoleCommandSender, CommandExecutor, ConsoleCommandExecutor};
use pocketmine\nbt\tag\{CompoundTag, IntTag, ListTag, StringTag};
//<EVENT>
use pocketmine\event\player\{
    PlayerMoveEvent, PlayerItemHeldEvent, PlayerChatEvent, PlayerItemConsumeEvent, PlayerRespawnEvent, PlayerDeathEvent, PlayerQuitEvent, PlayerKickEvent, PlayerCommandPreprocessEvent, PlayerInteractEvent, PlayerPreLoginEvent, PlayerLoginEvent, PlayerJoinEvent, PlayerBedEnterEvent, PlayerHungerChangeEvent, PlayerDropItemEvent
    };
use pocketmine\event\entity\{
    EntityTeleportEvent, ProjectileHitEvent, EntityShootBowEvent,EntityDamageByEntityEvent, EntityDamageByChildEntityEvent, EntityDamageEvent, EntityInventoryChangeEvent, EntityLevelChangeEvent
    };
use pocketmine\event\block\{
    BlockBreakEvent, BlockPlaceEvent
    };
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\event\level\LevelLoadEvent;
use pocketmine\level\Position;
use pocketmine\level\Level;
use pocketmine\entity\Entity;
use pocketmine\math\Vector3;

class AFCore extends PluginBase implements Listener{

public function onEnable(){
	$this->getServer()->getPluginManager()->registerEvents($this, $this);
}

    public function onVoidLoop(PlayerMoveEvent $event){//Credit For rirititi taken from NoVoid And Remodded By Me LOL
        if($event->getTo()->getFloorY() <= 5){
			$player = $event->getPlayer();
            $AFC = $this->getServer()->getDefaultLevel()->getSafeSpawn();
			$x = $AFC->getX();
			$y = $AFC->getY();
			$z = $AFC->getZ();
			$level = $this->getServer()->getDefaultLevel();
            $player->teleport(new Vector3($x, $y+5, $z, $level));
            }
        }
	public function spawnLobby(PlayerLoginEvent $event) {//Credit For philipshilling taken from AlwaysSpawn and Remodded By ME LOL
		    $player = $event->getPlayer();
            $AFC = $this->getServer()->getDefaultLevel()->getSafeSpawn();
			$x = $AFC->getX();
			$y = $AFC->getY();
			$z = $AFC->getZ();
		    $level = $this->getServer()->getDefaultLevel();
		    $player->setLevel($level);
		    $player->teleport(new Vector3($x, $y+5, $z, $level));
		}

}
