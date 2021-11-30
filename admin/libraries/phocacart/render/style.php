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
use Joomla\CMS\Factory;

class PhocacartRenderStyle
{
    private static $styles = array();
    private function __construct(){}


    /*
     * Not all classes are loaded this way, only the classes which can change for different libraries like bootstrap3, bootstrap4, etc.
     */

    public static function getStyles() {

        if(empty(self::$styles)) {

            $app        = Factory::getApplication();
            $pC 		= PhocacartUtils::getComponentParameters();
            $pos        = PhocacartPos::isPos();
            $admin      = $app->isClient('administrator');



            if ($pos || $admin) {
                $theme	= 'bs5';
                $icons  = 'fa5';// only icons are now active for admin
            } else {
                $theme	    = $pC->get( 'theme', 'bs5' );
                $icons	    = $pC->get( 'icon_type', 'fa5' );
            }
            self::$styles = self::loadStyles($theme, $icons);
        }

        return self::$styles;

    }




    public static function loadStyles($theme, $icons) {

        $s  = array();

// ===============
// CLASS - Default
// ===============
$s['c']['class-type']		        = 'bs5';

$s['c']['row']                      = 'row';
$s['c']['row-item']                 = 'row-item';

$s['c']['col.xs12.sm1.md1']		    = 'col-xs-12 col-sm-1 col-md-1';
$s['c']['col.xs12.sm2.md2']		    = 'col-xs-12 col-sm-2 col-md-2';
$s['c']['col.xs12.sm3.md3']		    = 'col-sm-12 col-md-3 col-lg-3 col-xl-3';
$s['c']['col.xs12.sm4.md4']		    = 'col-sm-12 col-md-4 col-lg-4 col-xl-4';
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
$s['c']['btn.btn-danger']	        = 'btn btn-danger';
$s['c']['btn.btn-info']	            = 'btn btn-info';
$s['c']['btn.btn-primary.btn-sm']	= 'btn btn-primary btn-sm';
$s['c']['btn.btn-secondary.btn-sm']	= 'btn btn-secondary btn-sm';
$s['c']['btn.btn-success.btn-sm']	= 'btn btn-success btn-sm';
$s['c']['btn.btn-danger.btn-sm']	= 'btn btn-danger btn-sm';
$s['c']['btn.btn-warning.btn-sm']	= 'btn btn-warning btn-sm';
//$s['c']['btn.btn-success.btn-xs']	= 'btn btn-success btn-xs';
//$s['c']['btn.btn-danger.btn-xs']	= 'btn btn-danger btn-xs';
$s['c']['btn.btn-success.btn-lg']	= 'btn btn-success btn-lg';

$s['c']['btn.btn-default']	        = 'btn btn-primary';
$s['c']['btn.btn-default.btn-sm']	= 'btn btn-primary btn-sm';

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
$s['c']['tabactive']                = 'active show';
$s['c']['tabactvietab']             = '';

$s['c']['panel.panel-default']      = 'panel panel-default';
$s['c']['panel-heading']            = 'panel-heading';
$s['c']['panel-title']              = 'panel-title';
$s['c']['panel-body']               = 'panel-body';
$s['c']['panel-collapse.collapse']  = 'panel-collapse collapse';
$s['c']['panel-collapse.collapse.in'] = 'panel-collapse collapse show';

$s['c']['label.label-success']      = 'badge bg-success';
$s['c']['label.label-danger']       = 'badge bg-danger';
$s['c']['label.label-info']         = 'badge bg-info';

$s['c']['modal-common-close']       = '';// In BS attribute will be used, not class $s['a']['data-bs-dismiss-modal']
$s['c']['modal-btn-close']          = 'btn-close';
$s['c']['modal.zoom']               = 'pc-modal modal';
$s['c']['modal-dialog']             = 'modal-dialog';
$s['c']['modal-content']            = 'modal-content';
$s['c']['modal-header']             = 'modal-header';
$s['c']['modal-title']              = 'modal-title';
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

$s['c']['form-check']               = 'form-check';
$s['c']['form-check-input']         = 'form-check-input';
$s['c']['form-check-label']         = 'form-check-label';

$s['c']['inputbox.checkbox']	    = 'checkbox';
$s['c']['inputbox.radio']	        = 'radio';
$s['c']['inputbox.textarea']        = 'form-control';
$s['c']['inputbox']	                = 'form-control';
$s['c']['inputbox.form-control']    = 'form-control';
$s['c']['inputbox.form-select']     = 'form-select';
$s['c']['input-group']              = 'input-group';


$s['c']['input-large']	            = 'ph-input-l';
$s['c']['input-medium']             = 'ph-input-m';
$s['c']['input-small']              = 'ph-input-sm';
$s['c']['input-xsmall']             = 'ph-input-xs';

$s['c']['hastooltip']               = 'hasTooltipPc';// 'hasTooltip' can create JS problems on site (mixing different libraries)


$s['c']['alert-primary']	        = 'alert alert-primary';
$s['c']['alert-success']	        = 'alert alert-success';
$s['c']['alert-danger']	            = 'alert alert-danger';
$s['c']['alert-warning']	        = 'alert alert-warning';
$s['c']['alert-info']	            = 'alert alert-info';
$s['c']['alert-close']	            = 'btn-close';


// ===============
// ATTRIBUTES - Default
// ===============
$s['a']['modal-btn-close']          = '';
$s['a']['data-bs-dismiss-modal']    = ' data-bs-dismiss="modal"';
$s['a']['tab']                      = '';
$s['a']['accordion']                = '';
$s['a']['alert']	                = ' role="alert"';
$s['a']['alert-close']	            = ' data-bs-dismiss="alert"';


switch($theme) {


    case 'uikit':

// ===============
// CLASS - UIkit
// ===============

$s['c']['class-type']		        = 'uikit';

$s['c']['row']                      = 'row uk-grid equal-height uk-margin-remove';
$s['c']['row-item']                 = 'row-item';

$s['c']['col.xs12.sm1.md1']		    = 'uk-width-1-1@s uk-width-1-6@m uk-width-1-6@l';
$s['c']['col.xs12.sm2.md2']		    = 'uk-width-1-1@s uk-width-1-6@m uk-width-1-6@l';
$s['c']['col.xs12.sm3.md3']		    = 'uk-width-1-1@s uk-width-1-4@m uk-width-1-4@l';
$s['c']['col.xs12.sm4.md4']		    = 'uk-width-1-1@s uk-width-1-3@m uk-width-1-3@l';
$s['c']['col.xs12.sm5.md5']		    = 'uk-width-1-1@s uk-width-1-3@m uk-width-1-3@l';
$s['c']['col.xs12.sm6.md6']		    = 'uk-width-1-1@s uk-width-1-2@m uk-width-1-2@l';
$s['c']['col.xs12.sm7.md7']		    = 'uk-width-1-1@s uk-width-2-3@m uk-width-2-3@l';
$s['c']['col.xs12.sm8.md8']		    = 'uk-width-1-1@s uk-width-2-3@m uk-width-2-3@l';
$s['c']['col.xs12.sm9.md9']		    = 'uk-width-1-1@s uk-width-3-4@m uk-width-3-4@l ';
$s['c']['col.xs12.sm10.md10']	    = 'uk-width-1-1@s uk-width-5-6@m uk-width-5-6@l';
$s['c']['col.xs12.sm12.md12']	    = 'uk-width-1-1@s uk-width-1-1@m uk-width-1-1@l';

$s['c']['col.xs2.sm2.md2']	        = 'uk-width-1-6@s uk-width-1-6@m uk-width-1-6@l';
$s['c']['col.xs6.sm6.md6']	        = 'uk-width-1-2@s uk-width-1-2@m uk-width-1-2@l';
$s['c']['col.xs10.sm10.md10']	    = 'uk-width-5-6@s uk-width-5-6@m uk-width-5-6@l';

$s['c']['col.xs0.sm0.md0']	        = '';
$s['c']['col.xs3.sm3.md3']	        = 'uk-width-1-3@s uk-width-1-3@m uk-width-1-3@l';
$s['c']['col.xs4.sm4.md4']	        = 'uk-width-1-3@s uk-width-1-3@m uk-width-1-3@l';
$s['c']['col.xs8.sm8.md8']	        = 'uk-width-2-3@s uk-width-2-3@m uk-width-2-3@l';

$s['c']['col.xs0.sm6.md6']	        = 'uk-width-1-2@m uk-width-1-2@l';
$s['c']['col.xs8.sm4.md4']	        = 'uk-width-2-3@s uk-width-1-3@m uk-width-1-3@l';
$s['c']['col.xs4.sm2.md2']	        = 'uk-width-1-3@s uk-width-1-6@m uk-width-1-6@l';

$s['c']['btn-group']	            = 'uk-button-group';
$s['c']['btn']	                    = 'uk-button';
$s['c']['btn.btn-primary']	        = 'uk-button uk-button-primary';
$s['c']['btn.btn-secondary']	    = 'uk-button uk-button-secondary';
$s['c']['btn.btn-default']	        = 'uk-button uk-button-default';
$s['c']['btn.btn-danger']	        = 'uk-button uk-button-danger';
$s['c']['btn.btn-default.btn-sm']	= 'uk-button uk-button-default uk-button-small';
$s['c']['btn.btn-primary.btn-sm']	= 'uk-button uk-button-primary uk-button-small';
$s['c']['btn.btn-secondary.btn-sm']	= 'uk-button uk-button-secondary uk-button-small';
$s['c']['btn.btn-success.btn-sm']	= 'uk-button uk-button-success uk-button-small';
$s['c']['btn.btn-danger.btn-sm']	= 'uk-button uk-button-danger uk-button-small';
$s['c']['btn.btn-info']	            = 'uk-button uk-button-info';// not exist
$s['c']['btn.btn-success']	        = 'uk-button uk-button-success';// not exist
$s['c']['btn.btn-warning']	        = 'uk-button uk-button-warning';// not exist
$s['c']['btn.btn-info.btn-lg']	    = 'uk-button uk-button-info uk-button-large';// not exist
$s['c']['btn.btn-success.btn-sm']	= 'uk-button uk-button-success uk-button-small';// not exist
$s['c']['btn.btn-warning.btn-sm']	= 'uk-button uk-button-warning uk-button-small';// not exist
$s['c']['btn.btn-success.btn-lg']	= 'uk-button uk-button-success uk-button-large';// not exist
/*
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
*/

$s['c']['tabnav']                   = 'uk-tab';
$s['c']['nav-item']                 = '';
$s['c']['nav-link']                 = '';
$s['c']['tabcontent']               = 'uk-switcher uk-margin';
$s['c']['tabpane']                  = '';
$s['c']['tabactive']                = 'uk-active';
$s['c']['tabactvietab']             = 'uk-active';

$s['c']['panel.panel-default']      = 'uk-accordion';
$s['c']['panel-heading']            = '';
$s['c']['panel-title']              = 'uk-accordion-title';
$s['c']['panel-body']               = 'uk-accordion-content';
$s['c']['panel-collapse.collapse']  = '';
$s['c']['panel-collapse.collapse.in'] = 'uk-open';

$s['c']['label.label-success']      = 'uk-label uk-label-success';
$s['c']['label.label-danger']       = 'uk-label uk-label-danger';
$s['c']['label.label-info']         = 'uk-label uk-label-default';


$s['c']['modal-common-close']       = 'uk-modal-close'; // CLOSE MODAL without close icon
$s['c']['modal-btn-close']          = 'uk-modal-close-default';// CLOSE MODAL but to show icon we need attribute: $s['a']['modal-btn-close']
$s['c']['modal.zoom']               = 'pc-modal uk-modal';
$s['c']['modal-dialog']             = 'uk-modal-dialog';
$s['c']['modal-content']            = 'uk-modal-content';// not exist
$s['c']['modal-header']             = 'uk-modal-header';
$s['c']['modal-title']              = 'uk-modal-title';
$s['c']['modal-body']               = 'uk-modal-body';
$s['c']['modal-footer']             = 'uk-modal-footer';
$s['c']['modal-lg']                 = 'uk-modal-lg';// not exist

$s['c']['controls']                 = 'uk-form-controls';
$s['c']['control-label']            = 'uk-form-label';
$s['c']['control-group']            = 'uk-form-group';// Not exist
$s['c']['control-group.form_inline']= 'uk-form-group uk-inline';// Not exist
$s['c']['form-group']               = 'uk-form-group';// Not exist
$s['c']['form-control']             = 'uk-input';
$s['c']['form-inline']              = 'uk-inline';
$s['c']['form-horizontal']          = 'uk-form-horizontal';
$s['c']['form-horizontal.form-validate']    = 'uk-form-horizontal uk-form-validate';// Not exist

$s['c']['form-check']               = 'form-check';
$s['c']['form-check-input']         = 'form-check-input';
$s['c']['form-check-label']         = 'form-check-label';


$s['c']['inputbox.checkbox']	    = 'uk-checkbox';
$s['c']['inputbox.radio']	        = 'uk-radio';
$s['c']['inputbox.textarea']	    = 'uk-textarea';
$s['c']['inputbox']	                = 'uk-input';
$s['c']['inputbox.form-control']    = 'uk-input';
$s['c']['inputbox.form-select']     = 'uk-select';
$s['c']['input-group']              = 'input-group';

$s['c']['input-large']	            = 'uk-form-width-large';
$s['c']['input-medium']             = 'uk-form-width-medium';
$s['c']['input-small']              = 'uk-form-width-small';
$s['c']['input-xsmall']             = 'uk-form-width-xsmall';

$s['c']['hastooltip']               = 'hasTooltipPc';// 'hasTooltip' can create JS problems on site (mixing different libraries)

$s['c']['alert-primary']	        = 'uk-alert-primary';
$s['c']['alert-success']	        = 'uk-alert-success';
$s['c']['alert-danger']	            = 'uk-alert-danger';
$s['c']['alert-warning']	        = 'uk-alert-warning';
$s['c']['alert-info']	            = 'uk-alert-info';
$s['c']['alert-close']	            = 'uk-alert-close';



// ===============
// ATTRIBUTES - UIkit
// ===============

$s['a']['modal-btn-close']          = ' uk-close';
$s['a']['data-bs-dismiss-modal']    = '';// Done per class in UIkit ($s['c']['modal-common-close'])
$s['a']['tab']                      = ' uk-tab';
$s['a']['accordion']                = ' uk-accordion="multiple: true"';
$s['a']['alert']	                = ' uk-alert';
$s['a']['alert-close']	            = ' uk-close';

    break;

    case 'bs5':
    default:
        // Moved to default
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
