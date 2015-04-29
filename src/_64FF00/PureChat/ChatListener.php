<?php

namespace _64FF00\PureChat;

use pocketmine\event\Listener;

use pocketmine\event\player\PlayerChatEvent;

class ChatListener implements Listener
{
    /* PurePerms by 64FF00 (xktiverz@gmail.com, @64ff00 for Twitter) */

    /*
          # #    #####  #       ####### #######   ###     ###   
          # #   #     # #    #  #       #        #   #   #   #  
        ####### #       #    #  #       #       #     # #     # 
          # #   ######  #    #  #####   #####   #     # #     # 
        ####### #     # ####### #       #       #     # #     # 
          # #   #     #      #  #       #        #   #   #   #  
          # #    #####       #  #       #         ###     ###                                        
                                                                                       
    */

    private $plugin;
    
    public function __construct(PureChat $plugin)
    {
        $this->plugin = $plugin;
    }
    
    /**
     * @param PlayerChatEvent $event
     * @priority HIGHEST
     */
    public function onPlayerChat(PlayerChatEvent $event)
    {
        $player = $event->getPlayer();
        
        $isMultiWorldFormatsEnabled = $this->plugin->getConfig()->get("enable-multiworld-formats");
        
        $levelName = $isMultiWorldFormatsEnabled ?  $player->getLevel()->getName() : null;
        
        $chatFormat = $this->plugin->formatMessage($player, $event->getMessage(), $levelName);
        
        $event->setFormat($chatFormat);
    }
}