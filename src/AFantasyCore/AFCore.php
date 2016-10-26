<?php

namespace AFantasyCore;

use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\{
	Player, Server};
use pocketmine\command\{
	Command, CommandSender, ConsoleCommandSender, CommandExecutor, ConsoleCommandExecutor};
use pocketmine\nbt\tag\{
	CompoundTag, IntTag, ListTag, StringTag};
use pocketmine\nbt\NBT;
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
use pocketmine\level\{
    Level, Positon};
use pocketmine\entity\{
	Entity, Effect};
use pocketmine\utils\{
    TextFormat as AFC, Config, Binary, BinaryStream};
use pocketmine\math\{Vector3, Math, AxisAlignedBB};
use pocketmine\item\Item;
use pocketmine\block\Block;
use pocketmine\level\format\{
    FullChunk, Chunk};
use pocketmine\scheduler\PluginTask;
use pocketmine\network\protocol\BlockEventPacket;
use pocketmine\event\server\QueryRegenerateEvent;
use pocketmine\inventory\ShapedRecipe;
use AFantasyCore\AttackSpeed\weakTask;
use AFantasyCore\AttackSpeed\strenghtTask;
use AFantasyCore\JukeboxPE\JukeboxAPI;

class AFCore extends PluginBase implements Listener{

	public $wpStep1 = [];
	public $wpStep2 = [];
    public $song;
    public $SongPlayer;
    public $name;

	public function onLoad(){
        $this->saveDefaultConfig();
		@mkdir($this->getDataFolder());
		$this->query = new Config($this->getDataFolder() . "Query.yml", Config::YAML, array("players-count" => 100, "max-players-count" => 1000));
        $this->abe = new Config($this->getDataFolder() . "AntiAbusiveEnchants.yml", Config::YAML,array("max-level" => 30));
	}
public function onEnable(){
        foreach($this->getCfg() as $craft) {
            $result = $this->getItem($craft["result"]);
            $rec = new ShapedRecipe($result, "ABC", "DEF", "GHI");
            $rec->setIngredient("A", $this->getItem($craft["shape"][0][0]));
            $rec->setIngredient("B", $this->getItem($craft["shape"][0][1]));
            $rec->setIngredient("C", $this->getItem($craft["shape"][0][2]));
            $rec->setIngredient("D", $this->getItem($craft["shape"][1][0]));
            $rec->setIngredient("E", $this->getItem($craft["shape"][1][1]));
            $rec->setIngredient("F", $this->getItem($craft["shape"][1][2]));
            $rec->setIngredient("G", $this->getItem($craft["shape"][2][0]));
            $rec->setIngredient("H", $this->getItem($craft["shape"][2][1]));
            $rec->setIngredient("I", $this->getItem($craft["shape"][2][2]));
            $this->getServer()->getCraftingManager()->registerRecipe($rec);
            $this->getLogger()->info("Registered recipe for " . $this->getItem($craft["result"])->getName());
        }
			$this->getServer()->getPluginManager()->registerEvents($this, $this);        if(!is_dir($this->getPluginDir())) {
            @mkdir($this->getServer()->getDataPath()."plugins/AFCore/Songs");
        }
        $this->getServer()->getPluginManager()->registerEvents($this,$this);
        if(!$this->CheckMusic()) {
            $this->getLogger()->info("§bYou Doesnt have Any Songs Please Provide .nbs file into AFCore/Songs!");
        }else{
            $this->StartNewTask();
        }
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
	public function getMaxEnchantLevel(){//Credit For SavionLegendZzz taken from AntiAbusiveEnchants and Remodded By ME LOL
		return $this->abe->get("max-level");
	}
	public function antiAbusiveEnchant(PlayerItemHeldEvent $ev){//Credit For SavionLegendZzz taken from AntiAbusiveEnchants and Remodded By ME LOL
		$p = $ev->getPlayer();
		$max = $this->getMaxEnchantLevel();
		$contents = $p->getInventory()->getContents();
		$i = $p->getInventory()->getItemInHand();
			if($i instanceof Item){
				if($i->hasEnchantments()){
					foreach($i->getEnchantments() as $e){
						if($e->getLevel() >= $max){
							$p->getInventory()->removeItem($i);
							$p->sendMessage(AFC::BOLD . AFC::DARK_RED . "[" . AFC::RED . "!" . AFC::DARK_RED . "] " . AFC::GREEN . $i->getName() . AFC::RESET . AFC::YELLOW . " has been removed from your inventory for being above or equal to the max enchantment level!");
						}
					}
				}
			}
		}
	public function appleRate(BlockBreakEvent $event) {//Credit For KairusDarkSeeker taken from AppleRate and Remodded By ME LOL
		$rnd = rand(1, 3);
		if($event->getBlock()->getId() == Block::LEAVES && $rnd == 2) {
			$event->setDrops([Item::get(Item::APPLE, 0, 1)]);
		}
	}
 public function onCommand(CommandSender $sender, Command $cmd, $label, array $args){//Credit For Ad5001 taken from AttackSpeed and Remodded By ME LOL
switch($cmd->getName()){
}
return false;
 }
 public function onHeldSpeeds(PlayerItemHeldEvent $event) {//Credit For Ad5001 taken from AttackSpeed and Remodded By ME LOL
	 if($event->getItem()->getId() === 270 or $event->getItem()->getId() === 258 or $event->getItem()->getId() === 275 or $event->getItem()->getId() === 279 or $event->getItem()->getId() === 286) {
		 $this->getServer()->getScheduler()->scheduleRepeatingTask(new  strenghtTask($this, $event->getPlayer()), 3);
	 }
 }
public function onHurtSpeeds(EntityDamageEvent $event){//Credit For Ad5001 taken from AttackSpeed and Remodded By ME LOL
	if($event instanceof EntityDamageByEntityEvent) {
	$attacker = $event->getDamager();
	if($attacker instanceof Player) {
		switch($attacker->getInventory()->getItemInHand()->getId()) {
			case 283: // gold sword
			$amp = 1.18;
			$time = 13;
			break;
			case 268: //wood sword
			$amp = 2;
			$time = 13;
			break;
			case 272: //stone sword
			$amp = 3;
			$time = 13;
			break;
			case 267: //iron sword
			$amp = 5;
			$time = 13;
			break;
			case 276: //diamond sword
			$amp = 7;
			$time = 13;
			break;			
			case 286: // gold axe
			$amp = 1;
			$time = 18;
			break;
			case 271: //wood axe
			$amp = 1;
			$time = 18;
			break;
			case 275: //stone axe
			$amp = 3;
			$time = 18;
			break;
			case 258: //iron axe
			$amp = 4.5;
			$time = 18;
			break;
			case 279: //diamond axe
			$amp = 6.5;
			$time = 18;
			break;			
			case 285: // gold pickaxe
			$amp = 1;
			$time = 16.5;
			break;
			case 270: //wood pickaxe
			$amp = 1;
			$time = 16.5;
			break;
			case 274: //stone pickaxe
			$amp = 1.5;
			$time = 16.5;
			break;
			case 257: //iron pickaxe
			$amp = 2.5;
			$time = 16.5;
			break;
			case 278: //diamond pickaxe
			$amp = 3.5;
			$time = 16.5;
			break;		
			case 2818: // gold shovel
			$amp = 1;
			$time = 16.5;
			break;
			case 269: //wood shovel
			$amp = 1;
			$time = 16.5;
			break;
			case 273: //stone shovel
			$amp = 1.5;
			$time = 16.5;
			break;
			case 256: //iron shovel
			$amp = 2.5;
			$time = 16.5;
			break;
			case 277: //diamond shovel
			$amp = 3.5;
			$time = 16.5;
			break;
			case 294: // gold hoe
			$amp = 0.5;
			$time = 16.5;
			break;
			case 290: //wood hoe
			$amp = 0.5;
			$time = 16.5;
			break;
			case 291: //stone hoe
			$amp = 1;
			$time = 16.5;
			break;
			case 292: //iron hoe
			$amp = 1.25;
			$time = 16.5;
			break;
			case 293: //diamond hoe
			$amp = 1.5;
			$time = 16.5;
			break;
			default:
			$amp = 3;
			$time = 1;
			break;
		}
		$weak = Effect::getEffectByName("Weakness");
		$weak->setDuration($time + 15);
		$weak->setAmplifier($amp);
		$weak->setVisible(false);
		$attacker->addEffect($weak);
	    $this->getServer()->getScheduler()->scheduleDelayedTask(new  weakTask($this, $attacker, $amp*0.75, $time + 10), $time + 10);
	    $this->getServer()->getScheduler()->scheduleDelayedTask(new  weakTask($this, $attacker, $amp*0.5, $time + 5), $time*2 + 5);
	    $this->getServer()->getScheduler()->scheduleDelayedTask(new  weakTask($this, $attacker, $amp*0.25, $time), $time*3);
	}
	}
}
    public function getItem(array $item) : Item {//Credit For Ad5001 taken from CustomCraft and Remodded By ME LOL
        $result = Item::get($item[0]);
        if(isset($item[1])) {
            $result->setCount($item[1]);
        }
        if(isset($item[2])) {
            $tags = $exception = null;
			$data = $item[2];
			try{
				$tags = NBT::parseJSON($data);
			}catch (\Throwable $ex){
				$exception = $ex;
			}

			if(!($tags instanceof \pocketmine\nbt\tag\CompoundTag) or $exception !== null){
				$this->getLogger()->warning(new \pocketmine\event\TranslationContainer("commands.give.tagError", [$exception !== null ? $exception->getMessage() : "Invalid tag conversion"]));
				return $result;
			}
            
            $result->setNamedTag($tags);
        }
        return $result;
    }
    private function getCfg() {//Credit For Ad5001 taken from CustomCraft and Remodded By ME LOL
        return yaml_parse(file_get_contents($this->getDataFolder() . "CustomCraft.yml"));
    }
	public function onQueryRegenerate(QueryRegenerateEvent $event) {//Credit For Kairus Dark Seeker taken from FakeServer and Remodded By ME LOL
		$event->setPlayerCount($this->query->get("players-count"));
		$event->setMaxPlayerCount($this->query->get("max-players-count"));
	}
    public function onDamage(EntityDamageEvent $e){//Credit For kvetinac97 taken from MagicServer and Remodded By ME LOL
        /** @var Player $p */
        $p = $e->getEntity();
        if (!$p instanceof Player){
            return;
        }

        if ($e instanceof EntityDamageByEntityEvent){
            /** @var Player $pl */
            $pl = $e->getDamager();
            if (!$pl instanceof Player){
                return;
            }
            $it = $pl->getInventory()->getItemInHand();
            if (!$it->hasEnchantments()){
                return;
            }
            $en = $it->getEnchantments();
            foreach ($en as $ench){
                $lvl = $ench->getLevel();
                switch ($ench->getId()){
                    case 9:
                        $e->setDamage($e->getDamage()+($lvl*1.25));
                        break;
                    case 12:
                        $e->setKnockback($e->getKnockBack()+($lvl*0.3));
                        break;
                    case 13:
                        if (!$e->isCancelled()){
                            $p->setOnFire($lvl*4);
                        }
                        break;
                    case 19:
                        $dmg = \round((($lvl+1)/4));
                        $e->setDamage($e->getDamage()+$dmg);
                        break;
                    case 20:
                        $e->setKnockBack($e->getKnockBack()+($lvl*0.4));
                        break;
                    case 21:
                        if (!$e->isCancelled()){
                            $p->setOnFire(5);
                        }
                        break;
                    case 22:
                        $pl->getInventory()->addItem(Item::ARROW, 0, 1);
                        break;
                }
            }
        }

        foreach ($p->getInventory()->getArmorContents() as $item){
            $eng = $item->getEnchantments();
            foreach ($eng as $enchantment){
                $lvl = $enchantment->getLevel();
                switch ($enchantment->getId()){
                    case 0:
                        $e->setDamage($e->getDamage() - (($lvl*0.04)*$e->getDamage()));
                        break;
                    case 1:
                        if ($e->getCause() > 4 && $e->getCause() < 8){
                            $e->setDamage($e->getDamage() - (($lvl*0.12)*$e->getDamage()));
                        }
                        break;
                    case 2:
                        if ($e->getCause() == 4){
                            $e->setDamage($e->getDamage() - (($lvl*0.15)*$e->getDamage()));
                        }
                        break;
                    case 3:
                        if ($e->getCause() > 8 && $e->getCause() < 11){
                            $e->setDamage($e->getDamage() - (($lvl*0.15)*$e->getDamage()));
                        }
                        break;
                    case 4:
                        if ($e->getCause() == 2){
                            $e->setDamage($e->getDamage() - (($lvl*0.12)*$e->getDamage()));
                        }
                        break;
                    case 7:
                        if ($e instanceof EntityDamageByEntityEvent){
                            /** @var Player $pl */
                            $pl = $e->getDamager();
                            Server::getInstance()->getPluginManager()->callEvent($ev = new EntityDamageEvent($pl, 14, $lvl*2));
                            if ($ev->isCancelled() || $ev->getDamage() <= 0){
                                break;
                            }
                            $pl->attack($lvl*2, $ev);
                        }
                        break;
                }
            }
        }
    }
    public function onBreak(BlockBreakEvent $e){//Credit For kvetinac97 taken from MagicServer and Remodded By ME LOL
        $p = $e->getPlayer();
        if (!$p->getInventory()->getItemInHand()->hasEnchantments()){
            return;
        }
        $ench = $p->getInventory()->getItemInHand()->getEnchantments();
        foreach ($ench as $en){
            $lvl = $en->getLevel();
            switch ($en->getId()){
                case 16:
                    $item = [$e->getBlock()];
                    $e->setDrops($item);
                    break;
                case 17:
                    if (\mt_rand(1, (6-$lvl)) === 2){
                        $i = $p->getInventory()->getItemInHand();
                        $i->setDamage($i->getDamage()+1);
                    }
                    break;
                case 18:
                    switch ($e->getBlock()->getId()){
                        case 16:
                            $drop = \mt_rand(3, 3+$lvl);
                            $e->setDrops([Item::get(263, 0, $drop)]);
                            break;
                        case 21:
                            $drop = \mt_rand(5, 5+$lvl);
                            $e->setDrops([Item::get(351, 4, $drop)]);
                            break;
                        case 56:
                            $drop = \mt_rand(1, 1+$lvl);
                            $e->setDrops([Item::get(264, 0, $drop)]);
                            break;
                        case 73:
                            $drop = \mt_rand(5, 5+$lvl);
                            $e->setDrops([Item::get(331, 0, $drop)]);
                            break;
                        case 89:
                            $e->setDrops([Item::get(16, 0, 4)]);
                            break;
                        case 129:
                            $drop = \mt_rand(1, \round(1+($lvl/3)));
                            $e->setDrops([Item::get(129, 0, $drop)]);
                            break;
                        case 153:
                            $drop = \mt_rand(2, 2+$lvl);
                            $e->setDrops([Item::get(406, 0, $drop)]);
                            break;
                    }
                    break;
            }
        }
    }
    public function onCommandMusic(CommandSender $sender, Command $cmd, $label, array $args) {//Credit For GlitchPlayer & Others taken from JukeboxPE and Remodded By ME LOL
        switch($cmd->getName()) {
            case "music":
            case "song":
                if(isset($args[0])) {
                    switch($args[0]) {
                        case "next":
                            $this->StartNewTask();
                            return true;
                            break;
                        case "stop":
                        case "pause":
                            if($sender->hasPermission("JukeboxPE.cmd.music")) {
                                $this->getServer()->getScheduler()->cancelTasks($this);
                            }else{
                                $sender->sendMessage(AFC::RED."No Permission");
                            }
                            return true;
                            break;
                        case "start":
                        case "play":
                            if($sender->hasPermission("JukeboxPE.cmd.music")) {
                                $this->StartNewTask();
                            }else{
                                $sender->sendMessage(AFC::RED."No Permission");
                            }
                            return true;
                            break;
                    }
                }else{
                    return false;
                }
                break;
        }
        return false;
    }

    public function CheckMusic() {//Credit For GlitchPlayer & Others taken from JukeboxPE and Remodded By ME LOL
        if($this->getDirCount($this->getPluginDir()) > 0 and $this->RandomFile($this->getPluginDir(),"nbs")) {
            return true;
        }
        return false;
    }

    public function getDirCount($PATH) {//Credit For GlitchPlayer & Others taken from JukeboxPE and Remodded By ME LOL
        $num = sizeof(scandir($PATH));
        $num = ($num>2)?$num-2:0;
        return $num;
    }

    public function getPluginDir() {//Credit For GlitchPlayer & Others taken from JukeboxPE and Remodded By ME LOL
        return $this->getServer()->getDataPath()."plugins/AFCore/Songs/";
    }

    public function getRandomMusic() {//Credit For GlitchPlayer & Others taken from JukeboxPE and Remodded By ME LOL
        $dir = $this->RandomFile($this->getPluginDir(),"nbs");
        if($dir) {
            $api = new JukeBoxAPI($this,$dir);
            return $api;
        }
        return false;
    }

    Public function RandomFile($folder='', $extensions='.*') {//Credit For GlitchPlayer & Others taken from JukeboxPE and Remodded By ME LOL
        $folder = trim($folder);
        $folder = ($folder == '') ? './' : $folder;
        if (!is_dir($folder)) {
            return false;
        }
        $files = array();
        if ($dir = @opendir($folder)) {
            while($file = readdir($dir)) {
                if (!preg_match('/^\.+$/', $file) and
                    preg_match('/\.('.$extensions.')$/', $file)) {
                    $files[] = $file;
                }
            }
            closedir($dir);
        }else{
            return false;
        }
        if (count($files) == 0) {
            return false;
        }
        mt_srand((double)microtime()*1000000);
        $rand = mt_rand(0, count($files)-1);
        if (!isset($files[$rand])) {
            return false;
        }
        if(function_exists("iconv")) {
            $rname = iconv('gbk','UTF-8',$files[$rand]);
        }else{
            $rname = $files[$rand];
        }
        $this->name = str_replace('.nbs', '', $rname);
        return $folder . $files[$rand];
    }

    public function getNearbyNoteBlock($x,$y,$z,$world) {//Credit For GlitchPlayer & Others taken from JukeboxPE and Remodded By ME LOL
        $nearby = [];
        $minX = $x - 5;
        $maxX = $x + 5;
        $minY = $y - 5;
        $maxY = $y + 5;
        $minZ = $z - 2;
        $maxZ = $z + 2;

        for($x = $minX; $x <= $maxX; ++$x) {
            for($y = $minY; $y <= $maxY; ++$y) {
                for($z = $minZ; $z <= $maxZ; ++$z) {
                    $v3 = new Vector3($x, $y, $z);
                    $block = $world->getBlock($v3);
                    if($block->getID() == 25) {
                        $nearby[] = $block;
                    }
                }
            }
        }
        return $nearby;
    }

    public function getFullBlock($x, $y, $z, $level) {//Credit For GlitchPlayer & Others taken from JukeboxPE and Remodded By ME LOL
        return $level->getChunk($x >> 4, $z >> 4, false)->getFullBlock($x & 0x0f, $y & 0x7f, $z & 0x0f);
    }

    public function Play($sound,$type = 0,$blo = 0) {//Credit For GlitchPlayer & Others taken from JukeboxPE and Remodded By ME LOL
        if(is_numeric($sound) and $sound > 0) {
            foreach($this->getServer()->getOnlinePlayers() as $p) {
                $noteblock = $this->getNearbyNoteBlock($p->x,$p->y,$p->z,$p->getLevel());
                $noteblock1 = $noteblock;
                if(!empty($noteblock)) {
                    $i = 0;
                    while ($i < $blo) {
                        if(current($noteblock)) {
                            next($noteblock);
                            $i ++;
                        }else{
                            $noteblock = $noteblock1;
                            $i ++;
                        }
                    }
                    $block = current($noteblock);
                    if($block) {
                        $pk = new BlockEventPacket();
                        $pk->x = $block->x;
                        $pk->y = $block->y;
                        $pk->z = $block->z;
                        $pk->case1 = $type;
                        $pk->case2 = $sound;
                        $p->dataPacket($pk);
                    }
                }
            }
        }
    }

    public function StartNewTask() {//Credit For GlitchPlayer & Others taken from JukeboxPE and Remodded By ME LOL
        $this->song = $this->getRandomMusic();
        $this->getServer()->getScheduler()->cancelTasks($this);
        $this->SongPlayer = new SongPlayer($this);
        $this->getServer()->getScheduler()->scheduleRepeatingTask($this->SongPlayer, 3000 / $this->song->speed );
    }

}
