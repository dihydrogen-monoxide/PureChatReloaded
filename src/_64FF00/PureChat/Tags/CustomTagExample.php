<?php
namespace _64FF00\PureChat\Tags;

use _64FF00\PureChat\Errors\ErrorHelper;
use pocketmine\Player;
use pocketmine\Server;

class CustomTagExample implements CustomTagInterface
{
  static $count = 1;
  private $logger;

  public function __construct(Server $server)
  {
    $this->logger = $server->getLogger();
  }

  public function onAdd() { $this->logger->info("Custom Tag Added!"); }

  public function onRemove($code = 0) { $this->logger->info("Removed Due to $code, " . ErrorHelper::getDetails($code)); }

  public function onError($code) { $this->logger->info("Fail Added $code, " . ErrorHelper::getDetails($code)); }

  public function getPrefix(): string
  {
    return "test"; //This indicates the prefix of the said tag this would be test_(stffix)
  }

  public function getAllTags(): array
  {
    return [
      "test" => "testing",
      "count" => "countIt",
      "rand" => "randIt"
      //"suffix" => "callable fucntion"
    ];
  }

  public function testing(Player $player)
    //Player is NEEDED as it will automatically be passed reagrdless of anything
    //Not adding Player OR editing it may cause PHP to throw a error which may result of getting this tag removed
  {
    return "Works!";//do something here with player
  }

  public function countIt(Player $player) //more examples
  {
    self::$count++;
    return self::$count;
  }

  public function randIt(Player $player)
  {
    mt_srand(self::$count + microtime(true));
    return mt_rand(0, 1000);
  }
}