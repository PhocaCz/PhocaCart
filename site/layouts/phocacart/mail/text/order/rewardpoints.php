<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

use Joomla\CMS\Language\Text;
use Phoca\PhocaCart\Constants\EmailDocumentType;

defined('_JEXEC') or die();

/** @var array $displayData */
/** @var Joomla\Registry\Registry $params */
/** @var EmailDocumentType $documentType */
$params = $displayData['params'];
$documentType = $displayData['documentType'];

if ($params->get( 'display_reward_points_invoice') && $documentType == EmailDocumentType::Invoice && $displayData['order']->user_id) {
    $pointsUser 	= PhocacartReward::getTotalPointsByUserIdExceptCurrentOrder($displayData['order']->user_id, $displayData['order']->id);
    $pointsOrder 	= PhocacartReward::getTotalPointsByOrderId($displayData['order']->id);

    echo Text::_('COM_PHOCACART_YOUR_CURRENT_REWARD_POINTS_BALANCE') . ': ' . $pointsUser . "\n";
    echo Text::_('COM_PHOCACART_POINTS_RECEIVED_FOR_THIS_PURCHASE') . ': ' . $pointsOrder .  "\n";
}
