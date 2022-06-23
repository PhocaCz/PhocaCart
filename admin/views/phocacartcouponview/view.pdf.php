<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();
use Joomla\CMS\MVC\View\HtmlView;
use Joomla\CMS\Factory;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Layout\FileLayout;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
jimport( 'joomla.application.component.view' );

class PhocaCartCpViewPhocacartCouponView extends HtmlView
{

	protected $t;
	protected $r;

	public function display($tpl = null) {


		$app			= Factory::getApplication();
		$this->t		= PhocacartUtils::setVars('couponview');
		$this->r		= new PhocacartRenderAdminview();
		$id				= $app->input->get('id', 0, 'int');
		$format			= $app->input->get('format', '', 'string');



		$pdfV                  = array();
		$pdfV['plugin-pdf']    = PhocacartUtilsExtension::getExtensionInfo('phocacart', 'plugin', 'phocapdf');
		$pdfV['component-pdf'] = PhocacartUtilsExtension::getExtensionInfo('com_phocapdf');
		$pdfV['pdf']           = 0;

		if ($pdfV['plugin-pdf'] == 1 && $pdfV['component-pdf'] == 1) {
			if (File::exists(JPATH_ADMINISTRATOR . '/components/com_phocapdf/helpers/phocapdfrender.php')) {
				require_once(JPATH_ADMINISTRATOR . '/components/com_phocapdf/helpers/phocapdfrender.php');
			} else {
				PhocacartLog::add(2, 'Coupon View - ERROR (PDF Class)', (int)$orderId, 'Render PDF file could not be found in system');
				throw new Exception('Error - Phoca PDF Helper - Render PDF file could not be found in system', 500);
				return false;
			}
			$pdfV['pdf'] = 1;
		}

		if ($pdfV['pdf'] == 1) {
			
				$layoutG	= new FileLayout('gift_voucher', null, array('component' => 'com_phocacart', 'client' => 0));

				$price 		= new PhocacartPrice();
				$gift 		= PhocacartCoupon::getGiftByCouponId($id);
				$d               = $gift;

				$staticData = array();
				$staticData['option']   = 'com_phocacart';
				$staticData['title']    = $d['title'];
				$staticData['file']     = '';// Must be empty to not save the pdf to server
				$staticData['filename'] = strip_tags( 'cooupon_' . $id) . '.pdf';
				$staticData['subject']  = '';
				$staticData['keywords'] = '';
				$staticData['output']   = '';


				// Initialize PDF for buyer which gets all the coupons
				// we need to initilaize PDF here because we need tcpdf classed in template output
				$pdf      = new stdClass();
				$content  = new stdClass();
				$document = new stdClass();
				PhocaPDFRender::initializePDF($pdf, $content, $document, $staticData);

				
				$d['typeview']   = 'Coupon';
				$d['product_id'] = $gift['gift_product_id'];

				$d['discount']   = $price->getPriceFormat($gift['discount']);
				$d['valid_from'] = HTMLHelper::date($gift['valid_from'], Text::_('DATE_FORMAT_LC3'));
				$d['valid_to']   = HTMLHelper::date($gift['valid_to'], Text::_('DATE_FORMAT_LC3'));
				$d['format']     = 'pdf';

				$d['pdf_instance'] = $pdf;// we need tcpdf instance in output to use different tcpdf functions

				
				$staticData['pdf_destination'] = 'I';
				$staticData['output']          = $layoutG->render($d);


				PhocaPDFRender::renderInitializedPDF($pdf, $content, $document, $staticData);
				exit;

		}



		// Set title here, if customized in pdf plugin parameters, it overwrites this title - this is only default title




		// PDF document name



		//$media = new PhocacartRenderAdminmedia();

		//parent::display($tpl);
	}

}
?>
