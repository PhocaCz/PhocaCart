<?php
namespace Phoca\PhocaCart\Event\Invoice;

use Phoca\PhocaCart\Event\AbstractEvent;

class GetElectronicInvoiceIcons extends AbstractEvent
{
    public function __construct(int $orderId, array $eventData = [])
    {
        parent::__construct('pci', 'onPCIgetElectronicInvoiceIcons', [
            'orderId' => $orderId,
            'eventData' => $eventData,
        ]);
    }

    public function getOrderId(): int
    {
        return $this->getArgument('orderId');
    }
}
