<?php
namespace Phoca\PhocaCart\Constants;

enum EmailDocumentType: int
{
    case None = 0;
    case Order = 1;
    case Invoice = 2;
    case DeliveryNote = 3;
}
