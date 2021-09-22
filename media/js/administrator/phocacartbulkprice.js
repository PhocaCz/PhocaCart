/*
 * @package   Phoca Cart
 * @author    Jan Pavelka - https://www.phoca.cz
 * @copyright Copyright (C) Jan Pavelka https://www.phoca.cz
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 and later
 * @cms       Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license   http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 */



function phBulkPrice(dataUrl) {
   
   var phVars = Joomla.getOptions('phVars');
   var phLang = Joomla.getOptions('phLang');
   var phItemCount = 1;
   var phOutputBoxId    = '#phBulkPriceOutputBox';


   // Clear Output box
   jQuery(phOutputBoxId).html('');

   function phGetNextItem() {
   
      jQuery.ajax({
         url: phVars['urlbulkprice'] +'&p=' + phItemCount + '&' + dataUrl,
         method: 'GET',
         async: true,
         success: function(data) {

            if (data.status == 1) {
               ++phItemCount;

               var currentOutput = jQuery(phOutputBoxId).html();
               var newOutput = data.output + currentOutput;
               jQuery(phOutputBoxId).html(newOutput);

               if (data.continue == 1) {
                  phGetNextItem();
               }
            } else {
               
               var currentOutput = jQuery(phOutputBoxId).html();
               var newOutput = data.output + currentOutput;
               jQuery(phOutputBoxId).html(newOutput);
            }

         },// end success
         
         error: function (xhr, ajaxOptions, thrownError) {

            var currentOutput = jQuery(phOutputBoxId).html();
            var newOutput = xhr.status + ' ' + thrownError;
            jQuery(phOutputBoxId).html(newOutput);
            }
      });// end ajax
      
   }
   phGetNextItem();
}

 
jQuery(document).ready(function(){
	jQuery('#phBulkPriceRun').on('submit', function(e){ 

      e.stopPropagation();
      e.preventDefault();
      var data = jQuery(this).serialize();
      phBulkPrice(data);
   })

   jQuery('#phBulkPriceRevert').on('submit', function(e){ 

      e.stopPropagation();
      e.preventDefault();
      var data = jQuery(this).serialize();
      phBulkPrice(data);
   })
})