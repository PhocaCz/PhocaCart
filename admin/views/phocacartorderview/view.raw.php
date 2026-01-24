<?php
defined('_JEXEC') or die();

use Joomla\CMS\MVC\View\HtmlView;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Phoca\PhocaCart\Dispatcher\Dispatcher;
use Phoca\PhocaCart\Event\Invoice\RenderElectronicInvoice;

jimport('joomla.application.component.view');

class PhocaCartCpViewPhocacartOrderView extends HtmlView
{
    public function display($tpl = null)
    {
        $app = Factory::getApplication();
        $id = $app->getInput()->get('id', 0, 'int');
        $type = $app->getInput()->get('type', 0, 'int');
        $subtype = $app->getInput()->get('subtype', '', 'string');
        $format = $app->getInput()->get('format', '', 'string');

        if ($type == 5 && $subtype != '') {
            $this->renderElectronicInvoice($id, $subtype);
            return;
        }

        $order = new PhocacartOrderRender();
        $o = $order->render($id, $type, $format);
        echo $o;
    }

    protected function renderElectronicInvoice($id, $subtype)
    {
        $app = Factory::getApplication();

        if ($id < 1) {
            echo Text::_('COM_PHOCACART_ERROR_NO_ORDER_FOUND');
            return;
        }

        $orderView = new PhocacartOrderView();
        $common = $orderView->getItemCommon($id);

        if (!$common || empty($common->order_number)) {
            echo Text::_('COM_PHOCACART_ERROR_NO_ORDER_FOUND');
            return;
        }

        $price = new PhocacartPrice();
        $price->setCurrency($common->currency_id);

        $orderData = [
            'common' => $common,
            'bas' => $orderView->getItemBaS($id, 1),
            'products' => $orderView->getItemProducts($id),
            'total' => $orderView->getItemTotal($id, 1),
            'taxrecapitulation' => $orderView->getItemTaxRecapitulation($id),
            'price' => $price,
            'params' => PhocacartUtils::getComponentParameters(),
        ];

        $output = null;
        $results = Dispatcher::dispatch(new RenderElectronicInvoice($orderData, [
            'pluginname' => $subtype,
            'orderData' => $orderData,
        ]));

        if (!empty($results)) {
            foreach ($results as $result) {
                if (is_array($result) && !empty($result['content'])) {
                    $output = $result;
                    break;
                }
            }
        }

        if ($output && !empty($output['content'])) {
            $filename = $output['filename'] ?? 'invoice.xml';
            $contentType = $output['contentType'] ?? 'application/xml';

            $app->setHeader('Content-Type', $contentType . '; charset=UTF-8');
            $app->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '"');
            $app->setHeader('Cache-Control', 'no-cache, must-revalidate');
            $app->setHeader('Pragma', 'public');

            echo $output['content'];
        } else {
            echo Text::_('COM_PHOCACART_ERROR_NO_ORDER_FOUND');
        }
    }
}
