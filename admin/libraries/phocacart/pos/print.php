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
defined( '_JEXEC' ) or die( 'Restricted access' );
use Joomla\CMS\Uri\Uri;
use Phoca\PhocaCart\Filesystem\File;

require JPATH_ADMINISTRATOR . '/components/com_phocacart/libraries/autoloadEscpos.php';
use Mike42\Escpos\Printer;
use Mike42\Escpos\EscposImage;

use Joomla\String\StringHelper;

class PhocacartPosPrint
{

	public $lineLength;
	public $lineLengthDoubleSize;
	public $lineSeparator;
	private $printer;
	private $connector	= null;
	private string $lineEnd;


	public function __construct($directPrint = 0) {




		$pC 							= PhocacartUtils::getComponentParameters();

		$this->lineLength 				= $pC->get( 'pos_print_line_length', 42 );
		$this->lineLengthDoubleSize 	= $pC->get( 'pos_print_line_length_double', 21 );
		$this->lineSeparator 			= $pC->get( 'pos_print_line_sep', '-' );
	//	$this->lineEnd					= $pC->get( 'pos_print_line_end', "\n" );
		$this->lineEnd					= "\n";
		if (StringHelper::strlen($this->lineSeparator) > 1) {
			$this->lineSeparator = substr($this->lineSeparator, 0, 1);
		}



		if ($directPrint) {

			$pos_print_connector			= $pC->get( 'pos_print_connector', 1 );
			$pos_print_connector_file		= $pC->get( 'pos_print_connector_file', '' );
			$pos_print_connector_dest		= $pC->get( 'pos_print_connector_dest', '' );
			$pos_print_connector_ip			= $pC->get( 'pos_print_connector_ip', '' );
			$pos_print_connector_port		= $pC->get( 'pos_print_connector_port', "9100" );
			$pos_print_connector_timeout	= $pC->get( 'pos_print_connector_timeout', 0 );



			switch($pos_print_connector) {


				case "2":

					if ($pos_print_connector_ip != '') {
						$this->connector 	= new Mike42\Escpos\PrintConnectors\NetworkPrintConnector($pos_print_connector_ip, $pos_print_connector_port, $pos_print_connector_timeout);
					} else {
						//throw new Exception('Printer Settings Error: No IP set', 500);
						PhocacartLog::add(2, 'Printer Settings Error: No IP set');
						return false;
					}
				break;

				case "3":
					if ($pos_print_connector_dest != '') {
						$this->connector 	= new Mike42\Escpos\PrintConnectors\WindowsPrintConnector($pos_print_connector_dest);
					} else {
						//throw new Exception('Printer Settings Error: No Destination set', 500);
						PhocacartLog::add(2, 'Printer Settings Error: No Destination set');
						return false;
					}
				break;

				case "4":
					if ($pos_print_connector_dest != '') {
						$this->connector 	= new Mike42\Escpos\PrintConnectors\CupsPrintConnector($pos_print_connector_dest);
					} else {
						//throw new Exception('Printer Settings Error: No Destination set', 500);
						PhocacartLog::add(2, 'Printer Settings Error: No Destination set');
						return false;
					}
				break;

				case 1:
				default:

					if ($pos_print_connector_file != '') {
						$this->connector 	= new Mike42\Escpos\PrintConnectors\FilePrintConnector($pos_print_connector_file);
					} else {
						//throw new Exception('Printer Settings Error: No File set', 500);
						PhocacartLog::add(2, 'Printer Settings Error: No File set');
						return false;
					}
				break;

			}
		}

	}

	public function printSeparator($sep = '', $class = '') {

		if ($sep == '') {
			$sep = $this->lineSeparator;
		}
		$lineEnd = $this->lineEnd;
		if ($sep == '_') {
			$lineEnd = $this->lineEnd . $this->lineEnd;
		}

		$startTag 	= '<div>';
		$endTag 	= '</div>';
		if ($class != '') {
			$startTag = '<div class="'.$class.'">';
		}

		if ($class == 'pDoubleSize') {
			return $startTag . str_repeat($sep, $this->lineLengthDoubleSize) .$endTag . $lineEnd;
		} else {
			return $startTag . str_repeat($sep, $this->lineLength) .$endTag. $lineEnd;
		}
	}

	public function printLineColumns($items, $ignoreLength = 0, $class = '') {


		$startTag 	= '<div>';
		$endTag 	= '</div>';
		if ($class != '') {
			$startTag = '<div class="'.$class.'">';
		}

		$item 						= array();
		$item['output'] 			= array();
		$item['outputcolumn1'] 		= '';
		$item['outputcolumn2'] 		= '';

		$item['length'] 			= array();
		$item['lengthcolumn1'] 		= 0;
		$item['lengthcolumn2'] 		= 0;

		$item['lengthsum'] 			= 0;

		$item['count']				= count($items);
		$item['lastitem']			= $item['count'] - 1;
		if (!empty($items)) {
			foreach($items as $k => $v) {
				$item['output'][$k] = $v;
				$item['length'][$k]	= StringHelper::strlen($v);
				$item['lengthsum']	+= StringHelper::strlen($v);

				// Divide all items into two columns (left and rights
				// First left column are all items except the last one
				// Second right column is only last item
				// If there is only one item (first = last) - don't set it as last
				if ($k == $item['lastitem'] && $k > 0){
					$item['outputcolumn2'] 		= $item['outputcolumn2'] != '' ? $item['outputcolumn2'] . ' ' . $v : $v;
					$item['lengthcolumn2']		= StringHelper::strlen($item['outputcolumn2']);
				} else {
					$item['outputcolumn1'] 		= $item['outputcolumn1'] != '' ? $item['outputcolumn1'] . ' ' . $v : $v;
					$item['lengthcolumn1']		= StringHelper::strlen($item['outputcolumn1']);
				}
			}
		}

		if ($ignoreLength == 1){
			return $startTag . trim(implode (' ', $item['output'])) . $endTag . $this->lineEnd;
		}

		if ($class == 'pDoubleSize') {
			$sizeRow = $this->lineLengthDoubleSize;
		} else {
			$sizeRow = $this->lineLength;
		}

		$o 				= '';
		$spaces 		= 0;
		$newLengthSum 	= (int)$item['lengthcolumn1'] + (int)$item['lengthcolumn2'];
		// We need to count each column because the became larger through join of spaces
		if ($newLengthSum < (int)$sizeRow || $newLengthSum == (int)$sizeRow) {
			// OK - we can add all items to one row
			$spaces = (int)$sizeRow - $newLengthSum;
			$length = (int)$item['lengthcolumn1'] + $spaces;

			//$item['outputcolumn1'] = str_replace('€', 'E', $item['outputcolumn1']);
			//$item['outputcolumn2'] = str_replace('€', 'E', $item['outputcolumn2']);

			$o .= $startTag . StringHelper::str_pad($item['outputcolumn1'], $length, ' ', STR_PAD_RIGHT) . $item['outputcolumn2'] . $endTag . $this->lineEnd;

		} else {
			$o .= $startTag . $item['outputcolumn1'] . $endTag . $this->lineEnd;
			// Possible TO DO - divide first column into blocks by items
			$o .= $startTag . $item['outputcolumn2'] . $endTag . $this->lineEnd;

		}

		return $o;
	}

	public function printLine($items, $class = '') {

		$o = '';
		$startTag 	= '<div>';
		$endTag 	= '</div>';
		if ($class != '') {
			$startTag = '<div class="'.$class.'">';
		}

		if (!empty($items)) {
			foreach($items as $k => $v) {
				$o .= $startTag . $v . $endTag . $this->lineEnd;
			}
		}
		return $o;
	}

	public function printImage($img = '') {

		$o = '';
		if ($img != '') {
			$o .= '<img src="'.Uri::root(false). ''.$img.'" data-src="'.$img.'" />'. $this->lineEnd;
		}
		return $o;
	}

	public function printFeed($number = 0) {

		$o = '';
		if ((int)$number > 0) {
			$o .= '<div class="pFeed" data-value="'.(int)$number.'"></div>'. $this->lineEnd;
		}
		return $o;
	}




	public function printOrder($o) {



		$oA = explode($this->lineEnd, $o);




		if (!empty($oA)) {
			try{
			if ($this->connector instanceof Mike42\Escpos\PrintConnectors\PrintConnector) {
				$printer 	= new Printer($this->connector);


				$doc = new DOMDocument();
				foreach ($oA as $k => $v) {

					if (strpos($v, '<div') === 0) {

						$doc->loadHTML($this->cleanOutput($v));
						$xpath = new DOMXPath($doc);
						$class = $xpath->evaluate("string(//div/@class)");

						switch($class) {

							case 'pDoubleSize':
								$printer->selectPrintMode(Printer::MODE_DOUBLE_HEIGHT | Printer::MODE_DOUBLE_WIDTH);
								$printer->setEmphasis(true);
								$printer->text(strip_tags($v) . $this->lineEnd);
								$printer->setEmphasis(false);
								$printer->selectPrintMode();
							break;

							case 'pDoubleSizeCenter':
								$printer->selectPrintMode(Printer::MODE_DOUBLE_HEIGHT | Printer::MODE_DOUBLE_WIDTH);
								$printer->setJustification(Printer::JUSTIFY_CENTER);
								$printer->text(strip_tags($v) . $this->lineEnd);
								$printer->setJustification();
								$printer->selectPrintMode();
							break;

							case 'pCenter':
								$printer->setJustification(Printer::JUSTIFY_CENTER);
								$printer->text(strip_tags($v) . $this->lineEnd);
								$printer->setJustification();
							break;

							case 'pLeft':
								$printer->setJustification(Printer::JUSTIFY_LEFT);
								$printer->text(strip_tags($v) . $this->lineEnd);
								$printer->setJustification();
							break;

							case 'pRight':
								$printer->setJustification(Printer::JUSTIFY_RIGHT);
								$printer->text(strip_tags($v) . $this->lineEnd);
								$printer->setJustification();
							break;

							case 'pUnderline':
								$printer->selectPrintMode(Printer::UNDERLINE_SINGLE);
								$printer->text(strip_tags($v) . $this->lineEnd);
								$printer->selectPrintMode();
							break;


							case 'pFeed':
								$protectionLimit = 20;
								$number = $xpath->evaluate("string(//div/@data-value)");
								if ((int)$number > 0 && (int)$number < $protectionLimit) {
									$printer->feed((int)$number);
								}
							break;

							default:
								$printer->text(strip_tags($v) . $this->lineEnd);
							break;
						}
					} else if (strpos($v, '<img') === 0) {

						$doc->loadHTML($this->cleanOutput($v));
						$xpath = new DOMXPath($doc);
						$src = $xpath->evaluate("string(//img/@data-src)");
						$srcAbs = JPATH_ROOT . '/' . $src;

						if (File::exists($srcAbs)) {
							$printer->setJustification(Printer::JUSTIFY_CENTER);
							$image = EscposImage::load($srcAbs, false);
							$printer->bitImage($image);
							$printer->setJustification();
						}

					} else {
						$printer->text(strip_tags($v) . $this->lineEnd);
					}

				}

				$printer -> feed(2);
				$printer -> cut();
				$printer -> close();

			} else {

				PhocacartLog::add(2, 'Server Printer Not Set - see POS Print Settings');
				ob_get_clean();
			}

			} catch ( Exception $e ) {

				PhocacartLog::add(2, 'Server Printer Error: '.$e->getMessage());
				ob_get_clean();
			}
		}

	//-	$connector = new Mike42\Escpos\PrintConnectors\WindowsPrintConnector("COM1");
	//-	$printer = new Printer($connector);


		//$printer -> setEmphasis(false);
	//-	$printer -> text($o);
		//$printer -> selectPrintMode(Printer::MODE_FONT_B);
		/*$printer -> setJustification(Printer::JUSTIFY_LEFT);

		 $printer -> setEmphasis(true);
		$printer -> text($o);
		$printer -> setEmphasis(false);
		$printer -> text($o);
		$printer -> setUnderline(true);
		$printer -> text($o);
		$printer -> setUnderline(false);


		$printer -> selectPrintMode(Printer::MODE_DOUBLE_HEIGHT | Printer::MODE_DOUBLE_WIDTH);

		$printer -> text($o);
		$printer -> selectPrintMode();// Reset
		/*$printer -> selectPrintMode(Printer::MODE_EMPHASIZED);
		$printer -> text($o);
		$printer -> selectPrintMode(Printer::MODE_DOUBLE_HEIGHT);
		$printer -> text($o);
		$printer -> selectPrintMode(Printer::MODE_DOUBLE_WIDTH);
		$printer -> text($o);
		$printer -> selectPrintMode(Printer::MODE_UNDERLINE);


		$printer -> text($o);*/


	//-	$printer -> setJustification();// Reset
	//-	$printer -> selectPrintMode();// Reset
	//-	$printer -> feed(5);
	//-	$printer -> cut();
		//$printer -> pulse();
	//- $printer -> close();

	}

	public function cleanOutput($text) {

		$text = str_replace('&', '&amp;', $text);
		return $text;
	}
}
?>
