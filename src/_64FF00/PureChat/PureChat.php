<?php

namespace _64FF00\PureChat;

use pocketmine\Player;

use pocketmine\plugin\PluginBase;

use pocketmine\utils\TextFormat;

/* PureChat by 64FF00 (xktiverz@gmail.com, @64ff00 for Twitter) */
/*
      # #    #####  #       ####### #######   ###     ###   
      # #   #     # #    #  #       #        #   #   #   #  
    ####### #       #    #  #       #       #     # #     # 
      # #   ######  #    #  #####   #####   #     # #     # 
    ####### #     # ####### #       #       #     # #     # 
      # #   #     #      #  #       #        #   #   #   #  
      # #    #####       #  #       #         ###     ###                                        
                                                                                       
*/

class PureChat extends PluginBase
{
    private $factionsPro;
    
    public function onLoad()
    {
        $this->saveDefaultConfig();
        
        if($this->getConfig()->getNested("enable-multiworld-support"))
        {
            $this->getLogger()->notice("Successfully enabled PureChat multiworld support");
        }
    }
    
    public function onEnable()
    {
        $this->PurePerms = $this->getServer()->getPluginManager()->getPlugin("PurePerms");
        $this->factionsPro = $this->getServer()->getPluginManager()->getPlugin("FactionsPro");
        
        $this->getServer()->getPluginManager()->registerEvents(new ChatListener($this), $this);
    }
    
    public function onDisable()
    {
    }

    /**
     * @param $pChatFormat
     * @return mixed
     */
    public function addColors($pChatFormat)
    {
        $pChatFormat = str_replace("{COLOR_BLACK}", TextFormat::BLACK, $pChatFormat);
        $pChatFormat = str_replace("{COLOR_DARK_BLUE}", TextFormat::DARK_BLUE, $pChatFormat);
        $pChatFormat = str_replace("{COLOR_DARK_GREEN}", TextFormat::DARK_GREEN, $pChatFormat);
        $pChatFormat = str_replace("{COLOR_DARK_AQUA}", TextFormat::DARK_AQUA, $pChatFormat);
        $pChatFormat = str_replace("{COLOR_DARK_RED}", TextFormat::DARK_RED, $pChatFormat);
        $pChatFormat = str_replace("{COLOR_DARK_PURPLE}", TextFormat::DARK_PURPLE, $pChatFormat);
        $pChatFormat = str_replace("{COLOR_GOLD}", TextFormat::GOLD, $pChatFormat);
        $pChatFormat = str_replace("{COLOR_GRAY}", TextFormat::GRAY, $pChatFormat);
        $pChatFormat = str_replace("{COLOR_DARK_GRAY}", TextFormat::DARK_GRAY, $pChatFormat);
        $pChatFormat = str_replace("{COLOR_BLUE}", TextFormat::BLUE, $pChatFormat);
        $pChatFormat = str_replace("{COLOR_GREEN}", TextFormat::GREEN, $pChatFormat);
        $pChatFormat = str_replace("{COLOR_AQUA}", TextFormat::AQUA, $pChatFormat);
        $pChatFormat = str_replace("{COLOR_RED}", TextFormat::RED, $pChatFormat);
        $pChatFormat = str_replace("{COLOR_LIGHT_PURPLE}", TextFormat::LIGHT_PURPLE, $pChatFormat);
        $pChatFormat = str_replace("{COLOR_YELLOW}", TextFormat::YELLOW, $pChatFormat);
        $pChatFormat = str_replace("{COLOR_WHITE}", TextFormat::WHITE, $pChatFormat);
        
        $pChatFormat = str_replace("{FORMAT_OBFUSCATED}", TextFormat::OBFUSCATED, $pChatFormat);
        $pChatFormat = str_replace("{FORMAT_BOLD}", TextFormat::BOLD, $pChatFormat);
        $pChatFormat = str_replace("{FORMAT_STRIKETHROUGH}", TextFormat::STRIKETHROUGH, $pChatFormat);
        $pChatFormat = str_replace("{FORMAT_UNDERLINE}", TextFormat::UNDERLINE, $pChatFormat);
        $pChatFormat = str_replace("{FORMAT_ITALIC}", TextFormat::ITALIC, $pChatFormat);
        
        $pChatFormat = str_replace("{FORMAT_RESET}", TextFormat::RESET, $pChatFormat);
        
        return $pChatFormat;
    }

    /**
     * @param Player $player
     * @param $message
     * @param null $levelName
     * @return mixed
     */
    public function formatMessage(Player $player, $message, $levelName = null)
    {
        $group = $this->PurePerms->getUser($player)->getGroup($levelName);
        
        $groupName = $group->getName();
        
        if($levelName == null)
        {
            if($this->getConfig()->getNested("groups.$groupName.default-chat") == null)
            {
                $this->getConfig()->setNested("groups.$groupName.default-chat", "[$groupName] {display_name} > {message}");
            }
            
            $pChatFormat = $this->getConfig()->getNested("groups.$groupName.default-chat");
        }
        else
        {
            if($this->getConfig()->getNested("groups.$groupName.worlds.$levelName.default-chat") == null)
            {
                $this->getConfig()->setNested("groups.$groupName.worlds.$levelName.default-chat", "[$groupName] {display_name} > {message}");
                
                $this->getConfig()->save();
            }
            
            $pChatFormat = $this->getConfig()->getNested("groups.$groupName.worlds.$levelName.default-chat");
        }

        $pChatFormat = str_replace("{world_name}", $levelName, $pChatFormat);
        $pChatFormat = str_replace("{display_name}", $player->getDisplayName(), $pChatFormat);
        $pChatFormat = str_replace("{user_name}", $player->getName(), $pChatFormat);
        $pChatFormat = str_replace("{message}", $message, $pChatFormat);
        
        if($this->factionsPro != null) 
        {
            if(!$this->factionsPro->isInFaction($player->getName()))
            {            
                $pChatFormat = str_replace("{faction}", "...", $pChatFormat);
            }

            if($this->factionsPro->isLeader($player->getName()))
            {
                $pChatFormat = str_replace("{faction}", "**" . $this->factionsPro->getPlayerFaction($player->getName()), $pChatFormat);
            }
            else
            {
                $pChatFormat = str_replace("{faction}", "*" . $this->factionsPro->getPlayerFaction($player->getName()), $pChatFormat);
            }
        }
        
        if(!$player->hasPermission("pchat.colored")) return $this->removeColors($pChatFormat);
        
        return $this->addColors($pChatFormat);
    }

    /**
     * @param Player $player
     * @param $levelName
     * @return mixed
     */
    public function getNametag(Player $player, $levelName)
    {
        $group = $this->PurePerms->getUser($player)->getGroup($levelName);
        
        $groupName = $group->getName();
        
        if($levelName == null)
        {
            if($this->getConfig()->getNested("groups.$groupName.default-nametag") == null)
            {
                $this->getConfig()->setNested("groups.$groupName.default-nametag", "[$groupName] {display_name}");
            }
            
            $nameTag = $this->getConfig()->getNested("groups.$groupName.default-nametag");
        }
        else
        {
            if($this->getConfig()->getNested("groups.$groupName.worlds.$levelName.default-nametag") == null)
            {
                $this->getConfig()->setNested("groups.$groupName.worlds.$levelName.default-nametag", "[$groupName] {display_name}");
                
                $this->getConfig()->save();
            }
            
            $nameTag = $this->getConfig()->getNested("groups.$groupName.worlds.$levelName.default-nametag");
        }
        
        $nameTag = str_replace("{world_name}", $levelName, $nameTag);
        $nameTag = str_replace("{display_name}", $player->getDisplayName(), $nameTag);
        $nameTag = str_replace("{user_name}", $player->getName(), $nameTag);
        
        if($this->factionsPro != null) 
        {
            if(!$this->factionsPro->isInFaction($player->getName()))
            {            
                $nameTag = str_replace("{faction}", "...", $nameTag);
            }

            if($this->factionsPro->isLeader($player->getName()))
            {
                $nameTag = str_replace("{faction}", "**" . $this->factionsPro->getPlayerFaction($player->getName()), $nameTag);
            }
            else
            {
                $nameTag = str_replace("{faction}", "*" . $this->factionsPro->getPlayerFaction($player->getName()), $nameTag);
            }
        }
        
        if(!$player->hasPermission("pchat.colored")) return $this->removeColors($nameTag);
        
        return $this->addColors($nameTag);
    }
    
    public function removeColors($pChatFormat)
    {
        $pChatFormat = str_replace("{COLOR_BLACK}", "", $pChatFormat);
        $pChatFormat = str_replace("{COLOR_DARK_BLUE}", "", $pChatFormat);
        $pChatFormat = str_replace("{COLOR_DARK_GREEN}", "", $pChatFormat);
        $pChatFormat = str_replace("{COLOR_DARK_AQUA}", "", $pChatFormat);
        $pChatFormat = str_replace("{COLOR_DARK_RED}", "", $pChatFormat);
        $pChatFormat = str_replace("{COLOR_DARK_PURPLE}", "", $pChatFormat);
        $pChatFormat = str_replace("{COLOR_GOLD}", "", $pChatFormat);
        $pChatFormat = str_replace("{COLOR_GRAY}", "", $pChatFormat);
        $pChatFormat = str_replace("{COLOR_DARK_GRAY}", "", $pChatFormat);
        $pChatFormat = str_replace("{COLOR_BLUE}", "", $pChatFormat);
        $pChatFormat = str_replace("{COLOR_GREEN}", "", $pChatFormat);
        $pChatFormat = str_replace("{COLOR_AQUA}", "", $pChatFormat);
        $pChatFormat = str_replace("{COLOR_RED}", "", $pChatFormat);
        $pChatFormat = str_replace("{COLOR_LIGHT_PURPLE}", "", $pChatFormat);
        $pChatFormat = str_replace("{COLOR_YELLOW}", "", $pChatFormat);
        $pChatFormat = str_replace("{COLOR_WHITE}", "", $pChatFormat);
        
        $pChatFormat = str_replace("{FORMAT_OBFUSCATED}", "", $pChatFormat);
        $pChatFormat = str_replace("{FORMAT_BOLD}", "", $pChatFormat);
        $pChatFormat = str_replace("{FORMAT_STRIKETHROUGH}", "", $pChatFormat);
        $pChatFormat = str_replace("{FORMAT_UNDERLINE}", "", $pChatFormat);
        $pChatFormat = str_replace("{FORMAT_ITALIC}", "", $pChatFormat);
        
        $pChatFormat = str_replace("{FORMAT_RESET}", "", $pChatFormat);
        
        return $pChatFormat;
    }
}