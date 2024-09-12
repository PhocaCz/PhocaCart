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
use Joomla\CMS\Language\Associations;
use Joomla\CMS\Language\Text;
use Joomla\Registry\Registry;
use Joomla\CMS\Router\Route;
use Joomla\CMS\HTML\HTMLHelper;
use Phoca\PhocaCart\Dispatcher\Dispatcher;

class PhocacartRenderFront
{
    public static function renderNewIcon($date, $size = 1, $statusOnly = 0)
    {
        $app = Factory::getApplication();
        $params = $app->getParams();
        $new = $params->get('display_new', 0);

        $o = '';
        if ($new == 0) {
            $o .= '';
        } else {
            $dateAdded = strtotime($date, time());
            $dateToday = time();
            $dateExists = $dateToday - $dateAdded;
            $dateNew = (int)$new * 24 * 60 * 60;
            if ($dateExists < $dateNew) {
                $o .= '<div class="ph-corner-icon-wrapper ph-corner-icon' . $size . '-wrapper"><div class="ph-corner-icon ph-corner-icon' . $size . ' ph-corner-icon-new">' . Text::_('COM_PHOCACART_LABEL_TXT_NEW') . '</div></div>';

                if ($statusOnly == 1) {
                    return true;
                }

            }
        }

        if ($statusOnly == 1) {
            return false;
        }

        return $o;
    }

    public static function renderHotIcon($sales, $size = 1, $statusOnly = 0)
    {
        $app = Factory::getApplication();
        $params = $app->getParams();
        $hot = $params->get('display_hot', 0);

        $o = '';
        if ($hot == 0) {
            $o .= '';
        } else {
            if ($sales > $hot || $sales == $hot) {
                $o .= '<div class="ph-corner-icon-wrapper  ph-corner-icon' . $size . '-wrapper"><div class="ph-corner-icon ph-corner-icon' . $size . ' ph-corner-icon-hot">' . Text::_('COM_PHOCACART_HOT') . '</div></div>';

                if ($statusOnly == 1) {
                    return true;
                }

            }
        }

        if ($statusOnly == 1) {
            return false;
        }

        return $o;
    }

    public static function renderFeaturedIcon($featured, $size = 1, $statusOnly = 0)
    {
        $app = Factory::getApplication();
        $params = $app->getParams();
        $feat = $params->get('display_featured', '');

        $o = '';
        if ($featured == 0 || $feat == '') {
            $o .= '';
        } else {
            $o .= '<div class="ph-corner-icon-wrapper  ph-corner-icon' . $size . '-wrapper"><div class="ph-corner-icon ph-corner-icon' . $size . ' ph-corner-icon-featured">' . Text::_($feat) . '</div></div>';

            if ($statusOnly == 1) {
                return true;
            }
        }

        if ($statusOnly == 1) {
            return false;
        }

        return $o;
    }

    public static function prepareDocument($document, $params, $category = false, $item = false, $header = '')
    {

        $app = Factory::getApplication();
        $menus = $app->getMenu();
        $menu = $menus->getActive();
        $pathway = $app->getPathway();

        $metakey = $params->get('cart_metakey', '');
        $metadesc = $params->get('cart_metadesc', '');
        $render_canonical_url = $params->get('render_canonical_url', 0);
        $nameInTitle = 1;// TO DO possible parameter Category or Title name

        $viewCurrent = $app->input->get('view');
        $viewLink = '';
        if (isset($menu->query['view'])) {
            $viewLink = $menu->query['view'];
        }

        $type = '';
        $name = new stdClass();
        if (!empty($item) && isset($item->title)) {
            $name = $item;
            $type = 'item';
        } else if (!empty($category) && isset($category->title)) {
            $name = $category;
            $type = 'category';
        }

        if ($menu) {
            $params->def('page_heading', $params->get('page_title', $menu->title));
        } else {
            $params->def('page_heading', Text::_('JGLOBAL_ARTICLES'));
        }

        $title = $params->get('page_title', '');


        if (isset($name->title) && $name->title != '') {
            $title = $name->title;
        }

        if (isset($name->title_long) && $name->title_long != '') {
            $title = $name->title_long;
        }

        if ($viewLink != $viewCurrent && $header != '') {
            $title = $header;
        } else if (empty($title)) {
            $title = htmlspecialchars_decode($app->get('sitename'));
        } else if ($app->get('sitename_pagetitles', 0) == 1) {
            $titleInclSiteName = Text::sprintf('JPAGETITLE', htmlspecialchars_decode($app->get('sitename')), $title);
            if ($nameInTitle == 1 && isset($name->title) && $name->title != '' && $title != $name->title) {
                $title = $titleInclSiteName . ' - ' . $name->title;
            } else {
                $title = $titleInclSiteName;
            }
        } else if ($app->get('sitename_pagetitles', 0) == 2) {
            if ($nameInTitle == 1 && isset($name->title) && $name->title != '') {
                if ($title != $name->title) {
                    $title = $title . ' - ' . $name->title;
                }
            }
            $title = Text::sprintf('JPAGETITLE', $title, htmlspecialchars_decode($app->get('sitename')));
        }

        if ($type == 'category' && isset($category->metatitle) && $category->metatitle != '') {
            $title = $category->metatitle;
        }
        if ($type == 'item' && isset($item->metatitle) && $item->metatitle != '') {
            $title = $item->metatitle;
        }

        if ($type == 'item' && $render_canonical_url == 1) {
            $canonical = PhocacartRoute::getProductCanonicalLink($item->id, $item->catid, $item->alias, $item->catalias, $item->preferred_catid);
            $document->addHeadLink(htmlspecialchars($canonical), 'canonical');
        }

        $document->setTitle($title);


       /* if (isset($item->image) && $item->image != '') {
            $pathItem		= PhocacartPath::getPath('productimage');
            $thumbnail      = PhocacartImage::getThumbnailName($pathItem, $item->image, 'large');
            if (isset($thumbnail->rel)) {
                $document->setMetadata('og:image', Uri::base(true) . '/' .$thumbnail->rel);
            }
        } else if (isset($category->image) && $category->image != '') {
            $pathItem		= PhocacartPath::getPath('categoryimage');
            $document->setMetadata('og:image', Uri::base(true) . $pathItem['orig_rel_ds'] .$category->image);
        }*/


        if (isset($item->metadesc) && $item->metadesc != '') {
            $document->setMetadata('description', $item->metadesc);
        } else if (isset($item->description) && strip_tags($item->description) != '') {

            $description = str_replace( '<', ' <', $item->description);
            $description = strip_tags($description);
            $description = preg_replace("/\s\s+/", " ", $description);
            $description = htmlspecialchars(trim($description), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            $document->setMetadata('description', $description);

        } else if (isset($item->description_long) && strip_tags($item->description_long) != '') {

            $description = str_replace( '<', ' <', $item->description_long);
            $description = strip_tags($description);
            $description = preg_replace("/\s\s+/", " ", $description);
            $description = PhocacartText::truncateText($description, 156, ' ...');// 160 - (" ...")
            $description = htmlspecialchars(trim($description), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            $document->setMetadata('description', $description);

        } else if (isset($category->metadesc) && $category->metadesc != '') {
            $document->setMetadata('description', $category->metadesc);
        }  else if (isset($category->description) && strip_tags($category->description) != '') {

            $description = str_replace( '<', ' <', $category->description);
            $description = strip_tags($description);
            $description = preg_replace("/\s\s+/", " ", $description);
            $description = htmlspecialchars(trim($description), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            $document->setMetadata('description', $description);
        } else if ($metadesc != '') {
            $document->setDescription($metadesc);
        } else if ($params->get('menu-meta_description', '')) {
            $document->setDescription($params->get('menu-meta_description', ''));
        }

        if (isset($item->metakey) && $item->metakey != '') {
            $document->setMetadata('keywords', $item->metakey);
        } else if (isset($category->metakey) && $category->metakey != '') {
            $document->setMetadata('keywords', $category->metakey);
        } else if ($metakey != '') {
            $document->setMetadata('keywords', $metakey);
        } else if ($params->get('menu-meta_keywords', '')) {
            $document->setMetadata('keywords', $params->get('menu-meta_keywords', ''));
        }

        if ($app->get('MetaTitle') == '1' && $params->get('menupage_title', '')) {
            $document->setMetaData('title', $params->get('page_title', ''));
        }


        if (isset($category->metadata)) {
            $registry = new Registry;
            $registry->loadString($category->metadata);
            $category->metadata = $registry->toArray();
        }

        if (isset($item->metadata)) {
            $registry = new Registry;
            $registry->loadString($item->metadata);
            $category->metadata = $registry->toArray();
        }

        if ($type == 'category' && isset($category->metadata['robots']) && $category->metadata['robots'] != '') {
            $document->setMetadata('robots', $category->metadata['robots']);
        }
        if ($type == 'item' && isset($item->metadata['robots']) && $item->metadata['robots'] != '') {
            $document->setMetadata('robots', $item->metadata['robots']);
        }

        if ($params->get('robots')) {
            $document->setMetadata('robots', $params->get('robots'));
        }

        // Breadcrumbs TO DO (Add the whole tree)
        if ($type == 'category') {

            $path = PhocacartCategory::getPath(array(), (int)$category->id, (int)$category->parent_id, $category->title, $category->alias);
            //$curpath = $pathway->getPathwayNames();
            $pathWayIdA = $pathway->getPathway();
            $pIdA = array();
            if (!empty($pathWayIdA)) {
                foreach ($pathWayIdA as $k => $v) {
                    if (isset($v->link)) {
                        $parts = parse_url($v->link);
                        if (isset($parts['query'])) {
                            parse_str($parts['query'], $query);
                            if (isset($query['id'])) {
                                $pIdA[] = (int)$query['id'];
                            }
                        }
                    }
                }
            }

            if (!empty($path)) {
                $path = array_reverse($path);

                foreach ($path as $k => $v) {


                    if (!in_array((int)$v['id'], $pIdA)) {
                        // Don't duplicate breadcrumbs
                        $pathway->addItem($v['title'], Route::_(PhocacartRoute::getCategoryRoute($v['id'], $v['alias'])));
                        //$pathway->addItem($v['title'], PhocacartRoute::getCategoryRoute($v['id'], $v['alias']));
                    }
                }
            }


            /*if (isset($category->parentid)) {
                if ($category->parentid == 0) {
                    // $pathway->addItem( Text::_('COM_PHOCACART_CATEGORIES'), Route::_(PhocacartRoute::getCategoriesRoute()));
                } else if ($category->parentid > 0) {
                    $curpath = $pathway->getPathwayNames();

                    $countCurPath = count($curpath)-1;

                    if(!isset($curpath[$countCurPath]) || ($category->parenttitle != $curpath[$countCurPath])){
                        $pathway->addItem($category->parenttitle, Route::_(PhocacartRoute::getCategoryRoute($category->parentid, $category->parentalias)));
                    }
                }
            }*/

            /*if (isset($category->title) && !empty($category->title)) {
                $curpath = $pathway->getPathwayNames();
                if( (!empty($curpath) && $category->title != $curpath[count($curpath)-1]) || empty($curpath)){
                    $pathway->addItem($category->title);
                }
            }*/
        } else if ($type == 'item' || $type == 'question') {

            $path = PhocacartCategory::getPath(array(), (int)$category->id, (int)$category->parent_id, $category->title, $category->alias);


            $pathWayIdA = $pathway->getPathway();
            $pIdA = array();

            if (!empty($pathWayIdA)) {
                foreach ($pathWayIdA as $k => $v) {
                    if (isset($v->link)) {
                        $parts = parse_url($v->link);
                        if (isset($parts['query'])) {
                            parse_str($parts['query'], $query);
                            if (isset($query['id'])) {
                                $pIdA[] = (int)$query['id'];
                            }
                        }
                    }
                }
            }

            if (!empty($path)) {
                $path = array_reverse($path);
                foreach ($path as $k => $v) {
                    if (!in_array((int)$v['id'], $pIdA)) {
                        // Don't duplicate breadcrumbs
                        $pathway->addItem($v['title'], Route::_(PhocacartRoute::getCategoryRoute($v['id'], $v['alias'])));
                        //$pathway->addItem($v['title'], PhocacartRoute::getCategoryRoute($v['id'], $v['alias']));
                    }
                }
            }

            /*$curpath = $pathway->getPathwayNames();

            if (!empty($path)) {
                $path = array_reverse($path);
                foreach ($path as $k => $v) {
                    $pathway->addItem($v['title'], Route::_(PhocacartRoute::getCategoryRoute($v['id'], $v['alias'])));

                }
            }*/

            /*	if (isset($category->id) && isset($category->title) && isset($category->alias)) {
                    if ($category->id > 0) {
                        $pathway->addItem($category->title, Route::_(PhocacartRoute::getCategoryRoute($category->id, $category->alias)));
                    }
                }*/

            if (isset($item->title) && !empty($item->title)) {
                $pathway->addItem($item->title);
            }
        } else {

            if ($viewCurrent == $viewLink) {
                // Don't add anything to pathway as we display the title from menu link
                // for example - comparision view has an menu link, use menu link title
            } else {
                // we are e.g. in comparison view but we use a menu link from categories
                // so we need to add comparison header if exists
                if ($header != '') {
                    $pathway->addItem($header);
                }
            }
        }
    }


    public static function renderHeader($headers = array(), $tag = '', $imageMeta = '')
    {

        $app = Factory::getApplication();
        //$menus		= $app->getMenu();
        //$menu 		= $menus->getActive();
        $p = $app->getParams();
        $showPageHeading    = $p->get('show_page_heading');
        $pageHeading        = $p->get('page_heading');
        $displayHeader      = $p->get('display_header_type', 'h1');
        $hideHeader         = $p->get('hide_header_view', array());



        if ($displayHeader == '-1') {
            return '';
        }

        $view = $app->input->get('view', '', 'string');

        if (!empty($hideHeader) && $view != '') {
            if (in_array($view, $hideHeader)) {
                return '';
            }

        }


        if ($tag == '') {
            $tag = $displayHeader;
        }

        $h = array();
        if ($showPageHeading && $pageHeading != '') {
            $h[] = htmlspecialchars($pageHeading);
        }

        if (!empty($headers)) {
            foreach ($headers as $k => $v) {
                if ($v != '') {
                    $h[] = htmlspecialchars($v);
                    break; // in array there are stored OR items (if empty try next, if not empty use this and do not try next)
                    // PAGE HEADING AND NEXT ITEM OR NEXT NEXT ITEM
                }
            }
        }

        $imgMetaAttr = '';
        if ($imageMeta != '') {
            $imgMetaAttr = 'data-image-meta="'.$imageMeta.'"';
        }

        if (!empty($h)) {
            return '<' . strip_tags($tag) . ' class="ph-header" '.$imgMetaAttr.'>' . implode(" - ", $h) . '</' . strip_tags($tag) . '>';
        } else if ($imgMetaAttr != '') {
            return '<div style="display:none;" '.$imgMetaAttr.'></div>';// use hidden tag for open graph info
        }
        return false;
    }

    /*
     * if the message type is "order" then display this error message only in case there is no other message
     */

    public static function renderMessageQueue($msg = '', $type = '')
    {
        $app = Factory::getApplication();
        $m = $app->getMessageQueue();
        $mO = '';


        if (!empty($m)) {
            $mO .= '<ul id="system-messages">';
            if ($msg != '' && $type != 'order') {
                $mO .= '<li class=" ">' . $msg . '</li>';
            }

            foreach ($m as $k => $v) {
                // ' alert alert-'.$v['type']
                $mO .= '<li class="' . $v['type'] . '">' . $v['message'] . '</li>';
            }
            $mO .= '</ul>';
        } else {
            $mO .= '<ul id="system-messages">';
            $mO .= '<li class="ph-msg-error">' . $msg . '</li>';
            $mO .= '</ul>';
        }

        return $mO;
    }

    public static function displayLink($title = '', $url = '', $target = "_blank")
    {

        $o = '';
        if ($url != '' && PhocacartUtils::isURLAddress($url) && $title != '') {

            $targetO = $target != '' ? 'target="' . $target . '"' : '';
            $o = '<a href="' . $url . '" ' . $targetO . '>' . $title . '</a>';

        } else if ($title != '') {
            $o = $title;
        }
        return $o;
    }

    public static function displayVideo($url, $view = 0, $ywidth = 0, $yheight = 0)
    {

        $o = '';

        $app = Factory::getApplication();
        $paramsC = PhocacartUtils::getComponentParameters();
        $width = $paramsC->get('video_width', 0);
        $height = $paramsC->get('video_height', 0);

        if ($url != '' && PhocacartUtils::isURLAddress($url)) {


            $ssl = strpos($url, 'https');
            $yLink = 'http://www.youtube.com/v/';
            if ($ssl != false) {
                $yLink = 'https://www.youtube.com/v/';
            }

            $shortUrl = 'http://youtu.be/';
            $shortUrl2 = 'https://youtu.be/';
            $pos = strpos($url, $shortUrl);
            $pos2 = strpos($url, $shortUrl2);
            if ($pos !== false) {
                $code = str_replace($shortUrl, '', $url);
            } else if ($pos2 !== false) {
                $code = str_replace($shortUrl2, '', $url);
            } else {
                $codeArray = explode('=', $url);
                $code = str_replace($codeArray[0] . '=', '', $url);
            }


            if ((int)$ywidth > 0) {
                $width = (int)$ywidth;
            }
            if ((int)$yheight > 0) {
                $height = (int)$yheight;
            }

            $attr = '';
            if ((int)$width > 0) {
                $attr .= ' width="' . (int)$width . '"';
            }
            if ((int)$height > 0) {
                $attr .= ' height="' . (int)$height . '"';
            }

            $o .= '<div class="ph-video-container">';
            $o .= '<iframe ' . $attr . ' src="https://www.youtube.com/embed/' . $code . '"></iframe>';
            $o .= '</div>';
            /*$o .= '<object height="'.(int)$height.'" width="'.(int)$width.'" data="http://www.youtube.com/v/'.$code.'" type="application/x-shockwave-flash">'
            .'<param name="movie" value="http://www.youtube.com/v/'.$code.'" />'
            .'<param name="allowFullScreen" value="true" />'
            .'<param name="allowscriptaccess" value="always" />'
            .'<embed src="'.$yLink.$code.'" type="application/x-shockwave-flash" allowscriptaccess="always" allowfullscreen="true" height="'.(int)$height.'" width="'.(int)$width.'" /></object>';*/
        }
        return $o;
    }

    public static function getLayoutType($layoutType)
    {
        if ($layoutType == 'list') {
            return 'list';
        } else if ($layoutType == 'gridlist') {
            return 'gridlist';
        } else {
            // Else is important, don't show any other types
            return 'grid';
        }
    }

    public static function setActiveLayoutType($layoutType)
    {

        $activeLT[0] = $activeLT[1] = $activeLT[2] = '';
        switch ($layoutType) {
            case 'grid':
                $activeLT[0] = 'active';
            break;
            case 'gridlist':
                $activeLT[1] = 'active';
            break;
            case 'list':
                $activeLT[2] = 'active';
            break;

        }

        return $activeLT;
    }

    public static function getColumnClass($column)
    {
        return 12 / $column;//1,2,3,4,6,12
    }

    public static function getLabel($date, $sales, $featured)
    {

        $label = array();
        $new = $hot = $feat = '';
        $c = 1;

        $label['new'] = self::renderNewIcon($date, $c);
        if ($label['new'] != '') {
            $c++;
        }
        $label['hot'] = self::renderHotIcon($sales, $c);
        if ($label['hot'] != '') {
            $c++;
        }
        $label['feat'] = self::renderFeaturedIcon($featured, $c);

        $label['cssthumbnail'] = '';
        $label['cssthumbnail2'] = 'img-thumbnail';
        if ($c > 1) {
            $label['cssthumbnail'] = 'b-thumbnail';
            $label['cssthumbnail2'] = '';
        }

        return $label;
    }

    public static function getLinkedTitle($link, $item, $route = 'item')
    {

        if ($link == 1) {
            if ($route == 'item') {
                return '<a href="' . Route::_(PhocacartRoute::getItemRoute($item->id, $item->catid, $item->alias, $item->catalias)) . '">' . $item->title . '</a>';
            } else if ($route == 'category') {
                return '<a href="' . Route::_(PhocacartRoute::getCategoryRoute($item->id, $item->alias)) . '">' . $item->title . '</a>';
            }
        } else {
            return $item->title;
        }
        return '';
    }

    public static function renderProductHeader($link, $v, $route = 'item', $tag = '', $additionalClass = '')
    {

        $app = Factory::getApplication();
        $p = $app->getParams();

        $displayHeader = $p->get('display_product_header', 'h3');


        if ($displayHeader == '-1') {
            return '';
        }

        if ($tag == '') {
            $tag = $displayHeader;
        }


        $header = PhocacartRenderFront::getLinkedTitle($link, $v, $route);

        return '<' . strip_tags($tag) . ' class="ph-product-header ' . strip_tags($additionalClass) . '">' . $header . '</' . strip_tags($tag) . '>';
    }



    public static function renderCategoryHeader($link, $v, $route = 'category', $tag = '', $additionalClass = '')
    {

        $app = Factory::getApplication();
        $p = $app->getParams();

        $displayHeader = $p->get('display_category_header', 'h3');


        if ($displayHeader == '-1') {
            return '';
        }

        if ($tag == '') {
            $tag = $displayHeader;
        }


        $header = PhocacartRenderFront::getLinkedTitle($link, $v, $route);

        return '<' . strip_tags($tag) . ' class="ph-category-header ' . strip_tags($additionalClass) . '">' . $header . '</' . strip_tags($tag) . '>';
    }

    public static function renderArticle($id, $format = 'html', $default = '', $changeLang = 0)
    {
        $o = '';
        if ((int)$id > 0) {
            $db = Factory::getDBO();

            $query = $db->getQuery(true);

            //$query = 'SELECT a.introtext, a.fulltext FROM #__content AS a WHERE id = ' . (int)$id;

            $query = $db->getQuery(true)
                    ->select('a.introtext, a.fulltext')
                    ->from($db->quoteName('#__content', 'a'))
                    ->where(
                        [
                            $db->quoteName('a.id') . ' = ' . (int)$id
                        ]
                    );

            $db->setQuery((string)$query);

            $a = $db->loadObject();


            // Associated article by language - lang, assoc
            if (Associations::isEnabled())
            {
                if ($id != null)
                {
                    $associations = Associations::getAssociations('com_content', '#__content', 'com_content.item', $id);


                    $lang = Factory::getLanguage();
                    $language = $lang->getTag();
                    if (isset($language) && $language != '*' && $language != '') {
                        foreach ($associations as $tag => $association) {
                           if ($language == $tag && isset($association->id)) {

                                $query = $db->getQuery(true)
                                ->select('a.introtext, a.fulltext')
                                ->from($db->quoteName('#__content', 'a'))
                                ->where(
                                    [
                                        $db->quoteName('a.id') . ' = ' . (int)$association->id
                                    ]
                                );

                                $db->setQuery((string)$query);
                                $a = $db->loadObject();
                            }

                        }
                    }
                }
            }

            $o = $a->introtext . $a->fulltext;

            // Disable emailclock for PDF | MAIL
            if ($format == 'pdf' || $format == 'mail') {
                $o = '{emailcloak=off}' . $o;
            }

            $o = HTMLHelper::_('content.prepare', $o);


            if ($changeLang == 1) {
                Dispatcher::dispatchChangeText($o);
            }

            if ($format == 'pdf' || $format == 'text') {
                // Remove Javascript for PDF
                $o = preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', "", $o);
                // Remove mailto - problematic for TCPDF
                $o = preg_replace("~<a\s+href=[\'|\"]mailto:(.*?)[\'|\"].*?>.*?</a>~", "$1", $o);

            }

            if ($format == 'text') {
                $o = PhocacartText::filterValue($o, 'text');
            }

        }



        // If no text is set by article and there is default value
        if ($o == '' && $default != '') {
            return $default;
        }
        return $o;
    }

    public static function getConfirmOrderText($orderValue = 0)
    {

        $cFT = Text::_('COM_PHOCACART_CONFIRM_ORDER');

        $app = Factory::getApplication();
        $p = $app->getParams();
        $confirm_order_text = $p->get('cofirm_order_text', '');
        $confirm_order_text_zero = $p->get('cofirm_order_text_zero', '');

        if ($confirm_order_text != '' && $orderValue > 0) {
            $cFT = Text::_($confirm_order_text);
        }
        if ($confirm_order_text_zero != '' && $orderValue == 0) {
            $cFT = Text::_($confirm_order_text_zero);
        }

        return $cFT;
    }


    public static function completeClass($items = array())
    {

        if (!empty($items)) {
            return implode(' ', $items);
        }
        return '';

    }

}
