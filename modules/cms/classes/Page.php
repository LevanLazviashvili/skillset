<?php namespace Cms\Classes;

use Lang;
use ApplicationException;
use October\Rain\Filesystem\Definitions as FileDefinitions;

/**
 * The CMS page class.
 *
 * @package october\cms
 * @author Alexey Bobkov, Samuel Georges
 */
class Page extends CmsCompoundObject
{
    /**
     * @var string The container name associated with the model, eg: pages.
     */
    protected $dirName = 'pages';

    /**
     * @var array The attributes that are mass assignable.
     */
    protected $fillable = [
        'url',
        'layout',
        'title',
        'description',
        'is_hidden',
        'meta_title',
        'meta_description',
        'markup',
        'settings',
        'code'
    ];

    /**
     * @var array The API bag allows the API handler code to bind arbitrary
     * data to the page object.
     */
    public $apiBag = [];

    /**
     * @var array The rules to be applied to the data.
     */
    public $rules = [
        'title' => 'required',
        'url'   => ['required', 'regex:/^\/[a-z0-9\/\:_\-\*\[\]\+\?\|\.\^\\\$]*$/i']
    ];

    /**
     * Creates an instance of the object and associates it with a CMS theme.
     * @param array $attributes
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->customMessages = [
            'url.regex' => 'cms::lang.page.invalid_url',
        ];
    }

    /**
     * Returns name of a PHP class to us a parent for the PHP class created for the object's PHP section.
     * @return mixed Returns the class name or null.
     */
    public function getCodeClassParent()
    {
        return PageCode::class;
    }

    /**
     * Returns a list of layouts available in the theme.
     * This method is used by the form widget.
     * @return array Returns an array of strings.
     */
    public function getLayoutOptions()
    {
        if (!($theme = Theme::getEditTheme())) {
            throw new ApplicationException(Lang::get('cms::lang.theme.edit.not_found'));
        }

        $layouts = Layout::listInTheme($theme, true);
        $result = [];
        $result[null] = Lang::get('cms::lang.page.no_layout');

        foreach ($layouts as $layout) {
            $baseName = $layout->getBaseFileName();

            if (FileDefinitions::isPathIgnored($baseName)) {
                continue;
            }

            $result[$baseName] = strlen($layout->name) ? $layout->name : $baseName;
        }

        return $result;
    }

    /**
     * Helper that returns a nicer list of pages for use in dropdowns.
     * @return array
     */
    public static function getNameList()
    {
        $result = [];
        $pages = self::sortBy('baseFileName')->all();
        foreach ($pages as $page) {
            $result[$page->baseFileName] = $page->title . ' (' . $page->baseFileName . ')';
        }

        return $result;
    }

    /**
     * Helper that makes a URL for a page in the active theme.
     * @param mixed $page Specifies the Cms Page file name.
     * @param array $params Route parameters to consider in the URL.
     * @return string
     */
    public static function url($page, array $params = [])
    {
        /*
         * Reuse existing controller or create a new one,
         * assuming that the method is called not during the front-end
         * request processing.
         */
        $controller = Controller::getController() ?: new Controller;

        return $controller->pageUrl($page, $params, true);
    }

    /**
     * Handler for the pages.menuitem.getTypeInfo event.
     * Returns a menu item type information. The type information is returned as array
     * with the following elements:
     * - references - a list of the item type reference options. The options are returned in the
     *   ["key"] => "title" format for options that don't have sub-options, and in the format
     *   ["key"] => ["title"=>"Option title", "items"=>[...]] for options that have sub-options. Optional,
     *   required only if the menu item type requires references.
     * - nesting - Boolean value indicating whether the item type supports nested items. Optional,
     *   false if omitted.
     * - dynamicItems - Boolean value indicating whether the item type could generate new menu items.
     *   Optional, false if omitted.
     * - cmsPages - a list of CMS pages (objects of the Cms\Classes\Page class), if the item type requires
     *   a CMS page reference to resolve the item URL.
     * @param string $type Specifies the menu item type
     * @return array Returns an array
     */
    public static function getMenuTypeInfo(string $type)
    {
        $result = [];

        if ($type === 'cms-page') {
            $theme = Theme::getActiveTheme();
            $pages = self::listInTheme($theme, true);
            $references = [];

            foreach ($pages as $page) {
                $references[$page->getBaseFileName()] = $page->title . ' [' . $page->getBaseFileName() . ']';
            }

            $result = [
                'references'   => $references,
                'nesting'      => false,
                'dynamicItems' => false
            ];
        }

        return $result;
    }

    /**
     * Handler for the pages.menuitem.resolveItem event.
     * Returns information about a menu item. The result is an array
     * with the following keys:
     * - url - the menu item URL. Not required for menu item types that return all available records.
     *   The URL should be returned relative to the website root and include the subdirectory, if any.
     *   Use the Url::to() helper to generate the URLs.
     * - isActive - determines whether the menu item is active. Not required for menu item types that
     *   return all available records.
     * - items - an array of arrays with the same keys (url, isActive, items) + the title key.
     *   The items array should be added only if the $item's $nesting property value is TRUE.
     * @param \RainLab\Pages\Classes\MenuItem $item Specifies the menu item.
     * @param string $url Specifies the current page URL, normalized, in lower case
     * @param \Cms\Classes\Theme $theme Specifies the current theme.
     * The URL is specified relative to the website root, it includes the subdirectory name, if any.
     * @return mixed Returns an array. Returns null if the item cannot be resolved.
     */
    public static function resolveMenuItem($item, string $url, Theme $theme)
    {
        $result = null;

        if ($item->type === 'cms-page') {
            if (!$item->reference) {
                return;
            }

            $page = self::loadCached($theme, $item->reference);
            $controller = Controller::getController() ?: new Controller;
            $pageUrl = $controller->pageUrl($item->reference, [], false);

            $result = [];
            $result['url'] = $pageUrl;
            $result['isActive'] = $pageUrl == $url;
            $result['mtime'] = $page ? $page->mtime : null;
        }

        return $result;
    }

    /**
     * Handler for the backend.richeditor.getTypeInfo event.
     * Returns a menu item type information. The type information is returned as array
     * @param string $type Specifies the page link type
     * @return array
     */
    public static function getRichEditorTypeInfo(string $type)
    {
        $result = [];

        if ($type === 'cms-page') {
            $theme = Theme::getActiveTheme();
            $pages = self::listInTheme($theme, true);

            foreach ($pages as $page) {
                $url = self::url($page->getBaseFileName());
                $result[$url] = $page->title;
            }
        }

        return $result;
    }
}
