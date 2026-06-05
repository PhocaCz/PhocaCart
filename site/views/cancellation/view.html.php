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

use Joomla\CMS\MVC\View\HtmlView;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;

jimport('joomla.application.component.view');

/**
 * HTML View for the EU Right of Withdrawal (cancellation) feature.
 *
 * @since  6.1.0
 */
class PhocaCartViewCancellation extends HtmlView
{
    /** @var array  Miscellaneous template data */
    protected array $t = [];

    /** @var array  Style arrays from PhocacartRenderStyle */
    protected array $s = [];

    /** @var object  Joomla params for the current menu item */
    protected $p;

    /** @var object  Current Joomla user */
    protected $u;

    /**
     * Display the view.
     *
     * @param   string|null  $tpl  Template override.
     *
     * @return  void
     *
     * @since  6.1.0
     */
    public function display($tpl = null): void
    {
        $app         = Factory::getApplication();
        $this->u     = PhocacartUser::getUser();
        $this->s     = PhocacartRenderStyle::getStyles();
        $this->p     = $app->getParams();

        /** @var PhocaCartModelCancellation $model */
        $model = $this->getModel();
        $data  = $model->getData();

        if ($data === false) {
            // Feature disabled or order not found — surface via message, show nothing
            $app->enqueueMessage(Text::_('COM_PHOCACART_CANCELLATION_ERROR_NOT_FOUND'), 'warning');
            $app->redirect(PhocacartRoute::getOrdersRoute());
            return;
        }

        $this->t['order']       = $data['order'];
        $this->t['eligibility'] = $data['eligibility'];
        $this->t['token']       = $data['token'];
        $paramsC = PhocacartUtils::getComponentParameters();
        $this->t['cancellation_description_article'] = $paramsC->get('cancellation_description_article', 0);

        // Load Phoca Cart front-end assets
        $media = PhocacartRenderMedia::getInstance('main');
        $media->loadBase();
        $media->loadSpec();

        $this->_prepareDocument();
        parent::display($tpl);
    }

    /**
     * Prepares the document metadata (title, breadcrumbs).
     *
     * @return  void
     *
     * @since  6.1.0
     */
    protected function _prepareDocument(): void
    {
        $header = Text::_('COM_PHOCACART_CANCELLATION_VIEW_TITLE');
        $articleId = (int)($this->t['cancellation_description_article'] ?? 0);
        if ($articleId > 0) {
            $header = PhocacartRenderFront::renderArticleTitle($articleId, $header);
        }

        PhocacartRenderFront::prepareDocument(
            $this->document,
            $this->p,
            false,
            false,
            $header
        );
    }
}
