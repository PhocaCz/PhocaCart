<?php
namespace Phoca\PhocaCart\Constants;

enum EmailDocumentType: int
{
    case Order = 1;
    case Invoice = 2;
    case DeliveryNote = 3;
}
