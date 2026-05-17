<?php
namespace Phoca\PhocaCart\Event\Calculation;

use Phoca\PhocaCart\Event\AbstractEvent;

class CalculationPrice extends AbstractEvent
{
	public function __construct(string $name, &$product)
	{
		parent::__construct('system', 'onPhocaCartCalculationPrice', [
			'product' => &$product,
			'context' => $name
		]);
	}
}
