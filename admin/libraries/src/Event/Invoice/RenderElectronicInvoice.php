<?php
namespace Phoca\PhocaCart\Event\Invoice;

use Phoca\PhocaCart\Event\AbstractEvent;

class RenderElectronicInvoice extends AbstractEvent
{
    public function __construct(array $orderData, array $eventData = [])
    {
        parent::__construct('pci', 'onPCIrenderElectronicInvoice', [
            'orderData' => $orderData,
            'eventData' => $eventData,
        ]);
    }

    public function getOrderData(): array
    {
        return $this->getArgument('orderData');
    }
}
