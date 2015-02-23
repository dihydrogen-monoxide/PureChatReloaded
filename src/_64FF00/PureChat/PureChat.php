<?php

namespace _64FF00\PureChat;

use pocketmine\Player;

use pocketmine\plugin\PluginBase;

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
    private $config, $plugin, $factionsPro;
    
    public function onLoad()
    {
        $this->saveDefaultConfig();
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
    
    public function formatMessage(Player $player, $message, $levelName = null)
    {
        $group = $this->PurePerms->getUser($player)->getGroup($levelName);
        
        $groupName = $group->getName();
        
        if($levelName == null)
        {
            if($this->getConfig()->getNested("groups.$groupName.default") == null)
            {
                $this->getConfig()->setNested("groups.$groupName.default", "[$groupName] {display_name} > {message}");
            }
            
            $chatFormat = $this->getConfig()->getNested("groups.$groupName.default");
        }
        else
        {
            if($this->getConfig()->getNested("groups.$groupName.worlds.$levelName") == null)
            {
                $this->getConfig()->setNested("groups.$groupName.worlds.$levelName", "[$groupName] {display_name} > {message}");
                
                $this->getConfig()->save();
            }
            
            $chatFormat = $this->getConfig()->getNested("groups.$groupName.worlds.$levelName");
        }
        
        $chatFormat = str_replace("{world_name}", $levelName, $chatFormat);
        $chatFormat = str_replace("{display_name}", $player->getDisplayName(), $chatFormat);
        $chatFormat = str_replace("{user_name}", $player->getName(), $chatFormat);
        $chatFormat = str_replace("{message}", $message, $chatFormat);
        
        if($this->factionsPro != null) 
        {
            if(!$this->factionsPro->isInFaction($player->getName()))
            {
                $chatFormat = str_replace("{faction}", "...", $chatFormat);
            }
            
            $chatFormat = str_replace("{faction}", $this->factionsPro->getPlayerFaction($player->getName()), $chatFormat);
        }
        
        return $chatFormat;
    }
}