<?php namespace Cms\Classes;

/**
 * The CMS partial class.
 *
 * @package october\cms
 * @author Alexey Bobkov, Samuel Georges
 */
class Partial extends CmsCompoundObject
{
    /**
     * @var string The container name associated with the model, eg: pages.
     */
    protected $dirName = 'partials';

    /**
     * @var array Allowable file extensions. TEMPORARY! DO NOT INCLUDE in Build 1.1.x, workaround for unsupported code
     */
    protected $allowedExtensions = ['htm', 'html', 'css', 'js', 'svg'];

    /**
     * Returns name of a PHP class to us a parent for the PHP class created for the object's PHP section.
     * @return string Returns the class name.
     */
    public function getCodeClassParent()
    {
        return PartialCode::class;
    }
}
