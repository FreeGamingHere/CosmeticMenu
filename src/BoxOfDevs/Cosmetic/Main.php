<?php

namespace BoxOfDevs\Cosmetic;
use pocketmine\event\entity\ExplosionPrimeEvent;
use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\network\mcpe\protocol\UseItemPacket;
use pocketmine\math\Vector3;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\event\player\PlayerItemHeldEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\entity\ProjectileHitEvent;
use pocketmine\event\entity\ProjectileLaunchEvent;
use pocketmine\level\particle\RedstoneParticle;
use pocketmine\utils\Config;
use pocketmine\level\Level;
use pocketmine\scheduler\Task as PluginTask;
use pocketmine\level\particle\HugeExplodeParticle;
use pocketmine\level\particle\WaterParticle;
use pocketmine\level\particle\AngryVillagerParticle;
use pocketmine\entity\Arrow;
use pocketmine\entity\projectile\EnderPearl;
use pocketmine\utils\Random;
use pocketmine\entity\Snowball;
use pocketmine\nbt\tag\FloatTag;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\inventory\Inventory;
use pocketmine\nbt\tag\DoubleTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\entity\Entity;
use pocketmine\item\Item;
use pocketmine\Player;
use pocketmine\block\Air;
use pocketmine\network\mcpe\protocol\AddItemEntityPacket;
use pocketmine\event\player\PlayerRespawnEvent;

class Main extends PluginBase implements Listener {
    //Particles
    public $water = array("WaterParticles");
    public $fire = array("FireParticles");
    public $heart = array("HeartParticles");
    public $smoke = array("SmokeParticles");
    //EnderPearl
    /**@var Item*/
    private $item;
    /**@var int*/
    protected $damage = 0;
    public function onEnable() {
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        $this->getScheduler()->scheduleRepeatingTask(new Particles($this), 5);
        $this->getLogger()->info("§aCosmeticMenu by BoxOfDevs edited by FreeGamingHere enabled!");
    }
    public function onJoin(PlayerJoinEvent $event) {
        $player = $event->getPlayer();
        $inv = $player->getInventory();
        $inv->clearAll();
        $item = Item::get(345, 0, 1);
        $inv->setItem(0, $item);
    }
    public function playerSpawnEvent(PlayerRespawnEvent $ev) {
        $item = new Item(345, 0, 1);
        $ev->getPlayer()->getInventory()->addItem($item);
    }
    public function onInteract(PlayerInteractEvent $event) {
        $player = $event->getPlayer();
        $name = $player->getName();
        if ($player instanceof Player) {
            $block = $player->getLevel()->getBlock($player->floor()->subtract(0, 1));
            $item = $player->getInventory()->getItemInHand();
            $level = $player->getLevel();
            if ($item->getId() == 341) {
                $block = $event->getBlock();
                $pos = new Vector3($block->getX(), $block->getY() + 2, $block->getZ());
                $particle = new RedstoneParticle($pos, 5);
                $particle2 = new HugeExplodeParticle($pos, 5);
                $particle3 = new WaterParticle($pos, 50);
                $particle4 = new AngryVillagerParticle($pos, 15);
                $level->addParticle($particle);
                $level->addParticle($particle2);
                $level->addParticle($particle3);
                $level->addParticle($particle4);
            }
            //Leaper
            if ($block->getId() === 0) {
                $player->sendPopup("§cPlease wait");
                return true;
            }
            if ($item->getId() == 258) {
                $player->setMotion(new Vector3(0, 5, 0));
                $player->sendPopup("§aLeaped!");
            }
            //Egg Launcher
            if ($item->getId() == 329) {
                $nbt = new CompoundTag("", ["Pos" => new ListTag("Pos", [new DoubleTag("", $player->x), new DoubleTag("", $player->y + $player->getEyeHeight()), new DoubleTag("", $player->z) ]), "Motion" => new ListTag("Motion", [new DoubleTag("", -\sin($player->yaw / 180 * M_PI) * \cos($player->pitch / 180 * M_PI)), new DoubleTag("", -\sin($player->pitch / 180 * M_PI)), new DoubleTag("", \cos($player->yaw / 180 * M_PI) * \cos($player->pitch / 180 * M_PI)) ]), "Rotation" => new ListTag("Rotation", [new FloatTag("", $player->yaw), new FloatTag("", $player->pitch) ]) ]);
                $f = 1.0;
                $snowball = Entity::createEntity("Egg", $player->getLevel(), $nbt, $player);
                $snowball->setMotion($snowball->getMotion()->multiply($f));
                $snowball->spawnToAll();
            } // EnderPearl
            if ($item->getId() == 332) {
                $nbt = new CompoundTag("", ["Pos" => new ListTag("Pos", [new DoubleTag("", $player->x), new DoubleTag("", $player->y + $player->getEyeHeight()), new DoubleTag("", $player->z) ]), "Motion" => new ListTag("Motion", [new DoubleTag("", -\sin($player->yaw / 180 * M_PI) * \cos($player->pitch / 180 * M_PI)), new DoubleTag("", -\sin($player->pitch / 180 * M_PI)), new DoubleTag("", \cos($player->yaw / 180 * M_PI) * \cos($player->pitch / 180 * M_PI)) ]), "Rotation" => new ListTag("Rotation", [new FloatTag("", $player->yaw), new FloatTag("", $player->pitch) ]) ]);
                $f = 1.0;
                $snowball = Entity::createEntity("Snowball", $player->getLevel(), $nbt, $player);
                $snowball->setMotion($snowball->getMotion()->multiply($f));
                $snowball->spawnToAll();
            }
            if ($item->getId() === 351) { // Dye
                switch ($item->getDamage()) {
                    case 4: // lapis: water
                        if (!in_array($name, $this->water)) {
                            $this->water[] = $name;
                            if(in_array($name, $this->fire)) {
                                unset($this->fire[array_search($name, $this->fire)]);
                            } elseif(in_array($name, $this->heart)) {
                                unset($this->heart[array_search($name, $this->heart)]);
                            } elseif(in_array($name, $this->smoke)) {
                                unset($this->smoke[array_search($name, $this->smoke)]);
                            }
                            $player->sendMessage("§l§aYou have enabled your §6Water §aParticles");
                        } else {
                            unset($this->water[array_search($name, $this->water)]);
                            $player->sendMessage("§l§cYou have disabled your §6Water §cParticles");
                        }
                    break;
                    case 14: // orange: fire
                        if (!in_array($name, $this->fire)) {
                            $this->fire[] = $name;
                            if(in_array($name, $this->water)) {
                                unset($this->water[array_search($name, $this->water)]);
                            } elseif(in_array($name, $this->heart)) {
                                unset($this->heart[array_search($name, $this->heart)]);
                            } elseif(in_array($name, $this->smoke)) {
                                unset($this->smoke[array_search($name, $this->smoke)]);
                            }
                            $player->sendMessage("§l§aYou have enabled your §6Fire §aParticles");
                        } else {
                            unset($this->fire[array_search($name, $this->fire)]);
                            $player->sendMessage("§l§cYou have disabled your §6Fire §cParticles");
                        }
                    break;
                    case 1: // red: heart
                        if (!in_array($name, $this->heart)) {
                            $this->heart[] = $name;
                            if(in_array($name, $this->water)) {
                                unset($this->water[array_search($name, $this->water)]);
                            } elseif(in_array($name, $this->fire)) {
                                unset($this->fire[array_search($name, $this->fire)]);
                            } elseif(in_array($name, $this->smoke)) {
                                unset($this->smoke[array_search($name, $this->smoke)]);
                            }
                            $player->sendMessage("§l§aYou have enabled your §6Heart §aParticles");
                        } else {
                            unset($this->heart[array_search($name, $this->heart)]);
                            $player->sendMessage("§l§cYou have disabled your §6Heart §cParticles");
                        }
                    break;
                    case 15: // white: smoke
                        if (!in_array($name, $this->smoke)) {
                            $this->smoke[] = $name;
                            if(in_array($name, $this->water)) {
                                unset($this->water[array_search($name, $this->water)]);
                            } elseif(in_array($name, $this->fire)) {
                                unset($this->fire[array_search($name, $this->fire)]);
                            } elseif(in_array($name, $this->heart)) {
                                unset($this->heart[array_search($name, $this->heart)]);
                            }
                            $player->sendMessage("§l§aYou have enabled your §6Smoke §aParticles");
                        } else {
                            unset($this->smoke[array_search($name, $this->smoke)]);
                            $player->sendMessage("§l§cYou have disabled your §6Smoke §cParticles");
                        }
                    break;
                }
            }
            //TNTLauncher
            if ($item->getId() == 352) {
                foreach ($player->getInventory()->getContents() as $item) {
                    $nbt = new CompoundTag("", ["Pos" => new ListTag("Pos", [new DoubleTag("", $player->x), new DoubleTag("", $player->y + $player->getEyeHeight()), new DoubleTag("", $player->z) ]), "Motion" => new ListTag("Motion", [new DoubleTag("", -\sin($player->yaw / 180 * M_PI) * \cos($player->pitch / 180 * M_PI)), new DoubleTag("", -\sin($player->pitch / 180 * M_PI)), new DoubleTag("", \cos($player->yaw / 180 * M_PI) * \cos($player->pitch / 180 * M_PI)) ]), "Rotation" => new ListTag("Rotation", [new FloatTag("", $player->yaw), new FloatTag("", $player->pitch) ]) ]);
                    $f = 3.0;
                    $snowball = Entity::createEntity("PrimedTNT", $player->getLevel(), $nbt, $player);
                    $snowball->setMotion($snowball->getMotion()->multiply($f));
                    $snowball->spawnToAll();
                }
            }
            //Items
            if ($item->getId() == 345) {
                $player->getInventory()->removeItem(Item::get(ITEM::COMPASS));
                $player->getInventory()->addItem(Item::get(ITEM::MINECART));
                $player->getInventory()->addItem(Item::get(ITEM::PAINTING));
                $player->getInventory()->addItem(Item::get(ITEM::GLOWSTONE_DUST));
                $player->getInventory()->addItem(Item::get(ITEM::BRICK));
            }
            //Armours
            if ($item->getId() == 336) {
                $player->getInventory()->removeItem(Item::get(ITEM::BRICK));
                $player->getInventory()->removeItem(Item::get(ITEM::MINECART));
                $player->getInventory()->removeItem(Item::get(ITEM::PAINTING));
                $player->getInventory()->removeItem(Item::get(ITEM::GLOWSTONE_DUST));
                $player->getInventory()->addItem(Item::get(ITEM::DIAMOND_BLOCK));
                $player->getInventory()->addItem(Item::get(ITEM::IRON_BLOCK));
                $player->getInventory()->addItem(Item::get(ITEM::GUNPOWDER));
                $player->getInventory()->addItem(Item::get(ITEM::GOLD_BLOCK));
                $player->getInventory()->addItem(Item::get(ITEM::LEATHER));
            }
            //Diamond Armour
            if ($item->getid() == 57) {
                $player->getInventory()->removeItem(Item::get(ITEM::DIAMOND_BLOCK));
                $player->getInventory()->removeItem(Item::get(ITEM::COMPASS));
                $player->getInventory()->removeItem(Item::get(ITEM::IRON_BLOCK));
                $player->getInventory()->removeItem(Item::get(ITEM::GOLD_BLOCK));
                $player->getInventory()->removeItem(Item::get(ITEM::GUNPOWDER));
                $player->getInventory()->removeItem(Item::get(ITEM::LEATHER));
                $player->getInventory()->addItem(Item::get(ITEM::BED));
                $player->getArmorInventory()->setHelmet(Item::get(ITEM::DIAMOND_HELMET));
                $player->getArmorInventory()->setChestplate(Item::get(ITEM::DIAMOND_CHESTPLATE));
                $player->getArmorInventory()->setLeggings(Item::get(ITEM::DIAMOND_LEGGINGS));
                $player->getArmorInventory()->setBoots(Item::get(ITEM::DIAMOND_BOOTS));
            }
            //Iron Armour
            if ($item->getid() == 42) {
                $player->getInventory()->removeItem(Item::get(ITEM::DIAMOND_BLOCK));
                $player->getInventory()->removeItem(Item::get(ITEM::COMPASS));
                $player->getInventory()->removeItem(Item::get(ITEM::IRON_BLOCK));
                $player->getInventory()->removeItem(Item::get(ITEM::GOLD_BLOCK));
                $player->getInventory()->removeItem(Item::get(ITEM::GUNPOWDER));
                $player->getInventory()->removeItem(Item::get(ITEM::LEATHER));
                $player->getInventory()->addItem(Item::get(ITEM::BED));
                $player->getArmorInventory()->setHelmet(Item::get(ITEM::IRON_HELMET));
                $player->getArmorInventory()->setChestplate(Item::get(ITEM::IRON_CHESTPLATE));
                $player->getArmorInventory()->setLeggings(Item::get(ITEM::IRON_LEGGINGS));
                $player->getArmorInventory()->setBoots(Item::get(ITEM::IRON_BOOTS));
            }
            //Gold Armour
            if ($item->getid() == 41) {
                $player->getInventory()->removeItem(Item::get(ITEM::DIAMOND_BLOCK));
                $player->getInventory()->removeItem(Item::get(ITEM::COMPASS));
                $player->getInventory()->removeItem(Item::get(ITEM::IRON_BLOCK));
                $player->getInventory()->removeItem(Item::get(ITEM::GOLD_BLOCK));
                $player->getInventory()->removeItem(Item::get(ITEM::GUNPOWDER));
                $player->getInventory()->removeItem(Item::get(ITEM::LEATHER));
                $player->getInventory()->addItem(Item::get(ITEM::BED));
                $player->getArmorInventory()->setHelmet(Item::get(ITEM::GOLD_HELMET));
                $player->getArmorInventory()->setChestplate(Item::get(ITEM::GOLD_CHESTPLATE));
                $player->getArmorInventory()->setLeggings(Item::get(ITEM::GOLD_LEGGINGS));
                $player->getArmorInventory()->setBoots(Item::get(ITEM::GOLD_BOOTS));
            }
            //Chain Armour
            if ($item->getid() == 289) {
                $player->getInventory()->removeItem(Item::get(ITEM::DIAMOND_BLOCK));
                $player->getInventory()->removeItem(Item::get(ITEM::COMPASS));
                $player->getInventory()->removeItem(Item::get(ITEM::IRON_BLOCK));
                $player->getInventory()->removeItem(Item::get(ITEM::GOLD_BLOCK));
                $player->getInventory()->removeItem(Item::get(ITEM::GUNPOWDER));
                $player->getInventory()->removeItem(Item::get(ITEM::LEATHER));
                $player->getInventory()->addItem(Item::get(ITEM::BED));
                $player->getArmorInventory()->setHelmet(Item::get(ITEM::CHAIN_HELMET));
                $player->getArmorInventory()->setChestplate(Item::get(ITEM::CHAIN_CHESTPLATE));
                $player->getArmorInventory()->setLeggings(Item::get(ITEM::CHAIN_LEGGINGS));
                $player->getArmorInventory()->setBoots(Item::get(ITEM::CHAIN_BOOTS));
            }
            //Leather Armour
            if ($item->getid() == 334) {
                $player->getInventory()->removeItem(Item::get(ITEM::DIAMOND_BLOCK));
                $player->getInventory()->removeItem(Item::get(ITEM::COMPASS));
                $player->getInventory()->removeItem(Item::get(ITEM::IRON_BLOCK));
                $player->getInventory()->removeItem(Item::get(ITEM::GOLD_BLOCK));
                $player->getInventory()->removeItem(Item::get(ITEM::GUNPOWDER));
                $player->getInventory()->removeItem(Item::get(ITEM::LEATHER));
                $player->getInventory()->addItem(Item::get(ITEM::BED));
                $player->getArmorInventory()->setHelmet(Item::get(ITEM::LEATHER_CAP));
                $player->getArmorInventory()->setChestplate(Item::get(ITEM::LEATHER_TUNIC));
                $player->getArmorInventory()->setLeggings(Item::get(ITEM::LEATHER_PANTS));
                $player->getArmorInventory()->setBoots(Item::get(ITEM::LEATHER_BOOTS));
            }
            //Gadgets
            if ($item->getid() == 328) {
                $player->getInventory()->removeItem(Item::get(ITEM::COMPASS));
                $player->getInventory()->removeItem(Item::get(ITEM::MINECART));
                $player->getInventory()->removeItem(Item::get(ITEM::PAINTING));
                $player->getInventory()->removeItem(Item::get(ITEM::GLOWSTONE_DUST));
                $player->getInventory()->removeItem(Item::get(ITEM::BRICK));
                $player->getInventory()->addItem(Item::get(ITEM::BED));
                $player->getInventory()->addItem(Item::get(ITEM::SADDLE));
                $player->getInventory()->addItem(Item::get(ITEM::SLIMEBALL));
                $player->getInventory()->addItem(Item::get(ITEM::IRON_AXE));
                $player->getInventory()->addItem(Item::get(ITEM::ENDER_PEARL, 0, 1));
                $player->getInventory()->addItem(Item::get(ITEM::BONE));
            }
            //Hats
            if ($item->getid() == 321) {
                $player->getInventory()->removeItem(Item::get(ITEM::COMPASS));
                $player->getInventory()->removeItem(Item::get(ITEM::MINECART));
                $player->getInventory()->removeItem(Item::get(ITEM::PAINTING));
                $player->getInventory()->removeItem(Item::get(ITEM::GLOWSTONE_DUST));
                $player->getInventory()->removeItem(Item::get(ITEM::BRICK));
                $player->getInventory()->addItem(Item::get(ITEM::BED));
                $player->getInventory()->addItem(Item::get(ITEM::SEEDS));
                $player->getInventory()->addItem(Item::get(ITEM::STEAK));
                $player->getInventory()->addItem(Item::get(ITEM::COOKIE));
                $player->getInventory()->addItem(Item::get(ITEM::PAPER));
                $player->getInventory()->addItem(Item::get(ITEM::BUCKET));
            }
            //SeedsHat
            if ($item->getId() == 295) {
                $player->getArmorInventory()->setHelmet(Item::get(ITEM::SEEDS));
                $player->sendPopup("§l§aPlop!");
            }
            //SteakHat
            if ($item->getId() == 364) {
                $player->getArmorInventory()->setHelmet(Item::get(ITEM::STEAK));
                $player->sendPopup("§l§aPlop!");
            }
            //CookieHat
            if ($item->getId() == 357) {
                $player->getArmorInventory()->setHelmet(Item::get(ITEM::COOKIE));
                $player->sendPopup("§l§aPlop!");
            }
            //PaperHat
            if ($item->getId() == 339) {
                $player->getArmorInventory()->setHelmet(Item::get(ITEM::PAPER));
                $player->sendPopup("§l§aPlop!");
            }
            //BucketHat
            if ($item->getId() == 325) {
                $player->getArmorInventory()->setHelmet(Item::get(ITEM::BUCKET));
                $player->sendPopup("§l§aPlop!");
            }
            //Particle
            if ($item->getid() == 348) {
                $player->getInventory()->removeItem(Item::get(ITEM::COMPASS));
                $player->getInventory()->removeItem(Item::get(ITEM::MINECART));
                $player->getInventory()->removeItem(Item::get(ITEM::PAINTING));
                $player->getInventory()->removeItem(Item::get(ITEM::GLOWSTONE_DUST));
                $player->getInventory()->removeItem(Item::get(ITEM::BRICK));
                $player->getInventory()->addItem(Item::get(ITEM::BED));
                $player->getInventory()->addItem(Item::get(ITEM::DYE, 4, 1));
                $player->getInventory()->addItem(Item::get(ITEM::DYE, 14, 1));
                $player->getInventory()->addItem(Item::get(ITEM::DYE, 1, 1));
                $player->getInventory()->addItem(Item::get(ITEM::DYE, 15, 1));
            }
            //Back
            if ($item->getId() == 355) {
                $player->getInventory()->removeItem(Item::get(ITEM::BED));
                $player->getInventory()->removeItem(Item::get(ITEM::SLIMEBALL));
                $player->getInventory()->removeItem(Item::get(ITEM::ENDER_PEARL, 0, 10000));
                $player->getInventory()->removeItem(Item::get(ITEM::IRON_AXE));
                $player->getInventory()->removeItem(Item::get(ITEM::MINECART));
                $player->getInventory()->removeItem(Item::get(ITEM::PAINTING));
                $player->getInventory()->removeItem(Item::get(ITEM::GLOWSTONE));
                $player->getInventory()->removeItem(Item::get(ITEM::STEAK));
                $player->getInventory()->removeItem(Item::get(ITEM::SEEDS));
                $player->getInventory()->removeItem(Item::get(ITEM::COOKIE));
                $player->getInventory()->removeItem(Item::get(ITEM::PAPER));
                $player->getInventory()->removeItem(Item::get(ITEM::BUCKET));
                $player->getInventory()->removeItem(Item::get(ITEM::SADDLE));
                $player->getInventory()->removeItem(Item::get(ITEM::DYE, 15, 1));
                $player->getInventory()->removeItem(Item::get(ITEM::DYE, 4, 1));
                $player->getInventory()->removeItem(Item::get(ITEM::DYE, 1, 1));
                $player->getInventory()->removeItem(Item::get(ITEM::DYE, 14, 1));
                $player->getInventory()->removeItem(Item::get(ITEM::BONE));
                $player->getInventory()->addItem(Item::get(ITEM::COMPASS));
                $player->getArmorInventory()->setHelmet(Item::get(ITEM::AIR));
                $player->getArmorInventory()->setChestplate(Item::get(ITEM::AIR));
                $player->getArmorInventory()->setLeggings(Item::get(ITEM::AIR));
                $player->getArmorInventory()->setBoots(Item::get(ITEM::AIR));
            }
        }
    }
    public function onPlayerItemHeldEvent(PlayerItemHeldEvent $e) {
        $i = $e->getItem();
        $p = $e->getPlayer();
        //ItemNames
        if ($i->getId() == 345) {
            $p->sendPopup("§l§dCosmetic§eMenu");
        }
        //Gadgets
        if ($i->getId() == 328) {
            $p->sendPopup("§l§6Gadgets");
        }
        //EggLauncher
        if ($i->getId() == 329) {
            $p->sendPopup("§l§6Egg§bLauncher");
        }
        //EnderPearl
        if ($i->getId() == 368) {
            $p->sendPopup("§l§dEnderPearl");
        }
        //BunnyHop
        if ($i->getId() == 258) {
            $p->sendPopup("§l§bBunnyHop");
        }
        //FlyTime
        if ($i->getId() == 288) {
            $p->sendPopup("§l§6FlyTime");
        }
        //ParticleBomb
        if ($i->getId() == 341) {
            $p->sendPopup("§l§dParticle§eBomb");
        }
        //Armors
        if ($i->getId() == 336) {
            $p->sendPopup("§l§dArmors");
        }
        //LightningStick
        if ($i->getId() == 352) {
            $p->sendPopup("§l§6Lighting§aStick");
        }
        //Partical
        if ($i->getId() == 348) {
            $p->sendPopup("§l§bParticles");
        }
        //Water
        if ($i->getId() == 351 && $i->getDamage() == 4) {
            $p->sendPopup("§l§6Water");
        }
        //Fire
        if ($i->getId() == 351 && $i->getDamage() == 14) {
            $p->sendPopup("§l§6Fire");
        }
        //Hearts
        if ($i->getId() == 351 && $i->getDamage() == 1) {
            $p->sendPopup("§l§6Hearts");
        }
        //Smoke
        if ($i->getId() == 351 && $i->getDamage() == 15) {
            $p->sendPopup("§l§6Smoke");
        }
        //Back
        if ($i->getId() == 355) {
            $p->sendPopup("§l§7Back...");
        }
        //TNTLauncher
        if ($i->getId() == 352) {
            $p->sendPopup("§l§cTNT§aLauncher");
        }
        //Diamond Armor
        if ($i->getId() == 57) {
            $p->sendPopup("§l§bDiamond §dArmor");
        }
        //Iron Armor
        if ($i->getId() == 42) {
            $p->sendPopup("§l§fIron §dArmor");
        }
        //Chain Armor
        if ($i->getId() == 289) {
            $p->sendPopup("§l§7Chain §dArmor");
        }
        //Gold Armor
        if ($i->getId() == 41) {
            $p->sendPopup("§l§6Gold §dArmor");
        }
        //Leather Armor
        if ($i->getId() == 334) {
            $p->sendPopup("§l§4Leather §dArmor");
        }
        //SeedsHat
        if ($i->getId() == 295) {
            $p->sendPopup("§l§3Seeds §eHat");
        }
        //SteakHat
        if ($i->getId() == 364) {
            $p->sendPopup("§l§4Steak §eHat");
        }
        //CookieHat
        if ($i->getId() == 357) {
            $p->sendPopup("§l§6Cookie §eHat");
        }
        //PaperHat
        if ($i->getId() == 339) {
            $p->sendPopup("§l§fPaper §eHat");
        }
        //BucketHat
        if ($i->getId() == 325) {
            $p->sendPopup("§l§7Bucket §eHat");
        }
        //Hats
        if ($i->getId() == 321) {
            $p->sendPopup("§l§eHat");
        }
    }
    public function onProjectileHit(ProjectileHitEvent $event) {
        $snowball = $event->getEntity();
        $loc = $snowball->getLocation();
        if ($snowball->getOwningEntity() instanceof Player and $snowball instanceof EnderPearl) { // If the player is online
//            $snowball->getOwningEntity()->teleport(new Vector3($loc->x, $loc->y, $loc->z), $loc->yaw, $loc->pitch);
            $snowball->getOwningEntity()->getInventory()->addItem(Item::get(ITEM::ENDER_PEARL, 0, 1));
        }
    }
    /**
     * FlyPower
     * LightingStick
     * Particals-Emerald is asigned on this
     */
    public function ExplosionPrimeEvent(ExplosionPrimeEvent $p) {
        $p->setBlockBreaking(false);
    }
}
