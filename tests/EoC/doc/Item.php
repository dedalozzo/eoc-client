<?php

/**
 * @file Item.php
 * @brief This file contains the Item class.
 * @details
 * @author Filippo F. Fadda
 */


use EoC\Doc\Doc;


class Item extends Doc {

  public function setDate($date) {
    $this->meta["date"] = $date;
  }


  public function getDate() {
    return $this->meta["date"];
  }


  public function setHitNum($hitNum) {
    $this->meta["hitNum"] = $hitNum;
  }


  public function getHitNum() {
    return $this->meta["hitNum"];
  }


  public function setIdMember($idMember) {
    $this->meta["idMember"] = $idMember;
  }


  public function getIdMember() {
    return $this->meta["idMember"];
  }


  public function setIditem($iditem) {
    $this->meta["iditem"] = $iditem;
  }


  public function getIditem() {
    return $this->meta["iditem"];
  }


  public function setReplyNum($replyNum) {
    $this->meta["replyNum"] = $replyNum;
  }


  public function getReplyNum() {
    return $this->meta["replyNum"];
  }


  public function setTitle($title) {
    $this->meta["title"] = $title;
  }


  public function getTitle() {
    return $this->meta["title"];
  }

}