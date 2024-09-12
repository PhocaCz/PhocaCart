<?php
namespace Phoca\PhocaCart\Event\Admin\Image;

use Phoca\PhocaCart\Event\AbstractEvent;

class GetThumbnailName extends AbstractEvent
{
  public function __construct(array $path, string $filename, string $size, &$thumbName) {
    parent::__construct('pca', 'onPCAonImageGetThumbnailName', [
      'path' => $path,
      'filename' => $filename,
      'size' => $size,
      'thumbName' => &$thumbName
    ]);
  }
}
