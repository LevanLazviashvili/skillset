/*
 * The autcomplete plugin, a forked version of Bootstrap's original typeahead plugin.
 *
 * Data attributes:
 * - data-control="autocomplete" - enables the autocomplete plugin
 *
 * JavaScript API:
 * $('input').autocomplete()
 *
 * Forked by daftspunk:
 *
 * - Source can be an object [{ value: 'something', label: 'Something' }, { value: 'else', label: 'Something Else' }]
 * - Source can also be { something: 'Something', else: 'Else' }
 */

!function($){

    "use strict"; // jshint ;_;


    /* AUTOCOMPLETE PUBLIC CLASS DEFINITION
     * ================================= */

    var Autocomplete = function (element, options) {
        this.$element = $(element)
        this.options = $.extend({}, $.fn.autocomplete.defaults, options)
        this.matcher = this.options.matcher || this.matcher
        this.sorter = this.options.sorter || this.sorter
        this.highlighter = this.options.highlighter || this.highlighter
        this.updater = this.options.updater || this.updater
        this.source = this.options.source
        this.$menu = $(this.options.menu)
        this.shown = false
        this.listen()
    }

    Autocomplete.prototype = {

        constructor: Autocomplete,

        select: function () {
            var val = this.$menu.find('.active').attr('data-value')
            this.$element
                .val(this.updater(val))
                .change()
            return this.hide()
        },

        updater: function (item) {
            return item
        },

        show: function () {
            var offset = this.options.bodyContainer ? this.$element.offset() : this.$element.position(),
                pos = $.extend({}, offset, {
                height: this.$element[0].offsetHeight
            }),
            cssOptions = {
                top: pos.top + pos.height
                , left: pos.left
            }

            if (this.options.matchWidth) {
                cssOptions.width = this.$element[0].offsetWidth
            }

            this.$menu.css(cssOptions)

            if (this.options.bodyContainer) {
                $(document.body).append(this.$menu)
            }
            else {
                this.$menu.insertAfter(this.$element)
            }

            this.$menu.show()

            this.shown = true
            return this
        },

        hide: function () {
            this.$menu.hide()
            this.shown = false
            return this
        },

        lookup: function (event) {
            var items

            this.query = this.$element.val()

            if (!this.query || this.query.length < this.options.minLength) {
                return this.shown ? this.hide() : this
            }

            items = $.isFunction(this.source) ? this.source(this.query, $.proxy(this.process, this)) : this.source

            return items ? this.process(items) : this
        },

        itemValue: function (item) {
            if (typeof item === 'object')
                return item.value;

            return item;
        },

        itemLabel: function (item) {
            if (typeof item === 'object')
                return item.label;

            return item;
        },

        itemsToArray: function (items) {
            var newArray = []
            $.each(items, function(value, label){
                newArray.push({ label: label, value: value })
            })
            return newArray
        },

        process: function (items) {
            var that = this

            if (typeof items == 'object')
                items = this.itemsToArray(items)

            items = $.grep(items, function (item) {
                return that.matcher(item)
            })

            items = this.sorter(items)

            if (!items.length) {
                return this.shown ? this.hide() : this
            }

            return this.render(items.slice(0, this.options.items)).show()
        },

        matcher: function (item) {
            return ~this.itemValue(item).toLowerCase().indexOf(this.query.toLowerCase())
        },

        sorter: function (items) {
            var beginswith = [],
                caseSensitive = [],
                caseInsensitive = [],
                item,
                itemValue

            while (item = items.shift()) {
                itemValue = this.itemValue(item)
                if (!itemValue.toLowerCase().indexOf(this.query.toLowerCase())) beginswith.push(item)
                else if (~itemValue.indexOf(this.query)) caseSensitive.push(item)
                else caseInsensitive.push(item)
            }

            return beginswith.concat(caseSensitive, caseInsensitive)
        },

        highlighter: function (item) {
            var query = this.query.replace(/[\-\[\]{}()*+?.,\\\^$|#\s]/g, '\\$&')
            return item.replace(new RegExp('(' + query + ')', 'ig'), function ($1, match) {
                return '<strong>' + match + '</strong>'
            })
        },

        render: function (items) {
            var that = this

            items = $(items).map(function (i, item) {
                i = $(that.options.item).attr('data-value', that.itemValue(item))
                i.find('a').html(that.highlighter(that.itemLabel(item)))
                return i[0]
            })

            items.first().addClass('active')
            this.$menu.html(items)
            return this
        },

        next: function (event) {
            var active = this.$menu.find('.active').removeClass('active'),
                next = active.next()

            if (!next.length) {
                next = $(this.$menu.find('li')[0])
            }

            next.addClass('active')
        },

        prev: function (event) {
            var active = this.$menu.find('.active').removeClass('active'),
                prev = active.prev()

            if (!prev.length) {
                prev = this.$menu.find('li').last()
            }

            prev.addClass('active')
        },

        listen: function () {
            this.$element
                .on('focus.autocomplete',    $.proxy(this.focus, this))
                .on('blur.autocomplete',     $.proxy(this.blur, this))
                .on('keypress.autocomplete', $.proxy(this.keypress, this))
                .on('keyup.autocomplete',    $.proxy(this.keyup, this))

            if (this.eventSupported('keydown')) {
                this.$element.on('keydown.autocomplete', $.proxy(this.keydown, this))
            }

            this.$menu
                .on('click.autocomplete', $.proxy(this.click, this))
                .on('mouseenter.autocomplete', 'li', $.proxy(this.mouseenter, this))
                .on('mouseleave.autocomplete', 'li', $.proxy(this.mouseleave, this))
        },

        eventSupported: function(eventName) {
            var isSupported = eventName in this.$element
            if (!isSupported) {
                this.$element.setAttribute(eventName, 'return;')
                isSupported = typeof this.$element[eventName] === 'function'
            }
            return isSupported
        },

        move: function (e) {
            if (!this.shown) return

            switch(e.key) {
                case 'Tab':
                case 'Enter':
                case 'Escape':
                    e.preventDefault()
                    break

                case 'ArrowUp':
                    e.preventDefault()
                    this.prev()
                    break

                case 'ArrowDown':
                    e.preventDefault()
                    this.next()
                    break
            }

            e.stopPropagation()
        },

        keydown: function (e) {
            this.suppressKeyPressRepeat = ~$.inArray(e.key, ['ArrowDown','ArrowUp','Tab','Enter','Escape'])
            this.move(e)
        },

        keypress: function (e) {
            if (this.suppressKeyPressRepeat) return
            this.move(e)
        },

        keyup: function (e) {
            switch(e.keyCode) {
                case 40: // down arrow
                case 38: // up arrow
                case 16: // shift
                case 17: // ctrl
                case 18: // alt
                    break

                case 9: // tab
                case 13: // enter
                    if (!this.shown) return
                    this.select()
                    break

                case 27: // escape
                    if (!this.shown) return
                    this.hide()
                    break

                default:
                    this.lookup()
            }

            e.stopPropagation()
            e.preventDefault()
        },

        focus: function (e) {
            this.focused = true
        },

        blur: function (e) {
            this.focused = false
            if (!this.mousedover && this.shown) this.hide()
        },

        click: function (e) {
            e.stopPropagation()
            e.preventDefault()
            this.select()
            this.$element.focus()
        },

        mouseenter: function (e) {
            this.mousedover = true
            this.$menu.find('.active').removeClass('active')
            $(e.currentTarget).addClass('active')
        },

        mouseleave: function (e) {
            this.mousedover = false
            if (!this.focused && this.shown) this.hide()
        },

        destroy: function() {
            this.hide()

            this.$element.removeData('autocomplete')
            this.$menu.remove()

            this.$element.off('.autocomplete')
            this.$menu.off('.autocomplete')

            this.$element = null
            this.$menu = null
        }
    }


    /* AUTOCOMPLETE PLUGIN DEFINITION
     * =========================== */

    var old = $.fn.autocomplete

    $.fn.autocomplete = function (option) {
        return this.each(function () {
            var $this = $(this)
                , data = $this.data('autocomplete')
                , options = typeof option == 'object' && option
            if (!data) $this.data('autocomplete', (data = new Autocomplete(this, options)))
            if (typeof option == 'string') data[option]()
        })
    }

    $.fn.autocomplete.defaults = {
        source: [],
        items: 8,
        menu: '<ul class="autocomplete dropdown-menu"></ul>',
        item: '<li><a href="#"></a></li>',
        minLength: 1,
        bodyContainer: false
    }

    $.fn.autocomplete.Constructor = Autocomplete


    /* AUTOCOMPLETE NO CONFLICT
     * =================== */

    $.fn.autocomplete.noConflict = function () {
        $.fn.autocomplete = old
        return this
    }


    /* AUTOCOMPLETE DATA-API
     * ================== */

    function paramToObj(name, value) {
        if (value === undefined) value = ''
        if (typeof value == 'object') return value

        try {
            return ocJSON("{" + value + "}")
        }
        catch (e) {
            throw new Error('Error parsing the '+name+' attribute value. '+e)
        }
    }

    $(document).on('focus.autocomplete.data-api', '[data-control="autocomplete"]', function (e) {
        var $this = $(this)
        if ($this.data('autocomplete')) return

        var opts = $this.data()

        if (opts.source) {
            opts.source = paramToObj('data-source', opts.source)
        }

        $this.autocomplete(opts)
    })

}(window.jQuery);


/* =============================================================
 * bootstrap-autocomplete.js v2.3.1
 * http://twitter.github.com/bootstrap/javascript.html#autocomplete
 * =============================================================
 * Copyright 2012 Twitter, Inc.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 * ============================================================ */
