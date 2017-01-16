<?php
namespace _64FF00\PureChat\Tags;
interface CustomTagInterface
{
  public function getAPI();

  public function onAdd();//calls when it is added

  public function onRemove();//calls when it is removed by internal reasons

  public function onError($code);//calls when fails to be added OR when error is detected while processing with reason

  public function getPrefix():string;

  public function getAllTags():array;
}