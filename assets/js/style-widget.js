
(function($) {

    var _jsFiles = {}, _cssKeys = {};

    $(window).load(function() {
        $('script[src]').each(function() {
            _jsFiles[$(this).attr('src')] = true;
        });
    });

    $.fn.styleWidget = function(method) {

        if (methods[method])
            return methods[method].apply( this, Array.prototype.slice.call(arguments, 1));
        else if (typeof method === 'object' || !method)
            return methods.init.apply(this, arguments);
        else
            $.error('jQuery.styleWidget: Method "' +  method + '" not found.');
    };

    var methods = {

        add: function(id) {
            if(this.find('.css-style-props [data-css-prop='+id+']').length > 0)
                return false;
            var $widget = this;
            var obj = this.data('obj');
            var options = this.data('options');
            $.get(options.url, {

                baseName: this.attr('name'),
                propName: id

            }, function (data) {

                var posCodes = { POS_HEAD: 1, POS_BEGIN: 2, POS_END: 3, POS_READY: 4, POS_LOAD: 5 };
                var $head = $('head');
                var $body = $('body');
                $.each(data.cssFiles, function(url, code) {
                    if($('link[href="'+url+'"][rel="stylesheet"]').length == 0)
                        $head.append(code);
                });
                $.each(data.css, function(key, code) {
                    if(_cssKeys[key] === undefined) {
                        $head.append(code);
                        _cssKeys[key] = true;
                    } else
                        console.log('CSS.Skip: ' + key);
                });

                var jsItems = [];
                $.each(posCodes, function(k, v) {
                    jsItems[v] = [];
                });

                var js2load = [];
                $.each(data.jsFiles, function(pos, files) {
                    $.each(files, function(url, code) {
                        if(_jsFiles[url] === undefined) {
                            js2load.push(url);
                            _jsFiles[url] = true;
                        } else
                            console.log('JSFile.Skip: ' + url);
                    });
                });

                var left = js2load.length;
                if(left > 0) {
                    $.each(js2load, function(k, url) {
                        console.log('Loading: ' + url + ', left = ' + left);
                        $.getScript(url, function() {
                            left--;
                            console.log('Loaded: ' + url + ', left = ' + left);
                            if(left < 1) {
                                console.log('All js sources loaded!');
                                addRows();
                            }
                        });
                    });
                } else
                    addRows();

                function addRows()
                {
                    console.log('Adding row...');
                    obj.$list.append(data.row);
                    $widget.trigger('propsAdded.styleWidget');

                    console.log('Eval js...');
                    $.each(data.js, function(pos, js) {
                        $.each(js, function(key, code) {
                            $.globalEval(code);
                        });
                    });
                }

/*
                var $rows = obj.$list.find('[data-css-prop]');
                if($rows.length > 0) {
                    console.log($rows.length);
                } else {
                    obj.$list.append(data.row);
                }
*/
            }).fail(function () {
                console.log('failed');
            });
        },

        del: function(id) {
            var $widget = this;
            var $row = this.find('.css-style-props [data-css-prop='+id+']');
            if($row.length > 0) {
                $row.fadeOut(300, function() {
                    $(this).remove();
                    $widget.styleWidget('changeSelect');
                });
            }
        },

        changeSelect: function(id) {
            var options = this.data('options');
            var obj = this.data('obj');
            if(id === undefined) {
                id = obj.$select.select2('val');
            } else {
                obj.$select.select2('val', id);
            }
            var prevID = this.data('_prevID');
            var prevBG = this.data('_prevBG');
            if(prevID !== undefined && prevBG !== undefined) {
                this.find('.css-style-props [data-css-prop='+prevID+']').
                    finish().css('background-color', prevBG);
            }
            obj.$button.attr('disabled', 'disabled').
                removeClass(options.btnAddClass).
                removeClass(options.btnDelClass);
            if(id.length < 1)
                return;
            var $row = this.find('.css-style-props [data-css-prop='+id+']');
            if($row.length > 0) {
                obj.$button.html(options.btnDelLabel).addClass(options.btnDelClass);
                if(id !== prevID) {
                    this.data('_prevBG', $row.css('background-color'));
                    $row.effect('highlight', function() {
                        if(obj.$select.select2('val') == id)
                            $row.css('background-color', options.activeRowBG);
                    });
                }
            } else {
                obj.$button.html(options.btnAddLabel).addClass(options.btnAddClass);
            }
            obj.$button.removeAttr('disabled');
            this.data('_prevID', id);
        },

        init: function(params) {

            var options = $.extend({
                btnAddLabel: '+',
                btnAddClass: '',
                btnDelLabel: '-',
                btnDelClass: '',
                activeRowBG: '#ffffee',
                delRowBG: '#ffeeee',
                delRowConfirm: 'Are you sure?',
                url: ''
            }, params);

            options.propsOrder = {};
            options.propsAvail.forEach(function(item, i) {
                options.propsOrder[item.id] = i+1;
            });
            this.data('options', options);

            var $widget = this;
            var obj = {
                $select: this.find('.css-style-toolbar input.css-prop-select'),
                $button: this.find('.css-style-toolbar button'),
                $list: this.find('.css-style-props')
            };
            this.data('obj', obj);

            obj.$select.select2({
                width: '100%',
                dropdownAutoWidth: true,
                data: { results: options.propsAvail, text: 'label' },
                formatResult: function(prop, container, query) {
                    return '<strong>' + prop.label + '</strong><br><small>' + prop.id + '</small>';
                },
                formatSelection: function(prop, container) {
                    return '<strong>' + prop.label + '</strong>';
                }
            }).on('change.styleWidget', function(event) {
                $widget.styleWidget('changeSelect');
            });

            obj.$button.on('click.styleWidget', function() {
                var id = obj.$select.select2('val');
                var $row = $widget.find('.css-style-props [data-css-prop='+id+']');
                if($row.length > 0) {
                    var prevBG = $row.css('background-color');
                    $row.css('background-color', options.delRowBG);
                    if(confirm(options.delRowConfirm))
                        $widget.styleWidget('del', id);
                    else
                        $row.css('background-color', prevBG);
                } else {
                    $widget.styleWidget('add', id);
                }
            });

            this.on('propsAdded.styleWidget', function() {
                $rows = obj.$list.find('[data-css-prop]');
                $rows.off('click.styleWidget').on('click.styleWidget', function() {
                    $widget.styleWidget('changeSelect', $(this).attr('data-css-prop'));
                });
                $widget.styleWidget('changeSelect', $(this).attr('data-css-prop'));
            });

            this.trigger('propsAdded.styleWidget');

        }

    };

})(jQuery);
