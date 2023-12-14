<?php
namespace Phoca\PhocaCart\Event\View\Cart;

use Phoca\PhocaCart\Event\AbstractEvent;

class UpdateItems extends AbstractEvent
{
  public function __construct(string $idKey, ?array $item, ?int $quantityOld, int $quantityNew) {
    parent::__construct('pcv', 'onPCVCartUpdateItems', [
      'idKey' => $idKey,
      'item' => $item,
      'quantityOld' => $quantityOld,
      'quantityNew' => $quantityNew,
    ]);
  }
}
