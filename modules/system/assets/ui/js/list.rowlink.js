/*
 * Table row linking plugin
 *
 * Data attributes:
 * - data-control="rowlink" - enables the plugin on an element
 * - data-exclude-class="nolink" - disables the link for elements with this class
 * - data-linked-class="rowlink" - this class is added to affected table rows
 *
 * JavaScript API:
 * $('a#someElement').rowLink()
 *
 * Dependences:
 * - Null
 */

+function ($) { "use strict";

    // ROWLINK CLASS DEFINITION
    // ============================

    var RowLink = function(element, options) {
        var self       = this
        this.options   = options
        this.$el       = $(element)

        var tr = this.$el.prop('tagName') == 'TR'
            ? this.$el
            : this.$el.find('tr:has(td)')

        tr.each(function(){

            var link = $(this).find(options.target).filter(function(){
                return !$(this).closest('td').hasClass(options.excludeClass) && !$(this).hasClass(options.excludeClass)
            }).first()

            if (!link.length) return

            var href = link.attr('href'),
                onclick = (typeof link.get(0).onclick == "function") ? link.get(0).onclick : null,
                popup = link.is('[data-control=popup]'),
                request = link.is('[data-request]')

            function handleClick(e) {
                if ($(document.body).hasClass('drag')) {
                    return
                }

                if (onclick) {
                    onclick.apply(link.get(0))
                }
                else if (request) {
                    link.request()
                }
                else if (popup) {
                    link.popup()
                }
                else if (e.ctrlKey || e.metaKey) {
                    window.open(href)
                }
                else {
                    window.location = href
                }
            }

            $(this).not('.' + options.excludeClass).find('td').not('.' + options.excludeClass).click(function (e) {
                handleClick(e)
            }).mousedown(function (e) {
                if (e.which == 2) {
                    window.open(href)
                }
            })

            $(this).not('.' + options.excludeClass).on('keypress', function(e) {
                if (e.key === '(Space character)' || e.key === 'Spacebar' || e.key === ' ') {
                    handleClick(e)
                    return false
                }
            })

            $(this).addClass(options.linkedClass)
            link.hide().after(link.html())
        })

        // Add Keyboard Navigation to list rows
        $('tr.rowlink').attr('tabindex', 0)
    }

    RowLink.DEFAULTS = {
        target: 'a',
        excludeClass: 'nolink',
        linkedClass: 'rowlink'
    }

    // ROWLINK PLUGIN DEFINITION
    // ============================

    var old = $.fn.rowLink

    $.fn.rowLink = function (option) {
        var args = Array.prototype.slice.call(arguments, 1)
        return this.each(function () {
            var $this   = $(this)
            var data    = $this.data('oc.rowlink')
            var options = $.extend({}, RowLink.DEFAULTS, $this.data(), typeof option == 'object' && option)
            if (!data) $this.data('oc.rowlink', (data = new RowLink(this, options)))
            else if (typeof option == 'string') data[option].apply(data, args)
        })
    }

    $.fn.rowLink.Constructor = RowLink

    // ROWLINK NO CONFLICT
    // =================

    $.fn.rowLink.noConflict = function () {
        $.fn.rowLink = old
        return this
    }

    // ROWLINK DATA-API
    // ===============

    $(document).render(function() {
        $('[data-control="rowlink"]').rowLink()
    })

}(window.jQuery);
