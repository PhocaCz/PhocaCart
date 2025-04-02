<?php
/**
 * @package   Phoca Cart
 * @author    Jan Pavelka - https://www.phoca.cz
 * @copyright Copyright (C) Jan Pavelka https://www.phoca.cz
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 and later
 * @cms       Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license   http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 */
defined('_JEXEC') or die();
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

use Joomla\String\StringHelper;
use Joomla\CMS\Language\Language;

class PhocacartText {

	/*
	 * type ... 1 customers - email sent to customer - set in email or in different text parts of e.g. invoice
	 * type ... 2 others - email sent to all others
	 *
	 */

	public static function completeText($body, $replace, $type = 1) {
        $body = (string)$body;
        foreach ($replace as $key => $value) {
            if ($value) {
                $body = str_replace('{' . $key .'}', $value, $body);
            }
        }

        return $body;
	}


	//public static function completeTextFormFields($body, $bas, $type = 1) {
    public static function completeTextFormFields($body, $basB, $basS)
    {
	    $bas = array_merge($basB, $basS);

		// Common prefix means that if you set:
        // {b_name} ... billing name will be displayed
        // {s_name} ... shipping name will be displayed
        // {bs_name} ... first displaying billing name and if it is not available then display shipping name
        // {sb_name} ... first displaying shipping name and if it is not available then display billing name


		if (!empty($bas)) {
			if (isset($basB['id'])) {unset($basB['id']);}
			if (isset($basB['order_id'])) {unset($basB['order_id']);}
			if (isset($basB['user_address_id'])) {unset($basB['user_address_id']);}
			if (isset($basB['user_token'])) {unset($basB['user_token']);}
			if (isset($basB['user_groups'])) {unset($basB['user_groups']);}
			if (isset($basB['ba_sa'])) {unset($basB['ba_sa']);}
			if (isset($basB['type'])) {unset($basB['type']);}

			if (isset($basS['id'])) {unset($basS['id']);}
			if (isset($basS['order_id'])) {unset($basS['order_id']);}
			if (isset($basS['user_address_id'])) {unset($basS['user_address_id']);}
			if (isset($basS['user_token'])) {unset($basS['user_token']);}
			if (isset($basS['user_groups'])) {unset($basS['user_groups']);}
			if (isset($basS['ba_sa'])) {unset($basS['ba_sa']);}
			if (isset($basS['type'])) {unset($basS['type']);}


			foreach($bas as $k => $v) {
                if (isset($basB[$k]) && $basB[$k] != '') {
                    $body = str_replace('{b_' . $k . '}', $basB[$k], $body);
                    $body = str_replace('{bs_' . $k . '}', $basB[$k], $body);
                } else if (isset($basS[$k]) && $basS[$k] != '') {
                    // bs_item: the value is not in billing, try to find it in shipping
                    $body = str_replace('{bs_' . $k . '}', $basS[$k], $body);
                }


                if (isset($basS[$k]) && $basS[$k] != '') {
                    $body = str_replace('{s_' . $k . '}', $basS[$k], $body);
                    $body = str_replace('{sb_' . $k . '}', $basS[$k], $body);
                } else if (isset($basB[$k]) && $basB[$k] != '') {
                    // sb_item: the value is not in shipping, try to find it in billing
                    $body = str_replace('{sb_' . $k . '}', $basB[$k], $body);
                }

                // Nothing found - remove from text
                $body = str_replace('{b_' . $k . '}', '', $body);
                $body = str_replace('{bs_' . $k . '}', '', $body);
                $body = str_replace('{s_' . $k . '}', '', $body);
                $body = str_replace('{sb_' . $k . '}', '', $body);
            }
		}


		return $body;
	}

	public static function prepareReplaceText($order, $orderId, $common, $bas, $status = [])
    {
		$pC				= PhocacartUtils::getComponentParameters();
		$config 		= Factory::getConfig();
		$price			= new PhocacartPrice();
		$price->setCurrency($common->currency_id, $orderId);
		$totalBrutto	= $order->getItemTotal($orderId, 0, 'brutto');

		$download_guest_access = $pC->get('download_guest_access', 0);
        $pdf_invoice_qr_code = $pC->get('pdf_invoice_qr_code', '');

        $email_downloadlink_description = isset($status['email_downloadlink_description']) && $status['email_downloadlink_description'] != '' ? $status['email_downloadlink_description'] : '';

		$r = array();
		$r['ordertoken'] = '';

		if ($common->user_id > 0) {
			// Standard User get standard download page and order page
		    $r['orderlink'] 	= PhocacartPath::getRightPathLink(PhocacartRoute::getOrdersRoute());

            $r['downloadlinkforce'] 	= PhocacartPath::getRightPathLink(PhocacartRoute::getDownloadRoute());

            $r['downloadlink'] = '';
            $products 	= $order->getItemProducts($orderId);
            $isDownload = false;

			if(!empty($products) && isset($common->order_token) && $common->order_token != '') {
				foreach ($products as $v) {
				    if (!empty($v->downloads)) {
				        foreach ($v->downloads as $v2) {

                            // Main Product Download File
                            if (isset($v2->published) && $v2->published == 1 && isset($v2->download_file) && $v2->download_file != '' && isset($v2->download_folder) && $v2->download_folder != '' && isset($v2->download_token) && $v2->download_token != '') {
                                $isDownload = true;
                                break;
                            }

				        }
                    }

					// Product Attribute Option Download File
                    if (!empty($v->attributes)) {
                        foreach ($v->attributes as $v2) {
                            if (isset($v2->download_published) && $v2->download_published == 1 && isset($v2->download_file) && $v2->download_file != '' && isset($v2->download_folder) && $v2->download_folder != '') {
                                $isDownload = true;
                                break;
                            }
                        }
                    }
				}
			}

            if ($isDownload) {
                if ($email_downloadlink_description != '') {
                    $r['downloadlink'] .= '<div>'.$email_downloadlink_description.'</div>';
                }

                $r['downloadlink'] 	.= PhocacartPath::getRightPathLink(PhocacartRoute::getDownloadRoute());
            }

			// Possible variables in email
			if (isset($common->order_token) && $common->order_token != '') {
			    $r['orderlinktoken'] = PhocacartPath::getRightPathLink(PhocacartRoute::getOrdersRoute() . '&o='.$common->order_token);
				$r['ordertoken'] = $common->order_token;
			}

		} else {
            $r['downloadlinkforce'] = '';
            $r['downloadlink'] = '';
		    // Guests
			if (isset($common->order_token) && $common->order_token != '') {
				$r['orderlinktoken'] = PhocacartPath::getRightPathLink(PhocacartRoute::getOrdersRoute() . '&o='.$common->order_token);
				$r['ordertoken'] = $common->order_token;
				$r['orderlink'] = $r['orderlinktoken'];

			}
			$products 	= $order->getItemProducts($orderId);
            $isDownload = false;

			$downloadO 	= '';

			if(!empty($products) && isset($common->order_token) && $common->order_token != '' && $download_guest_access > 0) {
				$downloadO	= '<p>&nbsp;</p><h4>'.Text::_('COM_PHOCACART_DOWNLOAD_LINKS').'</h4>';
				foreach ($products as $k => $v) {

				    if (!empty($v->downloads)) {
                        $downloadO .= '<div><strong>'.$v->title.'</strong></div>';
				        foreach ($v->downloads as $k2 => $v2) {


                            // Main Product Download File

                            if (isset($v2->published) && $v2->published == 1 && isset($v2->download_file) && $v2->download_file != '' && isset($v2->download_folder) && $v2->download_folder != '' && isset($v2->download_token) && $v2->download_token != '') {

                                $title = str_replace($v2->download_folder, '', $v2->download_file);
                                $title = str_replace('/', '', $title);
                                $downloadLink = PhocacartPath::getRightPathLink(PhocacartRoute::getDownloadRoute() . '&o='.$common->order_token.'&d='.$v2->download_token);
                                $downloadO .= '<div>'.Text::_('COM_PHOCACART_DOWNLOAD').': <a href="'.$downloadLink.'">'.$title.'</a></div>';
                                $downloadO .= '<div><small>'.Text::_('COM_PHOCACART_DOWNLOAD_LINK').': <a href="'.$downloadLink.'">'.$downloadLink.'</a></small><hr></div>';
                                $isDownload = true;
                            }

				        }
                    }

					// Product Attribute Option Download File
                    if (!empty($v->attributes)) {
                        foreach ($v->attributes as $k2 => $v2) {
                            if (isset($v2->download_published) && $v2->download_published == 1 && isset($v2->download_file) && $v2->download_file != '' && isset($v2->download_folder) && $v2->download_folder != '') {

                                $title = str_replace($v2->download_folder, '', $v2->download_file);
                                $title = str_replace('/', '', $title);

                                $downloadO .= '<div><strong>'.$v->title.'('.$v2->attribute_title.': '.$v2->option_title.')</strong></div>';
                                $downloadLink = PhocacartPath::getRightPathLink(PhocacartRoute::getDownloadRoute() . '&o='.$common->order_token.'&d='.$v2->download_token);
                                $downloadO .= '<div>'.Text::_('COM_PHOCACART_DOWNLOAD').': <a href="'.$downloadLink.'">'.$title.'</a></div>';
                                $downloadO .= '<div><small>'.Text::_('COM_PHOCACART_DOWNLOAD_LINK').': <a href="'.$downloadLink.'">'.$downloadLink.'</a></small><hr></div>';
                                $isDownload = true;
                            }
                        }
                    }
				}
				$downloadO .= '<p>&nbsp;</p>';
			}

            if ($isDownload) {
                if ($email_downloadlink_description != '') {
                    $r['downloadlink'] .= '<div>'.$email_downloadlink_description.'</div>';
                }
                $r['downloadlink'] .= $downloadO;
            }
		}


		// --- name and email as additional info here, all other user information can be accessed through: PhocacartText::completeTextFormFields ... b_name_first, b_name_middle
		$r['name'] 			= '';
		if (isset($bas['b']['name_first']) && isset($bas['b']['name_last'])) {
			$r['name'] = PhocacartUser::buildName($bas['b']['name_first'], $bas['b']['name_last']);
		}

		$r['email'] 		= '';
		if (isset($bas['b']['email'])) {
			$r['email'] = $bas['b']['email'];
		}

		if ($r['email'] == '' && isset($bas['s']['email'])) {
			$r['email'] = $bas['s']['email'];
		}
		// ---


		$r['trackingnumber'] 		= PhocacartOrderView::getTrackingNumber($common);
        $r['trackinglink'] 			= PhocacartOrderView::getTrackingLink($common);
		$r['trackingdescription'] 	= PhocacartOrderView::getTrackingDescription($common);
		$r['shippingtitle'] 		= PhocacartOrderView::getShippingTitle($common);
        $r['shippingdescriptioninfo'] 	= PhocacartOrderView::getShippingDescriptionInfo($common);


        $r['shippingbranchname']    = '';
        $r['shippingbranchcode']    = '';
        $r['shippingbranchid']      = '';
        $r['shippingbranchcountry'] = '';
        $r['shippingbranchcity']    = '';
        $r['shippingbranchstreet']  = '';
        $r['shippingbranchzip']     = '';
        $r['shippingbranchurl']     = '';
        $r['shippingbranchthumbnail']   = '';
        $r['shippingbranchcurrency']    = '';

        if (isset($common->params_shipping)) {
            $paramsShipping = json_decode($common->params_shipping, true);
            $r['shippingbranchname']    = isset($paramsShipping['name']) ? $paramsShipping['name']: '';
            $r['shippingbranchcode']    = isset($paramsShipping['branchCode']) ? $paramsShipping['branchCode']: '';
            $r['shippingbranchid']      = isset($paramsShipping['id']) ? $paramsShipping['id']: '';
            $r['shippingbranchcountry'] = isset($paramsShipping['country']) ? $paramsShipping['country']: '';
            $r['shippingbranchcity']    = isset($paramsShipping['city']) ? $paramsShipping['city']: '';
            $r['shippingbranchstreet']  = isset($paramsShipping['street']) ? $paramsShipping['street']: '';
            $r['shippingbranchzip']     = isset($paramsShipping['zip']) ? $paramsShipping['zip']: '';
            $r['shippingbranchurl']     = isset($paramsShipping['url']) ? $paramsShipping['url']: '';
            $r['shippingbranchthumbnail']   = isset($paramsShipping['thumbnail']) ? $paramsShipping['thumbnail']: '';
            $r['shippingbranchcurrency']    = isset($paramsShipping['currency']) ? $paramsShipping['currency']: '';
        }


		$r['dateshipped'] 			= PhocacartOrderView::getDateShipped($common);
		$r['customercomment'] 		= $common->comment;
		$r['currencycode'] 			= $common->currency_code;
		$r['websitename']			= $config->get( 'sitename' );
		$r['websiteurl']			= Uri::root();

		$r['orderid']				= $orderId;
		$r['ordernumber']			= PhocacartOrder::getOrderNumber($orderId, $common->date, $common->order_number);
		$r['invoicenumber']			= PhocacartOrder::getInvoiceNumber($orderId, $common->date, $common->invoice_number);
		$r['receiptnumber']			= PhocacartOrder::getReceiptNumber($orderId, $common->date, $common->receipt_number);
		$r['queuenumber']			= PhocacartOrder::getQueueNumber($orderId, $common->date, $common->queue_number);
		$r['paymentreferencenumber']= PhocacartOrder::getPaymentReferenceNumber($orderId, $common->date, $common->invoice_prn);
		$r['invoiceduedate']		= PhocacartOrder::getInvoiceDueDate($orderId, $common->date, $common->invoice_due_date, 'Y-m-d');
		//$r['invoiceduedateyear']	= PhocacartOrder::getInvoiceDueDate($orderId, $common->date, $common->invoice_due_date, 'Y');
		//$r['invoiceduedatemonth']	= PhocacartOrder::getInvoiceDueDate($orderId, $common->date, $common->invoice_due_date, 'm');
		//$r['invoiceduedateday']	= PhocacartOrder::getInvoiceDueDate($orderId, $common->date, $common->invoice_due_date, 'd');



        $dateIdd					= PhocacartDate::splitDate($r['invoiceduedate']);
        $r['invoicedueyear']	    = $dateIdd['year'];
		$r['invoiceduemonth']	    = $dateIdd['month'];
		$r['invoicedueday']	        = $dateIdd['day'];

		$r['invoicedate']			= PhocacartOrder::getInvoiceDate($orderId, $common->invoice_date, 'Y-m-d');

		$dateId 					= PhocacartDate::splitDate($r['invoicedate']);
        $r['invoiceyear']	        = $dateId['year'];
		$r['invoicemonth']	        = $dateId['month'];
		$r['invoiceday']	        = $dateId['day'];




		$r['invoicetimeofsupply']	= PhocacartOrder::getInvoiceDate($orderId, $common->invoice_time_of_supply, 'Y-m-d');

        $defaultCurrency = PhocacartCurrency::getDefaultCurrency();
		$totalToPay					= isset($totalBrutto[0]->amount) ? $totalBrutto[0]->amount : 0;
		$r['totaltopaynoformat']	= number_format($totalToPay, 2, '.', '');
        $r['totaltopaynoformatcomma']	= number_format($totalToPay, 2, ',', '');
		$r['totaltopay']			= $price->getPriceFormat($totalToPay, 0, 1, $defaultCurrency);

        $totalToPayCurrency			= isset($totalBrutto[0]->amount_currency) ? $totalBrutto[0]->amount_currency : 0;
        if ($totalToPayCurrency != 0) {
            $r['totaltopaynoformatcurrency']      = number_format($totalToPayCurrency, 2, '.', '');
            $r['totaltopaynoformatcommacurrency'] = number_format($totalToPayCurrency, 2, ',', '');
            $r['totaltopaycurrency']              = $price->getPriceFormat($totalToPayCurrency, 0, 1);
        } else {
            $r['totaltopaynoformatcurrency']      = $r['totaltopaynoformat'];
            $r['totaltopaynoformatcommacurrency'] = $r['totaltopaynoformatcomma'];
            $r['totaltopaycurrency']              = $r['totaltopay'];
        }



        $r['paymenttitle'] 		    = PhocacartOrderView::getPaymentTitle($common);
        $r['paymentdescriptioninfo'] 		= PhocacartOrderView::getPaymentDescriptionInfo($common);
		$dateO 						= PhocacartDate::splitDate($common->date);

		$r['orderdate']             = $common->date;
		$r['orderyear']				= $dateO['year'];
		$r['ordermonth']			= $dateO['month'];
		$r['orderday']				= $dateO['day'];
		$r['ordernumbertxt']		= Text::_('COM_PHOCACART_ORDER_NR');


		$r['bankaccountnumber']		= $pC->get( 'bank_account_number', '' );
		$r['iban']					= $pC->get( 'iban', '' );
		$r['bicswift']				= $pC->get( 'bic_swift', '' );

        $r['openingtimesinfo']      = PhocacartTime::getOpeningTimesMessage();

        $r['vendorname']            = '';
        $r['venderusername']        = '';
        if ((int)$common->vendor_id > 0) {
            $vendor                 = Factory::getUser((int)$common->vendor_id);
            $r['vendorname']        = $vendor->name;
            $r['venderusername']    = $vendor->username;
        }

        // Specific Case - QR Code inside {invoiceqr} parameter
        $pdf_invoice_qr_code_translated = PhocacartText::completeText($pdf_invoice_qr_code, $r, 1);
        $r['invoiceqr']                 = PhocacartUtils::getQrImage($pdf_invoice_qr_code_translated);

		return $r;
	}

    /**
     * @param $string
     * @param string $type html|url|number|number2|alphanumeric|alphanumeric2|alphanumeric3|folder|file|folderpath|filepath|text
     * @return string|string[]|null
     */
    public static function filterValue($string, $type = 'html') {


        $string = (string)$string;
        switch ($type) {

            case 'url':
                return rawurlencode($string);
            break;

            case 'number':
                return preg_replace( '/[^.0-9]/', '', $string );
            break;

            case 'number2':
                //return preg_replace( '/[^0-9\.,+-]/', '', $string );
                return preg_replace( '/[^0-9\.,-]/', '', $string );
            break;

            // Be sure we have number
            case 'number3':
                $string = str_replace(',', '.', $string);
                //return preg_replace( '/[^0-9\.,+-]/', '', $string );
                return preg_replace( '/[^0-9\.]/', '', $string );
            break;

            // Only number and , for database IN
            case 'number4':
                $string = str_replace('.', ',', $string);
                //return preg_replace( '/[^0-9\.,+-]/', '', $string );
                return preg_replace( '/[^0-9\,]/', '', $string );
            break;

            case 'alphanumeric':
                return preg_replace("/[^a-zA-Z0-9]+/", '', $string);
            break;

            case 'alphanumeric2':
                return preg_replace("/[^\\w-]/", '', $string);// Alphanumeric plus _  -
            break;

            case 'alphanumeric3':
                return preg_replace("/[^\\w.-]/", '', $string);// Alphanumeric plus _ . -
            break;
            case 'alphanumeric4':
                return preg_replace("/[^\\w.,-]/", '', $string);// Alphanumeric plus _ . , -
            break;
            case 'alphanumeric5':
                return preg_replace("/[^\\w.,]/", '', $string);// Alphanumeric plus _ . ,
            break;

            case 'float':
                $pattern = '/[-+]?[0-9]+(\.[0-9]+)?([eE][-+]?[0-9]+)?/';
                preg_match($pattern, $string, $matches);
                return isset($matches[0]) ? (float) $matches[0] : 0.0;
            break;

            case 'folder':
            case 'file':
                $string =  preg_replace('/[\"\*\/\\\:\<\>\?\'\|]+/', '', $string);
                return htmlspecialchars($string, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            break;

            case 'folderpath':
            case 'filepath':
                $string = preg_replace('/[\"\*\:\<\>\?\'\|]+/', '', $string);
                return htmlspecialchars($string, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            break;

            case 'text':
                return trim(htmlspecialchars(strip_tags($string), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'));
            break;

            case 'text-br-span':
                $string = strip_tags($string, '<br><span>');
                $cleaned = preg_replace('/<br[^>]*>/i', '<br>', $string);
                $cleaned = preg_replace('/<span[^>]*>/i', '<span>', $cleaned);
                return trim($cleaned);
            break;

            case 'text-div':
                $string = strip_tags($string, '<div>');
                $cleaned = preg_replace('/<div[^>]*>/i', '<div>', $string);
                return trim($cleaned);
            break;

            case 'class':
                $string = str_replace(' ', '-', strtolower($string));
                return preg_replace("/[^\\w-]/", '', $string);// Alphanumeric plus _  -
            break;

            case 'html':
            default:
                return htmlspecialchars($string, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            break;

        }

    }

    public static function stringURLSafe($string, $language = '')
	{
		// Remove any '-' from the string since they will be used as concatenaters
		$str = str_replace('-', ' ', $string);

		// Transliterate on the language requested (fallback to current language if not specified)
		$lang = $language == '' || $language == '*' ? Factory::getLanguage() : Language::getInstance($language);
		$str = $lang->transliterate($str);

		// Trim white spaces at beginning and end of alias and make lowercase
		$str = trim(StringHelper::strtolower($str));

		// Remove any duplicate whitespace, and ensure all characters are alphanumeric
		$str = preg_replace('/(\s|[^A-Za-z0-9\-_])+/', '-', $str);

		// Trim dashes at beginning and end of alias
		$str = trim($str, '-');

		return $str;
	}

	/*
	 * Be aware, tag without any attribute will be removed, used e.g. when editor adds first p tag (without attributes)
	 */

	public static function removeFirstTag($string, $tag = 'p') {

        // First
        $needle = '<'.$tag.'>';
        $pos = strpos($string, $needle);
        if ($pos !== false) {
            $string = substr_replace($string, '', $pos, strlen($needle));
        }

        // Last
        $needle = '</'.$tag.'>';
        $pos = strrpos($string, $needle);
        if ($pos !== false) {
            $string = substr_replace($string, '', $pos, strlen($needle));
        }

        return $string;
    }

    public static function truncateText($text, $limit, $suffix = '') {

        $length = StringHelper::strlen($text);

        if ((int)$length < (int)$limit) {
            return $text;
        }

        $textOnlySpaces = preg_replace('/\s+/', ' ', $text);
        $textTruncated = StringHelper::substr($textOnlySpaces, 0, StringHelper::strpos($textOnlySpaces, " ", $limit));
        $textOutput = trim(StringHelper::substr($textTruncated, 0, StringHelper::strrpos($textTruncated, " ")));

        return $textOutput . $suffix;
    }

    public static function parseDbColumnParameter($string, &$params = array()) {


        $stringA = explode('=', $string);

        if (isset($stringA[1])) {

            $pos = strpos($stringA[1], 'E');

            if ($pos !== false) { $params['edit'] = true;}
        }

        if (isset($stringA[0])) {
            return PhocacartText::filterValue($stringA[0], 'alphanumeric2');
        }

	    return false;

    }
}
