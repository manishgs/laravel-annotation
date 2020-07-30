var updateProperties = function(annotation) {
    console.log("%c redaction_permission inside annotator.properties.js -->" + redaction_permission, "color:magenta");

    var el, type;
    if (annotation.highlights) {
        el = $(annotation.highlights);
        type = 'text';
    } else {
        el = $('.annotator-' + annotation.id);
        type = 'pdf';
    }

    var properties = annotation['properties'];
    if (!properties) return;

    if (type === 'pdf') {
        if (properties['borderColor'] || properties['borderWidth']) {
            el.css({ 'border-style': 'solid' })
        }

        if (properties['borderColor']) {
            el.css({ 'border-color': properties['borderColor'] })
        } else {
            el.css({ 'border-color': 'transparent' })
        }

        if (properties['fillColor']) {
            el.css({ 'background-color': properties['fillColor'] })
        } else {
            el.css({ 'background-color': 'transparent' })
        }

        if (properties['borderWidth']) {
            el.css({ 'border-width': properties['borderWidth'] + 'px' })
        }
    }

    if (type === 'text') {
        if (properties['underline']) {
            el.css({ 'border-bottom': '1px solid black' });
        } else {
            el.css({ 'border-bottom': 'none' });
        }

        if (properties['strikeThrough']) {
            el.addClass('strikeThrough');
        } else {
            el.removeClass('strikeThrough');
        }

        if (properties['redaction']) {
            el.addClass('redaction')
            // check permission to show/hide text
            if (1 == 1) {
                if (redaction_permission) {
                    var button = $("<a href='#' class='redaction-toggle'></a>");
                    button.on('click', function() {
                        el.toggleClass('redaction');
                        $(this).toggleClass('icon-hide');
                    });
                    $(el[el.length - 1]).append(button);
                    console.log(el);
                }

            }
        } else if (properties['highlightColor']) {
            el.css({ 'background-color': properties['highlightColor'] })
        } else {
            el.css({ 'background-color': 'transparent' })
        }
    }
};

Annotator.Plugin.Properties = (function(_super) {
    __extends(Properties, _super);

    function Properties() {
        Properties.__super__.constructor.call(this, arguments);
    }

    Properties.prototype.pluginInit = function(options) {
        var self = this;
        this.annotator.subscribe("annotationCreated", updateProperties);
        this.annotator.subscribe("annotationUpdated", updateProperties);
        this.annotator.subscribe("annotationUpdated", updateProperties);
        this.annotator.subscribe("annotationEditorHidden", function(editor) {
            delete editor.annotation.text;
            delete editor.annotation.isComment;
        });

        this.annotator.subscribe("annotationsLoaded", function(annotations) {
            annotations.forEach(function(ann) {
                if (ann.ranges) {
                    updateProperties(ann);
                }
            });
        });

        this.annotator.viewer.addField({
            load: function(field, annotation) {
                var div = $('<button title="Add comment" class="annotator-edit annotator-add">Add comment</button>');
                div.on('click', function(e) {
                    annotation.isComment = true;
                });
                $(field).parent().find('.annotator-controls').prepend(div);
                if (annotation.comments && annotation.comments.length) {
                    annotation.comments = annotation.comments.filter(c => c.text);
                    if (annotation.comments.length) {
                        var html = '<ul style="padding:0px; margin:15px 0px 10px; list-style:none">';
                        annotation.comments.forEach(function(comment, i) {
                            if (comment.text) {
                                html += '<li data-id="' + i + '" style="padding:10px 10px 10px 15px">' +
                                    '<span style="color:#404040">' + comment.text + '</span><p style="margin-top:5px">By ' +
                                    (comment.created_by ? comment.created_by.username : '') + ' <br/> ' +
                                    comment.created_date +
                                    '</p> <span class="annotator-item-controls">' +
                                    '<button title="Edit Comment" class="annotator-edit">Edit</button>' +
                                    '<button title="Delete Comment" class="annotator-comment-delete">Delete</button>' +
                                    '</span>' +
                                    '</li>';
                            }
                        });
                        html += '</ul>';
                        $(field).css('padding', '0px').html(html);
                        $(field).parent().find('div:first').hide();
                        $(field).find('.annotator-edit').on('click', function() {
                            var parent = $(this).parent().parent();
                            var id = parent.data('id');
                            annotation.isComment = { id };
                        });
                        $(field).find('.annotator-comment-delete').on('click', function() {
                            if (confirm('Do you want to delete this comment?')) {
                                var parent = $(this).parent().parent();
                                var id = parent.data('id');
                                annotation.comments = annotation.comments.filter((v, i) => i !== id);
                                parent.remove();
                                self.annotator.plugins.Store.annotationUpdated(annotation);
                                self.annotator.viewer.hide();
                            }
                        })
                    }
                } else {
                    $(field).remove();
                }
            }
        });

        this.annotator.editor.addField({
            load: function(el, annotation) {
                $(el).parent().find('textarea').parent().show();

                annotation.properties = annotation.properties ? annotation.properties : {};

                if (annotation.shapes) {
                    $(el).show();
                    $(el).addClass('annotation-border-label');
                    $(el).html('<label class="border-label">Border</label>');
                } else {
                    $(el).hide();
                }

                if (annotation.isComment) {
                    var text = '';
                    if (annotation.comments && annotation.comments[annotation.isComment.id]) {
                        text = annotation.comments[annotation.isComment.id].text;
                    }
                    $(el).parent().find('textarea').val(text);
                    $(el).parent().find('textarea').parent().show();
                    $(el).parent().find('.annotation-border-label').hide();
                    return;
                }

                if (annotation.id) {
                    $(el).parent().find('textarea').parent().hide();
                    return;
                }
            },
            submit: function(el, annotation) {
                if (annotation.text) {
                    if (annotation.comments && annotation.comments[annotation.isComment.id]) {
                        annotation.comments[annotation.isComment.id].text = annotation.text;
                    } else {
                        comment = { text: annotation.text, added_by: USERNAME, added_at: Date.now() };
                        if (!annotation.comments) {
                            annotation.comments = [comment];
                        } else {
                            annotation.comments.push(comment);
                        }
                    }
                }

                delete annotation.text;
                delete annotation.isComment;
                delete annotation.selected;
            }
        });

        this.annotator.editor.addField({
            label: 'Border',
            type: 'input',
            load: function(el, annotation) {
                $(el).addClass('annotation-border-color');
                self.updateBorderColor(el, annotation);
                if (annotation.shapes && !annotation.isComment) {
                    $(el).show();
                } else {
                    $(el).hide();
                }
            },
            submit: function(el, annotation) {
                if (annotation.shapes) {
                    self.saveBorderColor(el, annotation)
                }
            }
        });


        this.annotator.editor.addField({
            label: 'Fill',
            type: 'input',
            load: function(el, annotation) {
                $(el).addClass('annotation-fill-color');
                self.updateFillColor(el, annotation);
                if (annotation.shapes && !annotation.isComment) {
                    $(el).show();
                } else {
                    $(el).hide();
                }
            },
            submit: function(el, annotation) {
                if (annotation.shapes) {
                    self.saveFillColor(el, annotation);
                }
            }
        });

        this.annotator.editor.addField({
            label: 'highlight',
            type: 'input',
            load: function(el, annotation) {
                $(el).addClass('annotation-text-highlight');
                self.updateHighlightColor(el, annotation);
                if (annotation.ranges && !annotation.isComment) {
                    $(el).show();
                } else {
                    $(el).hide();
                }
            },
            submit: function(el, annotation) {
                if (annotation.ranges) {
                    self.saveHighlightColor(el, annotation);
                }
            }
        });


        this.annotator.editor.addField({
            type: 'input',
            load: function(el, annotation) {
                $(el).addClass('annotation-border-width');
                self.updateBorderWidth(el, annotation);
                if (annotation.shapes && !annotation.isComment) {
                    $(el).show();
                } else {
                    $(el).hide();
                }
            },
            submit: function(el, annotation) {
                if (annotation.shapes) {
                    self.saveBorderWidth(el, annotation);
                }
            }
        });


        this.annotator.editor.addField({
            label: 'underline',
            type: 'checkbox',
            load: function(el, annotation) {
                $(el).addClass('annotation-text-underline');
                self.updateUnderline(el, annotation);
                if (annotation.ranges && !annotation.isComment) {
                    $(el).show();
                } else {
                    $(el).hide();
                }
            },
            submit: function(el, annotation) {
                if (annotation.ranges) {
                    self.saveUnderline(el, annotation)
                }
            }
        });

        this.annotator.editor.addField({
            label: 'strikeThrough',
            type: 'checkbox',
            load: function(el, annotation) {
                $(el).addClass('annotation-text-strikethrough');
                self.updateStrikeThrough(el, annotation);
                if (annotation.ranges && !annotation.isComment) {
                    $(el).show();
                } else {
                    $(el).hide();
                }
            },
            submit: function(el, annotation) {
                if (annotation.ranges) {
                    self.saveStrikeThrough(el, annotation);
                }
            }
        });

        this.annotator.editor.addField({
            label: 'redaction',
            type: 'checkbox',
            load: function(el, annotation) {
                $(el).addClass('annotation-text-redaction');
                self.updateRedaction(el, annotation);
                if (annotation.ranges && !annotation.isComment) {
                    $(el).show();
                } else {
                    $(el).hide();
                }
            },
            submit: function(el, annotation) {
                if (annotation.ranges) {
                    self.saveRedaction(el, annotation);
                }
            }
        });
    };

    // Border Color
    Properties.prototype.updateBorderColor = function(el, annotation) {
        var borderColor = annotation.properties.borderColor
        if (typeof borderColor === 'undefined') {
            borderColor = "#f00";
        }

        $(el).find('input').addClass('borderColor').val(borderColor);
        $(el).find('input').parent().find('label').remove();
        $(el).find('input').before('<label>Color </label>');
        $(el).find('input').spectrum({
            color: borderColor,
            allowEmpty: true,
            preferredFormat: 'hex'
        });
    };

    Properties.prototype.saveBorderColor = function(el, annotation) {
        annotation.properties.borderColor = $(el).find('input').val();
    };

    // Fill color
    Properties.prototype.updateFillColor = function(el, annotation) {
        var fillColor = annotation.properties.fillColor
        if (typeof annotation.properties.fillColor === 'undefined') {
            fillColor = "rgba(255, 255, 10, 0.3)";
        }
        $(el).find('input').addClass('fillColor').val(fillColor);
        $(el).find('input').parent().find('label').remove();
        $(el).find('input').before('<label>Fill </label>')
        $(el).find('input').spectrum({
            color: fillColor,
            allowEmpty: true,
            showAlpha: true,
            preferredFormat: 'rgb',
        });
    };

    Properties.prototype.saveFillColor = function(el, annotation) {
        annotation.properties.fillColor = $(el).find('input').val();
    };

    // border width
    Properties.prototype.saveBorderWidth = function(el, annotation) {
        annotation.properties.borderWidth = $(el).find('input').val() || '0';
    };

    Properties.prototype.updateBorderWidth = function(el, annotation) {
        var borderWidth = annotation.properties.borderWidth
        if (typeof annotation.properties.borderWidth === 'undefined') {
            borderWidth = "1";
        }

        $(el).find('input').addClass('borderWidth').val(borderWidth || 0);
        $(el).find('input').parent().find('label').remove();
        $(el).find('input').before('<label>Width </label>');
        $(el).find('input').after('<label> px</label>');
    };


    Properties.prototype.updateUnderline = function(el, annotation) {
        $(el).find('input').prop('checked', annotation.properties.underline || false);
    };

    Properties.prototype.saveUnderline = function(el, annotation) {
        annotation.properties.underline = $(el).find('input').prop('checked');
    };

    Properties.prototype.updateStrikeThrough = function(el, annotation) {
        $(el).find('input').prop('checked', annotation.properties.strikeThrough || false);
    };

    Properties.prototype.saveStrikeThrough = function(el, annotation) {
        annotation.properties.strikeThrough = $(el).find('input').prop('checked');
    };

    Properties.prototype.updateRedaction = function(el, annotation) {
        $(el).find('input').prop('checked', annotation.properties.redaction || false);
    };

    Properties.prototype.saveRedaction = function(el, annotation) {
        annotation.properties.redaction = $(el).find('input').prop('checked');
    };


    Properties.prototype.updateHighlightColor = function(el, annotation) {
        var highlightColor = annotation.properties.highlightColor
        if (typeof annotation.properties.highlightColor === 'undefined') {
            highlightColor = "rgba(255, 255, 10, 0.3)";
        }
        $(el).find('input').addClass('highlightColor').val(highlightColor);
        $(el).find('input').parent().find('label').remove();
        $(el).find('input').before('<label>Highlight </label>')
        $(el).find('input').spectrum({
            color: highlightColor,
            allowEmpty: true,
            showAlpha: true,
            preferredFormat: 'rgb'
        });
    };

    Properties.prototype.saveHighlightColor = function(el, annotation) {
        annotation.properties.highlightColor = $(el).find('input').val();
    };

    return Properties;
})(Annotator.Plugin);