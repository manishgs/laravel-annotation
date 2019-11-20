var __bind = function (fn, me) {
    return function () {
        return fn.apply(me, arguments);
    };
},
    __hasProp = {}.hasOwnProperty,
    __extends = function (child, parent) {
        for (var key in parent) {
            if (__hasProp.call(parent, key)) child[key] = parent[key];
        }

        function ctor() {
            this.constructor = child;
        }
        ctor.prototype = parent.prototype;
        child.prototype = new ctor();
        child.__super__ = parent.prototype;
        return child;
    };

Annotator.Plugin.Shape = (function (_super) {
    __extends(Shape, _super);
    Shape.prototype.events = {
        'annotationEditorSubmit': 'onAnnotationEditorSubmit'
    };
    Shape.prototype.options = {};

    function Shape(element, options) {
        this.onAnnotationEditorSubmit = __bind(this.onAnnotationEditorSubmit, this);
        Shape.__super__.constructor.apply(this, arguments);
    }

    Shape.prototype.pluginInit = function (options) {
        var annotator = this.annotator;
        var el = annotator.element;
        var boxEl = el.find('.annotator-wrapper');
        var self = this;

        var enableDragableResizable = function (annotation) {
            if (!annotation.shapes) return;
            var el = $('.annotator-' + annotation.id);
            el.resizable({
                helper: "ui-resizable-helper",
                stop: updateChange,
                maxWidth: el.parent().width(),
                minHeight: 20,
                minWidth: 20,
            });
            el.draggable({
                containment: "parent",
                cursor: "move",
                scroll: true,
                stop: updateChange,
            });
        };

        var disableAnnotation = function () {
            boxEl.boxer({ disabled: true });
        };

        var enableAnnotation = function () {
            if ($('body').hasClass('mode_shape')) {
                boxEl.boxer({ disabled: false, shape: $('body').data('shape') });
            } else {
                boxEl.boxer('destroy');
            }
        };

        annotator.subscribe("annotationCreated", enableDragableResizable);

        annotator.subscribe("annotationsLoaded", function (annotations) {
            boxEl.find('div.annotator-hl').remove();
            annotations.forEach(function (ann) {
                if (ann.shapes) {
                    self.annotationLoader(ann);
                    enableDragableResizable(ann);
                }
            });
        });

        annotator.subscribe("annotationDeleted", function (annotation) {
            $('.annotator-' + annotation.id).remove();
        });

        annotator.subscribe("annotationEditorShown", function (editor, annotation) {
            if (!annotation.shapes) return;
            disableAnnotation()
            boxEl.find('div.annotator-hl').draggable({ disabled: true });
            boxEl.find('div.annotator-hl').resizable({ disabled: true });
        });

        annotator.subscribe("annotationEditorHidden", function (editor) {
            if (!editor.annotation.shapes) return;
            enableAnnotation()
            boxEl.find('div.annotator-hl').draggable({ disabled: false });
            boxEl.find('div.annotator-hl').resizable({ disabled: false });
        });

        var updateChange = function (e, ele) {
            var el = $(e.target);
            $(annotator.viewer.element).addClass('annotator-hide');

            if (e.type == 'resizestop') {
                $(e.target).css('height', (el.height() + 'px'));
                $(e.target).css('width', (el.width() + 'px'));
            }

            var shape = [];
            shape.top = el.offset().top;
            shape.left = el.offset().left;
            shape.height = el.height();
            shape.width = el.width();

            disableAnnotation();
            $(e.target).find('div.annotator-resize-action').remove();
            boxEl.find('div.annotator-hl').removeClass('resizable-active');
            $(e.target).addClass('resizable-active');
            $(e.target).append(self.resizeButtons());
            var hl = el.find('div.annotator-hl').draggable("option", "disabled", true);
            boxEl.find('div.annotator-hl').draggable({ disabled: true });
            boxEl.find(e.target).draggable({ disabled: false });

            boxEl.find('div.annotator-hl').resizable({ disabled: true });
            boxEl.find(e.target).resizable({ disabled: false });
        };

        $(boxEl).on('click', '.annotator-resize-action button.cancel', function () {
            var el = $(this).parent().parent();
            var annotator = el.data('annotation');
            var shape = annotator.shapes[0].geometry;
            shape = self.getShape(shape);
            el.css({ top: shape.y, left: shape.x, height: shape.height, width: shape.width });
            el.find('.annotator-resize-action').remove();
            enableAnnotation();
            boxEl.find('div.annotator-hl').draggable({ disabled: false });
            boxEl.find('div.annotator-hl').resizable({ disabled: false });
        });

        $(boxEl).on('click', '.annotator-resize-action button.save', function (e) {
            var el = $(this).parent().parent();
            var annotator = el.data('annotation');
            var geometry = [];
            geometry.y = parseInt(el.css('top'));
            geometry.x = parseInt(el.css('left'));
            geometry.height = parseInt(el.css('height'));
            geometry.width = parseInt(el.css('width'));
            annotator.shapes[0].geometry = self.getGeoInPercentage(geometry);
            self.annotator.publish('annotationUpdated', annotator);
            el.find('button').remove();
            enableAnnotation();
            boxEl.find('div.annotator-hl').draggable({ disabled: false });
            boxEl.find('div.annotator-hl').resizable({ disabled: false });
        });

        var data = boxEl.boxer({
            shape: $('body').data('shape'),
            stop: function (event, ui) {
                var offset = [];
                offset.top = parseInt(ui.box.css('top'));
                offset.left = parseInt(ui.box.css('left'));
                offset.height = parseInt(ui.box.css('height'));
                offset.width = parseInt(ui.box.css('width'));
                ui.box.addClass('annotator-raw');
                var pos = {};
                pos.top = offset.top + offset.height / 2;
                pos.left = offset.left + offset.width / 2;
                annotator.showEditor({ shapes: self.getShapeDataFormat(offset), box: ui.box }, pos);
            }
        });

        $('.annotator-controls').on('click', 'a.annotator-cancel', function () {
            $('.annotator-raw').remove();
            enableAnnotation()
        });

        el.on('mouseover', 'div', function () {
            if ($(this).find('.save').length > 0) {
                $(annotator.viewer.element).addClass('annotator-hide');
                return '';
            }

            var annotation = $(this).data('annotator');

            if (annotation) {
                var pos = {};
                pos.top = parseInt($(this).css('top')) + parseInt($(this).height());
                pos.left = parseInt($(this).css('left')) + parseInt($(this).width() / 2);
                annotator.showViewer([annotation], pos);
            }
        })
    };

    Shape.prototype.getShapeDataFormat = function (offset) {
        return [{ type: "rect", geometry: { x: offset.left, y: offset.top, height: offset.height, width: offset.width } }];
    };

    Shape.prototype.resizeButtons = function () {
        return '<div class="btn-group annotator-resize-action" role="group">' +
            '<button title="Save" class="save btn btn-primary"><span class="fa fa-floppy-o"' +
            ' aria-hidden="true"></span></button> ' +
            '<button title="Cancel" class="cancel btn btn-danger" ><span class="fa fa-times' +
            ' aria-hidden="true"></span></button>'
        '</div>';
    };

    Shape.prototype.annotationLoader = function (annotation) {
        var geo = annotation.shapes[0].geometry;
        var type = annotation.shapes[0].type;

        geo = this.getShape(geo);

        var div = $('<div></div>')
            .data('annotation', annotation)
            .attr('data-annotation-id', annotation.id)
            .addClass('annotator-' + annotation.id)
            .addClass('annotator-hl')
            .addClass('annotator-pdf-hl')
            .appendTo(this.annotator.element.find('.annotator-wrapper'))
            .css({ position: 'absolute', left: geo.x, top: geo.y, height: geo.height, width: geo.width });

        if (type == 'circle') {
            div.addClass('ui-boxer-round');
        }

        updateProperties(annotation);
    };

    Shape.prototype.getShape = function (geometry) {
        var canvas = this.annotator.element.find('canvas');
        var g = {};
        g.x = geometry.x * canvas.width();
        g.y = geometry.y * canvas.height();
        g.height = geometry.height * canvas.height();
        g.width = geometry.width * canvas.width();
        return g;
    };

    Shape.prototype.getGeoInPercentage = function (geometry) {
        var canvas = this.annotator.element.find('canvas');
        var g = {};
        g.x = geometry.x / canvas.width();
        g.y = geometry.y / canvas.height();
        g.height = geometry.height / canvas.height();
        g.width = geometry.width / canvas.width();
        return g;
    };

    Shape.prototype.onAnnotationEditorSubmit = function (editor) {
        if (editor.annotation.box !== undefined) {
            var hl = editor.annotation.box;
            var box = editor.annotation.box;
            delete editor.annotation.box;
            editor.annotation.id = Date.now();
            hl.data('annotation', editor.annotation)
                .removeClass('annotator-raw')
                .attr('data-annotation-id', editor.annotation.id)
                .addClass('annotator-' + editor.annotation.id)
                .addClass('annotator-hl')
                .addClass('annotator-pdf-hl');
            var geometry = editor.annotation.shapes[0].geometry;
            geometry.x = parseInt(box.css('left'));
            geometry.y = parseInt(box.css('top'));
            geometry.height = parseInt(box.css('height'));
            geometry.width = parseInt(box.css('width'));
            editor.annotation.shapes[0].geometry = this.getGeoInPercentage(geometry);
            if (hl.hasClass('ui-boxer-round')) {
                editor.annotation.shapes[0].type = 'circle';
            }
            this.annotator.publish('annotationCreated', editor.annotation);
        }
    };
    return Shape;
})(Annotator.Plugin);
