<?php namespace Backend\Models;

use File;
use Cache;
use Model;
use Less_Parser;
use Exception;

/**
 * Editor settings that affect all users
 *
 * @package october\backend
 * @author Alexey Bobkov, Samuel Georges
 */
class EditorSetting extends Model
{
    use \System\Traits\ViewMaker;
    use \October\Rain\Database\Traits\Validation;

    /**
     * @var array Behaviors implemented by this model.
     */
    public $implement = [
        \System\Behaviors\SettingsModel::class
    ];

    /**
     * @var string Unique code
     */
    public $settingsCode = 'backend_editor_settings';

    /**
     * @var mixed Settings form field defitions
     */
    public $settingsFields = 'fields.yaml';

    /**
     * @var string The key to store rendered CSS in the cache under
     */
    public $cacheKey = 'backend::editor.custom_css';

    protected $defaultHtmlAllowEmptyTags = 'textarea, a, iframe, object, video, style, script, .fa, .fr-emoticon, .fr-inner, path, line, hr, i';

    protected $defaultHtmlAllowTags = 'a, abbr, address, area, article, aside, audio, b, bdi, bdo, blockquote, br, button, canvas, caption, cite, code, col, colgroup, datalist, dd, del, details, dfn, dialog, div, dl, dt, em, embed, fieldset, figcaption, figure, footer, form, h1, h2, h3, h4, h5, h6, header, hgroup, hr, i, iframe, img, input, ins, kbd, keygen, label, legend, li, link, main, map, mark, menu, menuitem, meter, nav, noscript, object, ol, optgroup, option, output, p, param, pre, progress, queue, rp, rt, ruby, s, samp, script, style, section, select, small, source, span, strike, strong, sub, summary, sup, table, tbody, td, textarea, tfoot, th, thead, time, title, tr, track, u, ul, var, video, wbr';

    protected $defaultHtmlNoWrapTags = 'figure, script, style';

    protected $defaultHtmlRemoveTags = 'script, style, base';

    protected $defaultHtmlLineBreakerTags = 'figure, table, hr, iframe, form, dl';

    protected $defaultHtmlStyleImage = [
        'oc-img-rounded' => 'Rounded',
        'oc-img-bordered' => 'Bordered',
    ];

    protected $defaultHtmlStyleLink = [
        'oc-link-green' => 'Green',
        'oc-link-strong' => 'Strong',
    ];

    protected $defaultHtmlStyleParagraph = [
        'oc-text-bordered' => 'Bordered',
        'oc-text-gray' => 'Gray',
        'oc-text-spaced' => 'Spaced',
        'oc-text-uppercase' => 'Uppercase',
    ];

    protected $defaultHtmlStyleTable = [
        'oc-dashed-borders' => 'Dashed Borders',
        'oc-alternate-rows' => 'Alternate Rows',
    ];

    protected $defaultHtmlStyleTableCell = [
        'oc-cell-highlighted' => 'Highlighted',
        'oc-cell-thick-border' => 'Thick Border',
    ];

    protected $defaultHtmlParagraphFormats = [
        'N' => 'Normal',
        'H1' => 'Heading 1',
        'H2' => 'Heading 2',
        'H3' => 'Heading 3',
        'H4' => 'Heading 4',
        'PRE' => 'Code',
    ];

    /**
     * Editor toolbar presets for Froala.
     */
    protected $editorToolbarPresets = [
        'default' => 'paragraphFormat, paragraphStyle, quote, bold, italic, align, formatOL, formatUL, insertTable,
                      insertLink, insertImage, insertVideo, insertAudio, insertFile, insertHR, html',
        'minimal' => 'paragraphFormat, bold, italic, underline, |, insertLink, insertImage, |, html',
        'full'    => 'undo, redo, |, bold, italic, underline, |, paragraphFormat, paragraphStyle, inlineStyle, |,
                      strikeThrough, subscript, superscript, clearFormatting, |, fontFamily, fontSize, |, color,
                      emoticons, -, selectAll, |, align, formatOL, formatUL, outdent, indent, quote, |, insertHR,
                      insertLink, insertImage, insertVideo, insertAudio, insertFile, insertTable, |, selectAll,
                      html, fullscreen',
    ];

    /**
     * Validation rules
     */
    public $rules = [];

    /**
     * Initialize the seed data for this model. This only executes when the
     * model is first created or reset to default.
     * @return void
     */
    public function initSettingsData()
    {
        $this->html_allow_empty_tags = $this->defaultHtmlAllowEmptyTags;
        $this->html_allow_tags = $this->defaultHtmlAllowTags;
        $this->html_no_wrap_tags = $this->defaultHtmlNoWrapTags;
        $this->html_remove_tags = $this->defaultHtmlRemoveTags;
        $this->html_line_breaker_tags = $this->defaultHtmlLineBreakerTags;
        $this->html_custom_styles = File::get(base_path().'/modules/backend/models/editorsetting/default_styles.less');
        $this->html_style_image = $this->makeStylesForTable($this->defaultHtmlStyleImage);
        $this->html_style_link = $this->makeStylesForTable($this->defaultHtmlStyleLink);
        $this->html_style_paragraph = $this->makeStylesForTable($this->defaultHtmlStyleParagraph);
        $this->html_style_table = $this->makeStylesForTable($this->defaultHtmlStyleTable);
        $this->html_style_table_cell = $this->makeStylesForTable($this->defaultHtmlStyleTableCell);
        $this->html_paragraph_formats = $this->makeFormatsForTable($this->defaultHtmlParagraphFormats);
    }

    public function afterFetch()
    {
        if (!isset($this->value['html_paragraph_formats'])) {
            $this->html_paragraph_formats = $this->makeFormatsForTable($this->defaultHtmlParagraphFormats);
            $this->save();
        }
    }

    public function afterSave()
    {
        Cache::forget(self::instance()->cacheKey);
    }

    protected function makeStylesForTable($arr)
    {
        $count = 0;

        return array_build($arr, function ($key, $value) use (&$count) {
            return [$count++, ['class_label' => $value, 'class_name' => $key]];
        });
    }

    protected function makeFormatsForTable($arr)
    {
        $count = 0;

        return array_build($arr, function ($key, $value) use (&$count) {
            return [$count++, ['format_label' => $value, 'format_tag' => $key]];
        });
    }

    /**
     * Same as getConfigured but uses a special structure for styles.
     * @return mixed
     */
    public static function getConfiguredStyles($key, $default = null)
    {
        return static::getConfiguredArray($key, $default, function ($key, $value) {
            if (array_has($value, ['class_name', 'class_label'])) {
                return [
                    array_get($value, 'class_name'),
                    array_get($value, 'class_label')
                ];
            }
        });
    }

    /**
     * Same as getConfigured but uses a special structure for paragraph formats.
     * @return mixed
     */
    public static function getConfiguredFormats($key, $default = null)
    {
        return static::getConfiguredArray($key, $default, function ($key, $value) {
            if (array_has($value, ['format_tag', 'format_label'])) {
                return [
                    array_get($value, 'format_tag'),
                    array_get($value, 'format_label')
                ];
            }
        });
    }

    protected static function getConfiguredArray($key, $default = null, $callback = null)
    {
        $instance = static::instance();

        $value = $instance->get($key);

        $defaultValue = $instance->getDefaultValue($key);

        if (is_array($value) && is_callable($callback)) {
            $value = array_filter(array_build($value, $callback));
        }

        return $value != $defaultValue ? $value : $default;
    }

    /**
     * Returns the value only if it differs from the default value.
     * @return mixed
     */
    public static function getConfigured($key, $default = null)
    {
        $instance = static::instance();

        $value = $instance->get($key);

        $defaultValue = $instance->getDefaultValue($key);

        return $value != $defaultValue ? $value : $default;
    }

    public function getDefaultValue($attribute)
    {
        $property = 'default'.studly_case($attribute);

        return $this->$property;
    }

    /**
     * Return the editor toolbar presets without line breaks.
     * @return array
     */
    public function getEditorToolbarPresets()
    {
        return array_map(function ($value) {
            return preg_replace('/\s+/', ' ', $value);
        }, $this->editorToolbarPresets);
    }

    public static function renderCss()
    {
        $cacheKey = self::instance()->cacheKey;
        if (Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        try {
            $customCss = self::compileCss();
            Cache::forever($cacheKey, $customCss);
        }
        catch (Exception $ex) {
            $customCss = '/* ' . $ex->getMessage() . ' */';
        }

        return $customCss;
    }

    public static function compileCss()
    {
        $parser = new Less_Parser(['compress' => true]);

        $customStyles = '.fr-view {';
        $customStyles .= self::get('html_custom_styles');
        $customStyles .= '}';

        $parser->parse($customStyles);

        return $parser->getCss();
    }
}
