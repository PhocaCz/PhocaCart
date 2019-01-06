<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
defined( '_JEXEC' ) or die();
jimport( 'joomla.application.component.modellist' );

class PhocaCartCpModelPhocaCartEditTax extends JModelList
{
	protected	$option 		= 'com_phocacart';
	
	public function getData() {
	
		$app	= JFactory::getApplication();
		$id		= $app->input->get('id', 0, 'int');
		$type	= $app->input->get('type', 0, 'type');// 1 country, 2 region
		
		$db = JFactory::getDBO();
		$query = 'SELECT a.id, a.title, a.code2, a.image';
		if ($type == 1) {
			$query .= ' FROM #__phocacart_countries AS a';
		} else {
			$query .= ' FROM #__phocacart_regions AS a';
		}
		$query .= ' WHERE a.id = '.(int)$id
		. ' LIMIT 1';
		$db->setQuery( $query );
		$item = $db->loadObject();
		return $item;

	}
	
	public function getCountryTaxData() {
	
		$app	= JFactory::getApplication();
		$id		= $app->input->get('id', 0, 'int');
		
		if ((int)$id > 0) {
			return PhocacartTax::getTaxesByCountry($id);
		}
	}
	
	public function getRegionTaxData() {
	
		$app	= JFactory::getApplication();
		$id		= $app->input->get('id', 0, 'int');
		
		if ((int)$id > 0) {
			return PhocacartTax::getTaxesByRegion($id);
		}
	}
	
	public function editTax($data) {
		
		$data['id']			= (int)$data['id'];
		$data['type']		= (int)$data['type'];

		$row 				= $this->getTable('PhocacartOrder', 'Table');
		$user 				= JFactory::getUser();

		if (!empty($data)) {
			
			if(isset($data['id']) && $data['id'] > 0) {
				
				$countryOrRegionId = $data['id'];
				
			
				
				$valuesNew 		= array();
				$valuesUpdate 	= array();	
				foreach($data as $k => $v) {
					
					// Ignore the $data['id']
					if (isset($v['tax_id'])) {
						
						if (!isset($v['title'])) {
							$v['title'] = '';
						}
						
						if (!isset($v['alias'])) {
							$v['alias'] = '';
						}
						
						if (!isset($v['tax_rate'])) {
							$v['tax_rate'] = '';
						}
						
						// We need to differentiate between 0 and ''
						// if 0  ... the VAT is 0
						// if '' ... the VAT is not set
						// if -1 ... it was set in past but now it is inactive (but we still hold the info about the id for the country/region and tax id combination
						
						if ($data['type'] == 2) {
						/*	$q = ' DELETE '
							.' FROM #__phocacart_tax_regions'
							. ' WHERE region_id = '.(int)$data['id'];*/
							
							$q = ' SELECT id '
							.' FROM #__phocacart_tax_regions'
							. ' WHERE region_id = '.(int)$data['id']
							. ' AND tax_id = '.(int)$v['tax_id'];
						} else {
							/*$q = ' DELETE '
							.' FROM #__phocacart_tax_countries'
							. ' WHERE country_id = '.(int)$data['id'];*/
							
							$q = ' SELECT id '
							.' FROM #__phocacart_tax_countries'
							. ' WHERE country_id = '.(int)$data['id']
							. ' AND tax_id = '.(int)$v['tax_id'];
						}
			
						$this->_db->setQuery($q);
						//$this->_db->execute();
						
						$idExists 		= $this->_db->loadResult();
						
						
						if ((int)$idExists > 0) {
							
							$title = $v['title'];
							$alias = $v['alias'];
							$taxRate = $v['tax_rate'];
							if ($v['tax_rate'] == '') {
								
								$title = '';
								$alias = '';
								$taxRate = -1;
							}
							
							$q = '';
							if ($data['type'] == 2) {
								$q .= ' UPDATE #__phocacart_tax_regions';
								$q .= ' SET tax_id = '.(int)$v['tax_id'].','
								.' region_id = '.(int)$countryOrRegionId.','
								.' title = '.$this->_db->quote($title).','
								.' alias = '.$this->_db->quote($alias).','
								.' tax_rate = '.$this->_db->quote($taxRate)
								.' WHERE id = '.(int)$idExists;
							} else {
								$q .= ' UPDATE #__phocacart_tax_countries';
								$q .= ' SET tax_id = '.(int)$v['tax_id'].','
								.' country_id = '.(int)$countryOrRegionId.','
								.' title = '.$this->_db->quote($title).','
								.' alias = '.$this->_db->quote($alias).','
								.' tax_rate = '.$this->_db->quote($taxRate)
								.' WHERE id = '.(int)$idExists;
							}
						
							//$valuesUpdate[] = $q;
							$this->_db->setQuery($q);
							$this->_db->execute();
						} else {
							if ($v['tax_rate'] == '') {
								
							} else {
								$valuesNew[] = '('.(int)$v['tax_id'].', '.(int)$countryOrRegionId.', '.$this->_db->quote($v['title']).', '.$this->_db->quote($v['alias']).', '.$this->_db->quote($v['tax_rate']).')';			
							}
						}
					}
				}
				
	
				// Update the existing items
				if (!empty($valuesUpdate)) {
					
					// update directly by each item
					//$q = implode(';', $valuesUpdate);
					//$this->_db->setQuery($q);
					//$this->_db->execute();
					
				}
				
				// And not existing items
				if (!empty($valuesNew)) {
					if ($data['type'] == 2) {
						$q = ' INSERT INTO #__phocacart_tax_regions (tax_id, region_id, title, alias, tax_rate)';
					} else {
						$q = ' INSERT INTO #__phocacart_tax_countries (tax_id, country_id, title, alias, tax_rate)';
					}
					$q .= ' VALUES ' .implode(',', $valuesNew);
					
					$this->_db->setQuery($q);
					$this->_db->execute();
					
				}
				
				return true;
				
			}
		}
		
		return false;
	}
	
	public function emptyInformation($id, $type) {
		
		if ((int)$id > 0) {
		
			$db = JFactory::getDBO();
			if ($type == 2) {
				//$query = 'DELETE FROM #__phocacart_tax_regions WHERE region_id = '.(int)$id;
				$query = 'UPDATE #__phocacart_tax_regions SET tax_rate = -1, title = \'\', alias = \'\' WHERE region_id = '.(int)$id;
			} else {
				//$query = 'DELETE FROM #__phocacart_tax_countries WHERE country_id = '.(int)$id;
				$query = 'UPDATE #__phocacart_tax_countries SET tax_rate = -1, title = \'\', alias = \'\' WHERE country_id = '.(int)$id;
			}

			$db->setQuery( $query );

			if ($db->execute()) {
				return true;
			} else {
				return false;
			}
		}
	}
}
?>