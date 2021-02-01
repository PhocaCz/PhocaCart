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

class PhocacartRenderStyle
{
    private static $styles = array();
    private function __construct(){}


    /*
     * Not all classes are loaded this way, only the classes which can change for different libraries like bootstrap3, bootstrap4, etc.
     */

    public static function getStyles() {

        if(empty(self::$styles)) {

            $app        = JFactory::getApplication();
            $pC 		= PhocacartUtils::getComponentParameters();
            $pos        = PhocacartPos::isPos();
            $admin      = $app->isClient('administrator');



            if ($pos || $admin) {
                $theme	= 'bs3';
                $icons  = 'bs';// only icons are now active for admin
            } else {
                $theme	    = $pC->get( 'theme', 'bs3' );
                $icons	    = $pC->get( 'icon_type', 'bs' );
            }
            self::$styles = self::loadStyles($theme, $icons);
        }

        return self::$styles;

    }




    public static function loadStyles($theme, $icons) {


        $s                      = array();

// ===============
// CLASS - Default
// ===============
$s['c']['class-type']		        = 'bs3';

$s['c']['row']                      = 'row';
$s['c']['row-item']                 = 'row-item';
$s['c']['row.row-flex']             = 'row row-flex';

$s['c']['col.xs12.sm1.md1']		    = 'col-xs-12 col-sm-1 col-md-1';
$s['c']['col.xs12.sm2.md2']		    = 'col-xs-12 col-sm-2 col-md-2';
$s['c']['col.xs12.sm3.md3']		    = 'col-xs-12 col-sm-3 col-md-3';
$s['c']['col.xs12.sm4.md4']		    = 'col-xs-12 col-sm-4 col-md-4';
$s['c']['col.xs12.sm5.md5']		    = 'col-xs-12 col-sm-5 col-md-5';
$s['c']['col.xs12.sm6.md6']		    = 'col-xs-12 col-sm-6 col-md-6';
$s['c']['col.xs12.sm7.md7']		    = 'col-xs-12 col-sm-7 col-md-7';
$s['c']['col.xs12.sm8.md8']		    = 'col-xs-12 col-sm-8 col-md-8';
$s['c']['col.xs12.sm9.md9']		    = 'col-xs-12 col-sm-9 col-md-9';
$s['c']['col.xs12.sm10.md10']	    = 'col-xs-12 col-sm-10 col-md-10';
$s['c']['col.xs12.sm12.md12']	    = 'col-xs-12 col-sm-12 col-md-12';

$s['c']['col.xs2.sm2.md2']	        = 'col-xs-2 col-sm-2 col-md-2';
$s['c']['col.xs6.sm6.md6']	        = 'col-xs-6 col-sm-6 col-md-6';
$s['c']['col.xs10.sm10.md10']	    = 'col-xs-10 col-sm-10 col-md-10';

$s['c']['col.xs0.sm0.md0']	        = 'col-xs-0 col-sm-0 col-md-0';
$s['c']['col.xs3.sm3.md3']	        = 'col-xs-3 col-sm-3 col-md-3';
$s['c']['col.xs4.sm4.md4']	        = 'col-xs-4 col-sm-4 col-md-4';
$s['c']['col.xs8.sm8.md8']	        = 'col-xs-8 col-sm-8 col-md-8';


$s['c']['col.xs0.sm6.md6']	        = 'col-xs-0 col-sm-6 col-md-6';
$s['c']['col.xs8.sm4.md4']	        = 'col-xs-8 col-sm-4 col-md-4';
$s['c']['col.xs4.sm2.md2']	        = 'col-xs-4 col-sm-2 col-md-2';

$s['c']['btn-group']	            = 'btn-group';
$s['c']['btn']	                    = 'btn';
$s['c']['btn.btn-primary']	        = 'btn btn-primary';
$s['c']['btn.btn-secondary']	    = 'btn btn-secondary';
$s['c']['btn.btn-success']	        = 'btn btn-success';
$s['c']['btn.btn-default']	        = 'btn btn-default';
$s['c']['btn.btn-danger']	        = 'btn btn-danger';
$s['c']['btn.btn-info']	            = 'btn btn-info';
$s['c']['btn.btn-default.btn-sm']	= 'btn btn-default btn-sm';
$s['c']['btn.btn-primary.btn-sm']	= 'btn btn-primary btn-sm';
$s['c']['btn.btn-success.btn-sm']	= 'btn btn-success btn-sm';
$s['c']['btn.btn-danger.btn-sm']	= 'btn btn-danger btn-sm';
$s['c']['btn.btn-warning.btn-sm']	= 'btn btn-warning btn-sm';
//$s['c']['btn.btn-success.btn-xs']	= 'btn btn-success btn-xs';
//$s['c']['btn.btn-danger.btn-xs']	= 'btn btn-danger btn-xs';
$s['c']['btn.btn-success.btn-lg']	= 'btn btn-success btn-lg';

$s['c']['pull-left']		        = 'ph-pull-left';
$s['c']['pull-right']		        = 'ph-pull-right';

$s['c']['caption']			        = 'caption';
$s['c']['thumbnail']			    = 'thumbnail';
$s['c']['img-responsive']	        = 'img-responsive';

$s['c']['grid']			            = 'grid';
$s['c']['cat_item_grid']            = 'jf_ph_cat_item_grid';
$s['c']['cat_item_btns']            = 'jf_ph_cat_item_btns_wrap';
$s['c']['cat_list']					= 'jf_ph_cat_list';
$s['c']['item_review_form']			= 'jf_ph_cart_item_review';

$s['c']['tabnav']                   = 'nav nav-tabs';
$s['c']['nav-item']                 = 'nav-item';
$s['c']['nav-link']                 = 'nav-link';
$s['c']['tabcontent']               = 'tab-content';
$s['c']['tabpane']                  = 'tab-pane fade';
$s['c']['tabactive']                = 'active in';
$s['c']['tabactvietab']             = 'active in';

$s['c']['panel.panel-default']      = 'panel panel-default';
$s['c']['panel-heading']            = 'panel-heading';
$s['c']['panel-title']              = 'panel-title';
$s['c']['panel-body']               = 'panel-body';
$s['c']['panel-collapse.collapse']  = 'panel-collapse collapse';
$s['c']['panel-collapse.collapse.in'] = 'panel-collapse collapse in';

$s['c']['label.label-success']      = 'label label-success badge badge-success';
$s['c']['label.label-danger']       = 'label label-important label-danger badge badge-danger';
$s['c']['label.label-info']         = 'label label-info badge badge-info';

$s['c']['modal.zoom']               = 'modal zoom';
$s['c']['modal-dialog']             = 'modal-dialog';
$s['c']['modal-content']            = 'modal-content';
$s['c']['modal-header']             = 'modal-header';
$s['c']['modal-body']               = 'modal-body';
$s['c']['modal-footer']             = 'modal-footer';
$s['c']['modal-lg']                 = 'modal-lg';

$s['c']['controls']                 = 'controls';
$s['c']['control-label']            = 'control-label';
$s['c']['control-group']            = 'control-group';
$s['c']['control-group.form_inline']= 'control-group form-inline';
$s['c']['form-group']               = 'form-group';
$s['c']['form-control']             = 'form-control';
$s['c']['form-inline']              = 'form-inline';
$s['c']['form-horizontal']          = 'form-horizontal';
$s['c']['form-horizontal.form-validate']    = 'form-horizontal form-validate';
$s['c']['checkbox']	                = 'checkbox';
$s['c']['inputbox']	                = 'inputbox';
$s['c']['inputbox.form-control']    = 'inputbox form-control';

$s['c']['hastooltip']               = 'hasTooltipPc';// 'hasTooltip' can create JS problems on site (mixing different libraries)


        switch($theme) {

            case 'bs4':

                $s['c']['col.xs12.sm3.md3']		    = 'col-sm-12 col-md-3 col-lg-3 col-xl-3';
                $s['c']['col.xs12.sm4.md4']		    = 'col-sm-12 col-md-4 col-lg-4 col-xl-4';

                $s['c']['class-type']		        = 'bs4';
                $s['c']['modal.zoom']               = 'modal';

                $s['c']['btn.btn-default']	        = 'btn btn-primary';
                $s['c']['btn.btn-default.btn-sm']	= 'btn btn-primary btn-sm';
                $s['c']['tabactive']                = 'active show';
                $s['c']['tabactvietab']             = '';

                $s['c']['panel-collapse.collapse.in'] = 'panel-collapse collapse show';

                $s['c']['label.label-success']      = 'badge badge-success';
                $s['c']['label.label-danger']       = 'badge badge-danger';
                $s['c']['label.label-info']         = 'badge badge-info';

            break;
            case 'bootstrap3':
            default:
                // Default
			break;
        }


// ===============
// ICONS - Default
// ===============
$pf = 'glyphicon glyphicon-';
$sf = '';


// Icon type name
$s['i']['icon-type']		= 'bs';

$s['i']['view-category']    = $pf.'search'.$sf;
$s['i']['view-product']     = $pf.'search'.$sf;
$s['i']['back-category']    = $pf.'arrow-left'.$sf;
$s['i']['ok']               = $pf.'ok'.$sf;
$s['i']['not-ok']           = $pf.'remove'.$sf;
$s['i']['remove']           = $pf.'remove'.$sf;
$s['i']['clear']            = $pf.'remove'.$sf;
$s['i']['remove-circle']    = $pf.'remove-sign'.$sf;
$s['i']['edit']             = $pf.'edit'.$sf;
$s['i']['plus']             = $pf.'plus'.$sf;
$s['i']['minus']            = $pf.'minus'.$sf;
$s['i']['chevron-up']       = $pf.'chevron-up'.$sf;
$s['i']['chevron-down']     = $pf.'chevron-down'.$sf;
$s['i']['shopping-cart']    = $pf.'shopping-cart'.$sf;
$s['i']['question-sign']    = $pf.'question-sign'.$sf;
$s['i']['info-sign']        = $pf.'info-sign'.$sf;
$s['i']['compare']          = $pf.'stats'.$sf;
$s['i']['ext-link']         = $pf.'share'.$sf;
$s['i']['int-link']         = $pf.'share-alt'.$sf;
$s['i']['download']         = $pf.'download'.$sf;
$s['i']['download-alt']     = $pf.'download-alt'.$sf;
$s['i']['quick-view']       = $pf.'eye-open'.$sf;
$s['i']['wish-list']        = $pf.'heart'.$sf;
$s['i']['ban']              = $pf.'ban-circle'.$sf;
$s['i']['refresh']          = $pf.'refresh'.$sf;
$s['i']['trash']            = $pf.'trash'.$sf;
$s['i']['triangle-bottom']  = $pf.'triangle-bottom'.$sf;
$s['i']['triangle-right']   = $pf.'triangle-right'.$sf;
$s['i']['save']             = $pf.'floppy-disk'.$sf;
$s['i']['user']             = $pf.'user'.$sf;
$s['i']['grid']             = $pf.'th-large'.$sf;
$s['i']['gridlist']         = $pf.'th-list'.$sf;
$s['i']['list']             = $pf.'align-justify'.$sf;
$s['i']['next']             = $pf.'arrow-right'.$sf;
$s['i']['prev']             = $pf.'arrow-left'.$sf;
$s['i']['submit']           = $pf.'ok'.$sf;
$s['i']['list-alt']         = $pf.'list-alt'.$sf;
$s['i']['invoice']          = $pf.'list-alt'.$sf;
$s['i']['del-note']         = $pf.'barcode'.$sf;
$s['i']['order']            = $pf.'search'.$sf;
$s['i']['receipt']          = $pf.'th-list'.$sf;
$s['i']['print']            = $pf.'print'.$sf;
$s['i']['barcode']          = $pf.'barcode'.$sf;
$s['i']['search']           = $pf.'search'.$sf;
$s['i']['payment-method']   = $pf.'credit-card'.$sf;
$s['i']['shipping-method']  = $pf.'barcode'.$sf;
$s['i']['log-out']          = $pf.'arrow-left'.$sf;
$s['i']['calendar']         = $pf.'calendar'.$sf;
$s['i']['globe']            = $pf.'globe'.$sf;
$s['i']['upload']           = $pf.'upload'.$sf;
        switch($icons) {

            case 'fa':
                case 'fa5':

$pf = 'fa fa-';
$sf = ' fa-fw';

// Icon type name
$s['i']['icon-type']		= 'fa';

$s['i']['view-category']    = $pf.'search'.$sf;
$s['i']['view-product']     = $pf.'search'.$sf;
$s['i']['back-category']    = $pf.'arrow-left'.$sf;
$s['i']['ok']               = $pf.'check'.$sf;
$s['i']['not-ok']           = $pf.'remove'.$sf;
$s['i']['remove']           = $pf.'remove'.$sf;
$s['i']['clear']            = $pf.'remove'.$sf;
$s['i']['remove-circle']    = $pf.'times-circle'.$sf;
$s['i']['edit']             = $pf.'edit'.$sf;
$s['i']['plus']             = $pf.'plus'.$sf;
$s['i']['minus']            = $pf.'minus'.$sf;
$s['i']['chevron-up']       = $pf.'chevron-up'.$sf;
$s['i']['chevron-down']     = $pf.'chevron-down'.$sf;
$s['i']['shopping-cart']    = $pf.'shopping-bag'.$sf;
$s['i']['question-sign']    = $pf.'question-circle'.$sf;
$s['i']['info-sign']        = $pf.'info-circle'.$sf;
$s['i']['compare']          = $pf.'clone'.$sf;
$s['i']['ext-link']         = $pf.'share'.$sf;
$s['i']['int-link']         = $pf.'share-alt'.$sf;
$s['i']['download']         = $pf.'download'.$sf;
$s['i']['quick-view']       = $pf.'eye'.$sf;
$s['i']['wish-list']        = $pf.'heart'.$sf;
$s['i']['ban']              = $pf.'ban'.$sf;
$s['i']['refresh']          = $pf.'refresh'.$sf;
$s['i']['trash']            = $pf.'trash'.$sf;
$s['i']['triangle-bottom']  = $pf.'caret-down'.$sf;
$s['i']['triangle-right']   = $pf.'caret-right'.$sf;
$s['i']['save']             = $pf.'save'.$sf;
$s['i']['user']             = $pf.'user'.$sf;
$s['i']['grid']             = $pf.'th-large'.$sf;
$s['i']['gridlist']         = $pf.'th-list'.$sf;
$s['i']['list']             = $pf.'align-justify'.$sf;
$s['i']['next']             = $pf.'arrow-right'.$sf;
$s['i']['prev']             = $pf.'arrow-left'.$sf;
$s['i']['submit']           = $pf.'check'.$sf;
$s['i']['list-alt']         = $pf.'list-alt'.$sf;
$s['i']['invoice']          = $pf.'list-alt fa-file-invoice-dollar'.$sf;
$s['i']['del-note']         = $pf.'barcode fa-file-invoice'.$sf;
$s['i']['order']            = $pf.'search fa-file-alt'.$sf;
$s['i']['receipt']          = $pf.'th-list fa-receipt'.$sf;
$s['i']['print']            = $pf.'print'.$sf;
$s['i']['barcode']          = $pf.'barcode'.$sf;
$s['i']['search']           = $pf.'search'.$sf;
$s['i']['payment-method']   = $pf.'credit-card'.$sf;
$s['i']['shipping-method']  = $pf.'barcode'.$sf;
$s['i']['log-out']          = $pf.'sign-out-alt'.$sf;
$s['i']['calendar']         = $pf.'calendar'.$sf;
$s['i']['globe']            = $pf.'globe'.$sf;
$s['i']['upload']           = $pf.'upload'.$sf;

if ($icons == 'fa5') {

    $s['i']['icon-type']		= 'fa fa5';
    $s['i']['refresh']          = $pf.'redo'.$sf;
    $s['i']['remove']          = $pf.'times'.$sf;
    $s['i']['clear']          = $pf.'times'.$sf;
}

            break;
            case 'bs3':
                case 'bs4':
            default:
                // Default
            break;
        }

        return $s;

    }

    public final function __clone() {
        throw new Exception('Function Error: Cannot clone instance of Singleton pattern', 500);
        return false;
    }
}
?>
