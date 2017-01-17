<?php

namespace _64FF00\PureChat;

use _64FF00\PureChat\Errors\ErrorHelper;
use _64FF00\PureChat\Tags\CustomTagExample;
use _64FF00\PureChat\Tags\CustomTagInterface;
use _64FF00\PurePerms\PPGroup;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\Player;
use pocketmine\plugin\Plugin;
use pocketmine\plugin\PluginBase;
use pocketmine\scheduler\PluginTask;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat;
use specter\api\DummyPlayer;

class PureChat extends PluginBase
{
  /*
      PureChat by 64FF00 (Twitter: @64FF00)

        888  888    .d8888b.      d8888  8888888888 8888888888 .d8888b.   .d8888b.
        888  888   d88P  Y88b    d8P888  888        888       d88P  Y88b d88P  Y88b
      888888888888 888          d8P 888  888        888       888    888 888    888
        888  888   888d888b.   d8P  888  8888888    8888888   888    888 888    888
        888  888   888P "Y88b d88   888  888        888       888    888 888    888
      888888888888 888    888 8888888888 888        888       888    888 888    888
        888  888   Y88b  d88P       888  888        888       Y88b  d88P Y88b  d88P
        888  888    "Y8888P"        888  888        888        "Y8888P"   "Y8888P"
  */

  const MAIN_PREFIX = "\x5b\x50\x75\x72\x65\x43\x68\x61\x74\x3a\x36\x34\x46\x46\x30\x30\x5d";

  /** @var Config $config */
  private $config;

  /** @var \_64FF00\PurePerms\PurePerms $purePerms */
  private $purePerms;

  /** @var CustomTagInterface[] $customTags */
  private $customTags = [];

  private $player;

  public function onLoad()
  {
    $this->saveDefaultConfig();

    $this->config = new Config($this->getDataFolder() . "config.yml", Config::YAML);

    if (!$this->config->get("version")) {
      $version = $this->getDescription()->getVersion();

      $this->config->set("version", $version);

      $this->fixOldConfig();
    }

    $this->purePerms = $this->getServer()->getPluginManager()->getPlugin("PurePerms");
  }

  public function onEnable()
  {
    $this->getServer()->getPluginManager()->registerEvents(new PCListener($this), $this);
    $this->registerCustomTag(new CustomTagExample(), true);

    $this->getServer()->getScheduler()->scheduleDelayedTask(
      new class($this) extends PluginTask
      {
        /** @var PureChat */
        protected $owner;

        public function __construct(Plugin $owner) { parent::__construct($owner); }

        public function onRun($currentTick) { $this->owner->delayedStart(); }
      }
      , 60);
  }

  public function delayedStart()
  {
    $this->player = new DummyPlayer("Debug", "DEBUG", 19132);
    echo "\ndelayedStart:\n";
    $ret = $this->applyCustomTags("{not_found} {test_test}", $this->player->getPlayer());
    echo "RET:\n";
    print_r($ret);
  }

  /**
   * @param CommandSender $sender
   * @param Command $cmd
   * @param string $label
   * @param array $args
   */
  public function onCommand(CommandSender $sender, Command $cmd, $label, array $args)
  {
    switch (strtolower($cmd->getName())) {
      case "setformat":

        if (count($args) < 3) {
          $sender->sendMessage(TextFormat::GREEN . self::MAIN_PREFIX . " Usage: /setformat <group> <world> <format>");

          return true;
        }

        $group = $this->purePerms->getGroup($args[0]);

        if ($group === null) {
          $sender->sendMessage(TextFormat::RED . self::MAIN_PREFIX . " Group " . $args[0] . "does NOT exist.");

          return true;
        }

        $levelName = null;

        if ($args[1] !== "null" and $args[1] !== "global") {
          /** @var \pocketmine\level\Level $level */
          $level = $this->getServer()->getLevelByName($args[1]);

          if ($level === null) {
            $sender->sendMessage(TextFormat::RED . self::MAIN_PREFIX . " Invalid World Name!");

            return true;
          }

          $levelName = $level->getName();
        }

        $chatFormat = implode(" ", array_slice($args, 2));

        $this->setOriginalChatFormat($group, $chatFormat, $levelName);

        $sender->sendMessage(TextFormat::GREEN . self::MAIN_PREFIX . " You set the chat format of the group to " . $chatFormat . ".");

        break;

      case "setnametag":

        if (count($args) < 3) {
          $sender->sendMessage(TextFormat::GREEN . self::MAIN_PREFIX . " Usage: /setnametag <group> <world> <format>");

          return true;
        }

        $group = $this->purePerms->getGroup($args[0]);

        if ($group === null) {
          $sender->sendMessage(TextFormat::RED . self::MAIN_PREFIX . " Group " . $args[0] . "does NOT exist.");

          return true;
        }

        $levelName = null;

        if ($args[1] !== "null" and $args[1] !== "global") {
          /** @var \pocketmine\level\Level $level */
          $level = $this->getServer()->getLevelByName($args[1]);

          if ($level === null) {
            $sender->sendMessage(TextFormat::RED . self::MAIN_PREFIX . " Invalid World Name!");

            return true;
          }

          $levelName = $level->getName();
        }

        $nameTag = implode(" ", array_slice($args, 2));

        $this->setOriginalNametag($group, $nameTag, $levelName);

        $sender->sendMessage(TextFormat::GREEN . self::MAIN_PREFIX . " You set the nametag of the group to " . $nameTag . ".");

        break;

      case "setprefix":

        if (!$sender instanceof Player) {
          $sender->sendMessage(TextFormat::GREEN . self::MAIN_PREFIX . " This command can be only used in-game.");

          return true;
        }

        if (!isset($args[0])) {
          $sender->sendMessage(TextFormat::GREEN . self::MAIN_PREFIX . " Usage: /setprefix <prefix>");

          return true;
        }

        $levelName = $this->config->get("enable-multiworld-chat") ? $sender->getLevel()->getName() : null;

        $prefix = str_replace("{BLANK}", ' ', implode('', $args));

        $this->setPrefix($prefix, $sender, $levelName);

        $sender->sendMessage(TextFormat::GREEN . self::MAIN_PREFIX . " You set your prefix to " . $prefix . ".");

        break;

      case "setsuffix":

        if (!$sender instanceof Player) {
          $sender->sendMessage(TextFormat::GREEN . self::MAIN_PREFIX . " This command can be only used in-game.");

          return true;
        }

        if (!isset($args[0])) {
          $sender->sendMessage(TextFormat::GREEN . self::MAIN_PREFIX . " Usage: /setsuffix <suffix>");

          return true;
        }

        $levelName = $this->config->get("enable-multiworld-chat") ? $sender->getLevel()->getName() : null;

        $suffix = str_replace("{BLANK}", ' ', implode('', $args));

        $this->setSuffix($suffix, $sender, $levelName);

        $sender->sendMessage(TextFormat::GREEN . self::MAIN_PREFIX . " You set your suffix to " . $suffix . ".");

        break;
    }

    return true;
  }

  private function fixOldConfig()
  {
    $tempData = $this->config->getAll();

    $version = $this->getDescription()->getVersion();

    $tempData["version"] = $version;

    if (!isset($tempData["default-factions-plugin"]))
      $tempData["default-factions-plugin"] = null;

    if (isset($tempData["enable-multiworld-support"])) {
      $tempData["enable-multiworld-chat"] = $tempData["enable-multiworld-support"];

      unset($tempData["enable-multiworld-support"]);
    }

    if (isset($tempData["custom-no-fac-message"]))
      unset($tempData["custom-no-fac-message"]);

    if (isset($tempData["groups"])) {
      foreach ($tempData["groups"] as $groupName => $tempGroupData) {
        if (isset($tempGroupData["default-chat"])) {
          $tempGroupData["chat"] = $this->fixOldData($tempGroupData["default-chat"]);

          unset($tempGroupData["default-chat"]);
        }

        if (isset($tempGroupData["default-nametag"])) {
          $tempGroupData["nametag"] = $this->fixOldData($tempGroupData["default-nametag"]);

          unset($tempGroupData["default-nametag"]);
        }

        if (isset($tempGroupData["worlds"])) {
          foreach ($tempGroupData["worlds"] as $worldName => $worldData) {
            if (isset($worldData["default-chat"])) {
              $worldData["chat"] = $this->fixOldData($worldData["default-chat"]);

              unset($worldData["default-chat"]);
            }

            if (isset($worldData["default-nametag"])) {
              $worldData["nametag"] = $this->fixOldData($worldData["default-nametag"]);

              unset($worldData["default-nametag"]);
            }

            $tempGroupData["worlds"][$worldName] = $worldData;
          }
        }

        $tempData["groups"][$groupName] = $tempGroupData;
      }
    }

    $this->config->setAll($tempData);
    $this->config->save();

    $this->config->reload();

    $this->getLogger()->notice("Upgraded PureChat config.yml to the latest version");
  }

  /**
   * @param $string
   * @return mixed
   */
  private function fixOldData($string)
  {
    $string = str_replace("{COLOR_BLACK}", "&0", $string);
    $string = str_replace("{COLOR_DARK_BLUE}", "&1", $string);
    $string = str_replace("{COLOR_DARK_GREEN}", "&2", $string);
    $string = str_replace("{COLOR_DARK_AQUA}", "&3", $string);
    $string = str_replace("{COLOR_DARK_RED}", "&4", $string);
    $string = str_replace("{COLOR_DARK_PURPLE}", "&5", $string);
    $string = str_replace("{COLOR_GOLD}", "&6", $string);
    $string = str_replace("{COLOR_GRAY}", "&7", $string);
    $string = str_replace("{COLOR_DARK_GRAY}", "&8", $string);
    $string = str_replace("{COLOR_BLUE}", "&9", $string);
    $string = str_replace("{COLOR_GREEN}", "&a", $string);
    $string = str_replace("{COLOR_AQUA}", "&b", $string);
    $string = str_replace("{COLOR_RED}", "&c", $string);
    $string = str_replace("{COLOR_LIGHT_PURPLE}", "&d", $string);
    $string = str_replace("{COLOR_YELLOW}", "&e", $string);
    $string = str_replace("{COLOR_WHITE}", "&f", $string);

    $string = str_replace("{FORMAT_OBFUSCATED}", "&k", $string);
    $string = str_replace("{FORMAT_BOLD}", "&l", $string);
    $string = str_replace("{FORMAT_STRIKETHROUGH}", "&m", $string);
    $string = str_replace("{FORMAT_UNDERLINE}", "&n", $string);
    $string = str_replace("{FORMAT_ITALIC}", "&o", $string);
    $string = str_replace("{FORMAT_RESET}", "&r", $string);

    $string = str_replace("{world_name}", "{world}", $string);
    $string = str_replace("{faction}", "{fac_rank}{fac_name}", $string);
    $string = str_replace("{user_name}", "{display_name}", $string);
    $string = str_replace("{message}", "{msg}", $string);

    return $string;
  }

  /*
        888  888          d8888 8888888b. 8888888
        888  888         d88888 888   Y88b  888
      888888888888      d88P888 888    888  888
        888  888       d88P 888 888   d88P  888
        888  888      d88P  888 8888888P"   888
      888888888888   d88P   888 888         888
        888  888    d8888888888 888         888
        888  888   d88P     888 888       8888888
  */

  /**
   * @param $string
   * @return mixed
   */
  public function applyColors($string)
  {
    $string = str_replace("&0", TextFormat::BLACK, $string);
    $string = str_replace("&1", TextFormat::DARK_BLUE, $string);
    $string = str_replace("&2", TextFormat::DARK_GREEN, $string);
    $string = str_replace("&3", TextFormat::DARK_AQUA, $string);
    $string = str_replace("&4", TextFormat::DARK_RED, $string);
    $string = str_replace("&5", TextFormat::DARK_PURPLE, $string);
    $string = str_replace("&6", TextFormat::GOLD, $string);
    $string = str_replace("&7", TextFormat::GRAY, $string);
    $string = str_replace("&8", TextFormat::DARK_GRAY, $string);
    $string = str_replace("&9", TextFormat::BLUE, $string);
    $string = str_replace("&a", TextFormat::GREEN, $string);
    $string = str_replace("&b", TextFormat::AQUA, $string);
    $string = str_replace("&c", TextFormat::RED, $string);
    $string = str_replace("&d", TextFormat::LIGHT_PURPLE, $string);
    $string = str_replace("&e", TextFormat::YELLOW, $string);
    $string = str_replace("&f", TextFormat::WHITE, $string);
    $string = str_replace("&k", TextFormat::OBFUSCATED, $string);
    $string = str_replace("&l", TextFormat::BOLD, $string);
    $string = str_replace("&m", TextFormat::STRIKETHROUGH, $string);
    $string = str_replace("&n", TextFormat::UNDERLINE, $string);
    $string = str_replace("&o", TextFormat::ITALIC, $string);
    $string = str_replace("&r", TextFormat::RESET, $string);

    return $string;
  }

  /**
   * @param $string
   * @param Player $player
   * @param $message
   * @param null $levelName
   * @param $msg bool
   * @return mixed
   */
  public function applyPCTags($string, Player $player, $message, $levelName, $msg = false)
  {
    $string = str_replace("{display_name}", $player->getDisplayName(), $string);
    $string = str_replace("{world}", ($levelName === null ? "" : $levelName), $string);
    $string = str_replace("{prefix}", $this->getPrefix($player, $levelName), $string);
    $string = str_replace("{suffix}", $this->getSuffix($player, $levelName), $string);

    if ($msg) $string = $this->applyMsg($string, $player, $message);

    return $string;
  }

  public function registerCustomTag(CustomTagInterface $tag, $quite = false, &$detail = null)
  {
    $prefix = strtolower($tag->getPrefix());

    if ($prefix !== $tag->getPrefix()) {
      $detail = ["Error" => true, "Reason" => "Prefix must be lowercase"];
      $tag->onError(ErrorHelper::not_lowercased);
      if (!$quite) {
        throw new \Exception("Prefix must be lowercase");
      }
      return false;
    }
    if (preg_match("/^[a-z]{3,}$/", $tag->getPrefix()) !== 1) {
      $detail = ["Error" => true, "Reason" => "Prefix must be lowercased letters only and at least 3 character"];
      $tag->onError(ErrorHelper::invalid_char);
      if (!$quite) throw new \Exception("Prefix must be lowercased letters only and at least 3 character");
      return false;
    }

    $usedPrefix = ['display'];
    /** @var CustomTagInterface $cTag */
    foreach ($this->customTags as $cTag) $usedPrefix[] = $cTag->getPrefix();

    if (in_array($prefix, $usedPrefix)) {
      $detail = ["Error" => true, "Reason" => "Cannot Register Used Prefix"];
      $tag->onError(ErrorHelper::prefix_used);
      if (!$quite) throw new \Exception("Cannot Register Used Prefix");
      return false;
    }

    //echo "\n\nPrefixes:\n" . print_r($tag->getAllTags(), true) . "\n\n";

    foreach ($tag->getAllTags() as $suffix => $function) {
      if ($suffix !== strtolower($suffix)) {
        $detail = ["Error" => true, "Reason" => "Sufix must be lowercase"];
        $tag->onError(ErrorHelper::not_lowercased);
        if (!$quite) throw new \Exception("Sufix must be lowercase");
        return false;
      }
      if (preg_match("/^[a-z]{3,}$/", $suffix) !== 1) {
        $detail = ["Error" => true, "Reason" => "Suffix must be letters only and at least 3 character"];
        $tag->onError(ErrorHelper::invalid_char);
        if (!$quite) throw new \Exception("Suffix must be letters only and at least 3 character");
        return false;
      }
      if (!is_callable([$tag, $function])) {
        $detail = ["Error" => true, "Reason" => "Suffix function uncallable"];
        $tag->onError(ErrorHelper::suffix_func_invalid);
        if (!$quite) throw new \Exception("Suffix function uncallable");
        return false;
      }
      //maybe reflector to check if it only need Player ??
      //maybe do a test call to all functions and check their return
    }
    $this->customTags[] = $tag; //need a new way to store things that arent too hacky
    $tags = '';
    foreach ($tag->getAllTags() as $suffix => $func) $tags .= "$suffix ";
    $this->getLogger()->debug("Successfully registered {$tag->getPrefix()}_ with " . count($tag->getAllTags()) . " tags ($tags)");
    return true;
  }

  private function registerCustomTags() { }//todo

  public function applyCustomTags(string $string, Player $player)
  {
    foreach ($this->customTags as $ref => $ctag)
      foreach ($ctag->getAllTags() as $suffix => $func) {
        $suffix = '{' . $ctag->getPrefix() . "_" . $suffix . '}';
        if (strpos($string, $suffix) !== false) //yay remove un nessary calls
          try {
            $call = call_user_func([$ctag, $func], $player);
            if (!is_string($call) AND !is_numeric($call)) {
              $this->getLogger()->debug("Removing X CustomTag due to invalid return\n");
              $ctag->onRemove(ErrorHelper::proc_invalid_ret);
              unset($this->customTags[$ref]);
              continue;
            }
            $string = str_replace($suffix, $call, $string);
          } catch (\Exception$exception) {//todo try only catch error that will cause PM to kill the plugin and ignore general errors
            $this->getLogger()->debug("Removing X CustomTag due to unknow thrown exception\n ERROR: {$exception->getMessage()}");
            $ctag->onRemove(ErrorHelper::proc_throw);
            unset($this->customTags[$ref]);
          }
      }

    return $string;
  }

  public function applyMsg($string, Player $player, $message)
  {
    if ($message === null) $message = "";

    if ($player->hasPermission("pchat.coloredMessages")) {
      $string = str_replace("{msg}", $this->applyColors($message), $string);
    } else {
      $string = str_replace("{msg}", $this->stripColors($message), $string);
    }
    return $string;
  }

  /**
   * @param Player $player
   * @param $message
   * @param null $levelName
   * @return mixed
   */
  public function getChatFormat(Player $player, $message, $levelName = null)
  {
    $originalChatFormat = $this->getOriginalChatFormat($player, $levelName);

    $chatFormat = $this->applyColors($originalChatFormat);
    $chatFormat = $this->applyPCTags($chatFormat, $player, $message, $levelName);
    $chatFormat = $this->applyCustomTags($chatFormat, $player);//
    $chatFormat = $this->applyMsg($chatFormat, $player, $message);
    return $chatFormat;
  }

  /**
   * @param Player $player
   * @param null $levelName
   * @return mixed
   */
  public function getNametag(Player $player, $levelName = null)
  {
    $originalNametag = $this->getOriginalNametag($player, $levelName);

    $nameTag = $this->applyColors($originalNametag);
    $nameTag = $this->applyPCTags($nameTag, $player, null, $levelName);
    $nameTag = $this->applyCustomTags($nameTag, $player);
    return $nameTag;
  }

  /**
   * @param Player $player
   * @param null $levelName
   * @return mixed
   */
  public function getOriginalChatFormat(Player $player, $levelName = null)
  {
    /** @var \_64FF00\PurePerms\PPGroup $group */
    $group = $this->purePerms->getUserDataMgr()->getGroup($player, $levelName);

    if ($levelName === null) {
      if ($this->config->getNested("groups." . $group->getName() . ".chat") === null) {
        $this->getLogger()->critical("Invalid chat format found in config.yml (Group: " . $group->getName() . ") / Setting it to default value.");

        $this->config->setNested("groups." . $group->getName() . ".chat", "&8&l[" . $group->getName() . "]&f&r {display_name} &7> {msg}");

        $this->config->save();
        $this->config->reload();
      }

      return $this->config->getNested("groups." . $group->getName() . ".chat");
    } else {
      if ($this->config->getNested("groups." . $group->getName() . "worlds.$levelName.chat") === null) {
        $this->getLogger()->critical("Invalid chat format found in config.yml (Group: " . $group->getName() . ", WorldName = $levelName) / Setting it to default value.");

        $this->config->setNested("groups." . $group->getName() . "worlds.$levelName.chat", "&8&l[" . $group->getName() . "]&f&r {display_name} &7> {msg}");

        $this->config->save();
        $this->config->reload();
      }

      return $this->config->getNested("groups." . $group->getName() . "worlds.$levelName.chat");
    }
  }

  public function getOriginalNametag(Player $player, $levelName = null)
  {
    /** @var \_64FF00\PurePerms\PPGroup $group */
    $group = $this->purePerms->getUserDataMgr()->getGroup($player, $levelName);

    if ($levelName === null) {
      if ($this->config->getNested("groups." . $group->getName() . ".nametag") === null) {
        $this->getLogger()->critical("Invalid nametag found in config.yml (Group: " . $group->getName() . ") / Setting it to default value.");

        $this->config->setNested("groups." . $group->getName() . ".nametag", "&8&l[" . $group->getName() . "]&f&r {display_name}");

        $this->config->save();
        $this->config->reload();
      }

      return $this->config->getNested("groups." . $group->getName() . ".nametag");
    } else {
      if ($this->config->getNested("groups." . $group->getName() . "worlds.$levelName.nametag") === null) {
        $this->getLogger()->critical("Invalid nametag found in config.yml (Group: " . $group->getName() . ", WorldName = $levelName) / Setting it to default value.");

        $this->config->setNested("groups." . $group->getName() . "worlds.$levelName.nametag", "&8&l[" . $group->getName() . "]&f&r {display_name}");

        $this->config->save();
        $this->config->reload();
      }

      return $this->config->getNested("groups." . $group->getName() . "worlds.$levelName.nametag");
    }
  }

  /**
   * @param Player $player
   * @param null $levelName
   * @return mixed|null|string
   */
  public function getPrefix(Player $player, $levelName = null)
  {
    if ($levelName === null) {
      return $this->purePerms->getUserDataMgr()->getNode($player, "prefix");
    } else {
      $worldData = $this->purePerms->getUserDataMgr()->getWorldData($player, $levelName);

      if (!isset($worldData["prefix"]) || $worldData["prefix"] === null)
        return "";

      return $worldData["prefix"];
    }
  }

  /**
   * @param Player $player
   * @param null $levelName
   * @return mixed|null|string
   */
  public function getSuffix(Player $player, $levelName = null)
  {
    if ($levelName === null) {
      return $this->purePerms->getUserDataMgr()->getNode($player, "suffix");
    } else {
      $worldData = $this->purePerms->getUserDataMgr()->getWorldData($player, $levelName);

      if (!isset($worldData["suffix"]) || $worldData["suffix"] === null)
        return "";

      return $worldData["suffix"];
    }
  }

  /**
   * @param PPGroup $group
   * @param $chatFormat
   * @param null $levelName
   * @return bool
   */
  public function setOriginalChatFormat(PPGroup $group, $chatFormat, $levelName = null)
  {
    if ($levelName === null) {
      $this->config->setNested("groups." . $group->getName() . ".chat", $chatFormat);
    } else {
      $this->config->setNested("groups." . $group->getName() . "worlds.$levelName.chat", $chatFormat);
    }

    $this->config->save();

    $this->config->reload();

    return true;
  }

  /**
   * @param PPGroup $group
   * @param $nameTag
   * @param null $levelName
   * @return bool
   */
  public function setOriginalNametag(PPGroup $group, $nameTag, $levelName = null)
  {
    if ($levelName === null) {
      $this->config->setNested("groups." . $group->getName() . ".nametag", $nameTag);
    } else {
      $this->config->setNested("groups." . $group->getName() . "worlds.$levelName.nametag", $nameTag);
    }

    $this->config->save();

    $this->config->reload();

    return true;
  }

  /**
   * @param $prefix
   * @param Player $player
   * @param null $levelName
   * @return bool
   */
  public function setPrefix($prefix, Player $player, $levelName = null)
  {
    if ($levelName === null) {
      $this->purePerms->getUserDataMgr()->setNode($player, "prefix", $prefix);
    } else {
      $worldData = $this->purePerms->getUserDataMgr()->getWorldData($player, $levelName);

      $worldData["prefix"] = $prefix;

      $this->purePerms->getUserDataMgr()->setWorldData($player, $levelName, $worldData);
    }

    return true;
  }

  /**
   * @param $suffix
   * @param Player $player
   * @param null $levelName
   * @return bool
   */
  public function setSuffix($suffix, Player $player, $levelName = null)
  {
    if ($levelName === null) {
      $this->purePerms->getUserDataMgr()->setNode($player, "suffix", $suffix);
    } else {
      $worldData = $this->purePerms->getUserDataMgr()->getWorldData($player, $levelName);

      $worldData["suffix"] = $suffix;

      $this->purePerms->getUserDataMgr()->setWorldData($player, $levelName, $worldData);
    }

    return true;
  }

  /**
   * @param $string
   * @return mixed
   */
  public function stripColors($string)
  {
    $string = str_replace(TextFormat::BLACK, '', $string);
    $string = str_replace(TextFormat::DARK_BLUE, '', $string);
    $string = str_replace(TextFormat::DARK_GREEN, '', $string);
    $string = str_replace(TextFormat::DARK_AQUA, '', $string);
    $string = str_replace(TextFormat::DARK_RED, '', $string);
    $string = str_replace(TextFormat::DARK_PURPLE, '', $string);
    $string = str_replace(TextFormat::GOLD, '', $string);
    $string = str_replace(TextFormat::GRAY, '', $string);
    $string = str_replace(TextFormat::DARK_GRAY, '', $string);
    $string = str_replace(TextFormat::BLUE, '', $string);
    $string = str_replace(TextFormat::GREEN, '', $string);
    $string = str_replace(TextFormat::AQUA, '', $string);
    $string = str_replace(TextFormat::RED, '', $string);
    $string = str_replace(TextFormat::LIGHT_PURPLE, '', $string);
    $string = str_replace(TextFormat::YELLOW, '', $string);
    $string = str_replace(TextFormat::WHITE, '', $string);
    $string = str_replace(TextFormat::OBFUSCATED, '', $string);
    $string = str_replace(TextFormat::BOLD, '', $string);
    $string = str_replace(TextFormat::STRIKETHROUGH, '', $string);
    $string = str_replace(TextFormat::UNDERLINE, '', $string);
    $string = str_replace(TextFormat::ITALIC, '', $string);
    $string = str_replace(TextFormat::RESET, '', $string);

    return $string;
  }
}
