<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  Content.digidonate
 *
 * @copyright   (C) 2006 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt

 * @phpcs:disable PSR1.Classes.ClassDeclaration.MissingNamespace
 */

use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ModuleHelper;
use Joomla\CMS\Plugin\CMSPlugin;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Plugin to enable loading modules into content (e.g. articles)
 * This uses the {digidonate} syntax
 *
 */
class PlgContentDigiDonate extends CMSPlugin
{
    protected static $modules = [];

    protected static $mods = [];

    /**
     * Plugin that loads module positions within content
     *
     * @param   string   $context   The context of the content being passed to the plugin.
     * @param   object   &$article  The article object.  Note $article->text is also available
     * @param   mixed    &$params   The article params
     * @param   integer  $page      The 'page' number
     *
     * @return  void
     *
     * @since   1.6
     */
    public function onContentPrepare($context, &$article, &$params, $page = 0)
    {
        // Don't run this plugin when the content is being indexed
        if ($context === 'com_finder.indexer') {
            return;
        }

        // Only execute if $article is an object and has a text property
        if (!is_object($article) || !property_exists($article, 'text') || is_null($article->text)) {
            return;
        }

        $defaultStyle = $this->params->get('style', 'none');

        // Fallback xhtml (used in Joomla 3) to html5
        if ($defaultStyle === 'xhtml') {
            $defaultStyle = 'html5';
        }


        // Expression to search for(modules)
        $regexmod = '/{digidonate\s(.*?)}/i';


        if (str_contains($article->text, '{digidonate ')) {
            // Find all instances of plugin and put in $matchesmod for loadmodule
            preg_match_all($regexmod, $article->text, $matchesmod, PREG_SET_ORDER);

            // If no matches, skip this
            if ($matchesmod) {
                foreach ($matchesmod as $matchmod) {
                    $button_id = $matchmod[1];
                    // First parameter is the module, will be prefixed with mod_ later
                    $module = "mod_digiwallet_donation";
                    // Second parameter is the title
                    $title = 'Digiwallet Donation';

                    $output = $this->_loadmod($module, $title, $button_id);

                    // We should replace only first occurrence in order to allow positions with the same name to regenerate their content:
                    if (($start = strpos($article->text, $matchmod[0])) !== false) {
                        $article->text = substr_replace($article->text, $output, $start, strlen($matchmod[0]));
                    }
                }
            }
        }
    }

    /**
     * This is always going to get the first instance of the module type unless
     * there is a title.
     *
     * @param   string  $module  The module title
     * @param   string  $title   The title of the module
     * @param   string  $style   The style of the module
     *
     * @return  mixed
     *
     * @since   1.6
     */
    protected function _loadmod($module, $title, $style = 'none')
    {
        $document = Factory::getDocument();
        $renderer = $document->loadRenderer('module');
        $mod      = ModuleHelper::getModule($module, $title);

        // If the module without the mod_ isn't found, try it with mod_.
        // This allows people to enter it either way in the content
        if (!isset($mod)) {
            $name = 'mod_' . $module;
            $mod  = ModuleHelper::getModule($name, $title);
        }

        $params = ['style' => $style];
        ob_start();

        if ($mod->id) {
            $mod->button_id = $style;
            echo $renderer->render($mod, $params);
        }

        return ob_get_clean();
    }
}
