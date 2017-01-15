<?php
namespace _64FF00\PureChat\Tags;
interface CustomTagInterface
{
  public function getAPI();

  public function onAdd():void;//calls when it is added

  public function onRemove():void;//calls when it is removed by internal reasons

  public function onFailedAdd($code):void;//calls when fails to be added with reason

  public function getPrefix():string;

  public function getAllTags():array;
}