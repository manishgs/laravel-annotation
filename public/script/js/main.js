$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
});

function apiUrl(ep) {
    return BASE_URL + '/' + ep.trimLeft('/');
}

// when page load highlight annotation
var highlightAnnotation = null;
var createdList = [];

/* 
    Helper Functions
*/
/* edited vishnu */
var getUrl = window.location;
var baseurl = getUrl.protocol + "//" + getUrl.host + "/" + getUrl.pathname.split('/')[1];
//console.log(baseurl);
/* edited vishnu */

function debounce(func, wait, immediate) {
    var timeout;
    return function() {
        var context = this,
            args = arguments;
        var later = function() {
            timeout = null;
            if (!immediate) func.apply(context, args);
        };
        var callNow = immediate && !timeout;
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
        if (callNow) func.apply(context, args);
    };
}

function getDateFormat(timestamp) {
    var date = moment(parseInt(timestamp));
    return date.format('hh:mm a, MMM D, Y');
}

// --------------------------------------------------------------------------------- 



/* 
    Stamps
*/

const defaultWidth = 200;
const isChrome = /Chrome/.test(navigator.userAgent) && /Google Inc/.test(navigator.vendor);

function loadStampList() {
    console.log("function loadStampList");
    var html = STAMPS.map(function(stamp) {
        return '<li><div class="stamp-select">' + getStampTemplate(stamp) + '</div></li>';
    });

    $('.stamp-list').append(html);

    // make stamp draggable
    $(".stamp-select").draggable({
        helper: 'clone',
        cursor: 'move',
        drag: function(e) {
            var el = $(e.target).parent().find('.ui-draggable-dragging .stamp-item');
            $(el).find('*').css({ zoom: PDFViewerApplication.pdfViewer._currentScale })
        },
    });
}

function loadStampListText() {
    console.log("function loadStampListText");
    var html = STAMPS_TEXTS.map(function(stamp) {

        return '<li><div class="stamp-select">' + getStampTemplate(stamp) + '</div></li>';
    });

    $('.stamp-text-list').append(html);

    // make stamp draggable
    $(".stamp-select").draggable({
        helper: 'clone',
        cursor: 'move',
        drag: function(e) {
            var el = $(e.target).parent().find('.ui-draggable-dragging .stamp-item');
            $(el).css({ zoom: PDFViewerApplication.pdfViewer._currentScale })
        },
    });
}

function getStampUrlById(id) {
    var stamps = STAMPS.filter(function(s) {
        return s.id === id;
    });

    return stamps.length ? stamps[0] : '';
}

function getStampUrlByIdText(id) {
    console.log(id);
    var stamps = STAMPS_TEXTS.filter(function(s) {
        return s.id === id;
    });

    return stamps.length ? stamps[0] : '';
}

/*code by manish*/
function getStampTemplate1(stamp) {
    const html = stamp.type == 'image' ? '<img src="' + stamp.value + '" />' : stamp.value
    const className = stamp.type == 'image' ? 'stamp-image' : 'stamp-text';
    return '<div class="stamp-item stamp-block ' + className + ' stamp-' + stamp.id + '" data-stamp="' + stamp.id + '">' +
        html +
        '</div>';
}
/*code by manish*/
/*code by vishnu*/
function getStampTemplate(stamp) {
    console.log("stamp.type-->" + stamp.type);
    if (stamp.type == 'image') {
        var html = '<img src="' + stamp.value + '" />';
        var className = 'stamp-image';
    } else if (stamp.type == 'draw') {
        var html = '<img src="' + stamp.value + '" />';
        var className = 'stamp-image';
    } else if (stamp.type == 'text') {
        var html = stamp.value;
        var className = 'stamp-text';
    }
    return '<div class="stamp-item stamp-block ' + className + ' stamp-' + stamp.id + '" data-stamp="' + stamp.id + '">' +
        html +
        '</div>';
}

/*code by vishnu*/
function getStampPositionInPercentage(position, page) {
    var canvas = $('#viewer').find('.page:nth-child(' + page + ')').find('canvas');
    position.top = position.top / canvas.height();
    position.left = position.left / canvas.width();
    position.height = position.height / canvas.height();
    position.width = position.width / canvas.width();
    return position;
}

function getposition(position, page) {
    var canvas = $('#viewer').find('.page:nth-child(' + page + ')').find('canvas');
    position.top = position.top * canvas.height();
    position.left = position.left * canvas.width();
    position.height = position.height * canvas.height();
    position.width = position.width * canvas.width();
    return position;
}

// Stamp
function saveStamp(el, data) {
    data.position = getStampPositionInPercentage(data.position, data.page);
    var req = {
        method: "POST",
        url: apiUrl("stamp"),
        data
    };

    if (el.data('stamp')) {
        data.id = el.data('stamp').id;
        req = {
            method: "PUT",
            url: apiUrl("stamp/" + data.id),
            data
        }
    }

    $.ajax(req).done(function(data) {
        if (Object.keys(data).length > 1) {
            el.data('stamp', data);
        }
    });
}

function deleteStamp(stamp) {
    $.ajax({
        method: "DELETE",
        url: apiUrl("stamp/" + stamp.id),
    }).done(function(data) {});
}

function loadStamp(page, callback) {
    $.ajax({
        method: "GET",
        url: apiUrl("stamp/" + PDF.id + "/" + page)
    }).done(function(data) {
        callback(data);
    });
}

function updateTextZoom(el, shape) {
    const data = el.data('stamp');
    const type = data.stamp_image_id || data;
    const zoom = shape.width / defaultWidth;
    let font = zoom >= 1 ? type == 1 ? 12 : 10 : 15;
    let pfont = zoom >= 1 ? 30 : 27;
    if (isChrome) {
        el.find('span').css({ 'zoom': zoom, 'font-size': font });
        el.find('p').css({ 'zoom': zoom, 'font-size': pfont })
    } else {
        el.find('span').css({ 'font-size': font * zoom });
        el.find('p').css({ 'font-size': pfont * zoom })
    }
}

function loadStampText(page, callback) {
    $.ajax({
        method: "GET",
        url: baseurl + "/stamp/" + PDF.id + "/" + page
    }).done(function(data) {

        callback(data);
    });
}

function renderStamp(shape, draggable, type) {
    var zoom = PDFViewerApplication.pdfViewer._currentScale;
    var div = $('<div class="stamp"></div>');
    draggable.css({ 'zoom': zoom });
    shape.width = shape.width || defaultWidth;
    shape.height = shape.height || (shape.width * getStampRatio(type));
    div.css(shape);
    div.html(draggable);
    var trash = $('<div class="delete-stamp"><img src="/script/images/trash.svg" /></div>');
    trash.on('click', function() {
        var stamp = $(this).parent().data('stamp');
        $(this).parent().remove();
        if (stamp) {
            deleteStamp(stamp);
        }
    });
    div.prepend(trash);
    updateTextZoom(draggable, shape);
    return div;
}

function stampDraggable(el, data) {
    el.draggable({
        containment: "parent",
        cursor: "move",
        scroll: true,
        stop: updateStampChange(data),
    });

    el.resizable({
        helper: "stamp-resizable-helper",
        stop: updateStampChange(data),
        minHeight: 80 * PDFViewerApplication.pdfViewer._currentScale,
        minWidth: 100 * PDFViewerApplication.pdfViewer._currentScale,
        aspectRatio: false
    })
}

function updateStampChange(data) {
    return function(e, ele) {
        var el = $(e.target);
        var position = {};
        var width = parseInt(el.css('width').replace('px', ''));
        var height = parseInt(el.css('height').replace('px', ''));

        if (e.type == 'resizestop') {
            width = ele.size.width;
            height = ele.size.height;

            el.css('height', height + 'px');
            el.css('width', width + 'px');
        }

        position.top = el.css('top').replace('px', '');
        position.left = el.css('left').replace('px', '');
        position.height = height;
        position.width = width;

        updateTextZoom(el, position);
        data.position = position;
        saveStamp(el, data);
    };
}

function getStampRatio(type) {
    return type == 1 ? 0.8 : 0.4;
}

function getDateFormat(timestamp) {
    var date = moment(parseInt(timestamp));
    return date.format('hh:mm a, MMM D, Y');
}

/* Annotations */


function listenAnnotationEvents(event) {
    event.subscribe("annotationCreated", function(annotation) {
        if (createdList.findIndex(a => a.id === annotation.id) === -1) {
            createdList.push(annotation);
        }
        setTimeout(fetchAndShowAnnotations, 1000);
    });

    event.subscribe("annotationUpdated", function(annotation) {
        setTimeout(fetchAndShowAnnotations, 1000);
    });

    event.subscribe("annotationDeleted", function(annotation) {
        createdList = createdList.filter(a => a.id != annotation.id);
        setTimeout(fetchAndShowAnnotations, 1000);
    });
}

function deleteAnnotations() {
    // toggle delete options
    $('.deleteAnnotations').on('click', function() {
        $('.annotation-delete-option').toggle();
    });

    $('.annotation-option-list li').on('click', function() {
        const action = $(this).data('action');
        if (action === 'all') {
            deleteAllAnnnotations()
        } else if (action === 'session') {
            deleteSessionAnnotations()
        } else {
            alert('Invalid action')
        }

        $('.annotation-delete-option').toggle();
    });
}

function deleteAllAnnnotations() {
    if (confirm('Do you want to remove all annotations?')) {
        $.ajax({
            method: "DELETE",
            url: apiUrl("annotation/" + PDF.id + "/deleteAll"),
        }).done(function() {
            $('.annotator-pdf-hl').remove();
            $('.annotator-hl').each(function() {
                $(this).replaceWith($(this).text());
            });
            createdList.length = 0;
            fetchAndShowAnnotations();
        });
    }
}

function deleteSessionAnnotations() {
    if (!createdList.length) {
        alert('You have not created any annotion.')
        return;
    }

    if (confirm('Do you want to remove ' + createdList.length + ' annotations created during this session?')) {
        const ids = createdList.map(a => a.id);
        deleteAnnotationsByIds(ids, function() {
            createdList.length = 0;

        });
    }
}

function deleteAnnotationsByIds(ids, cb) {
    $.ajax({
        method: "DELETE",
        url: apiUrl("annotation/" + PDF.id + "/deleteAll"),
        data: { id: ids }
    }).done(function() {
        $('.annotator-pdf-hl').each(function() {
            if (ids.includes($(this).data('annotation').id)) {
                $(this.remove());
            }
        });

        $('.annotator-hl').each(function() {
            if (ids.includes($(this).data('annotation').id)) {
                $(this).replaceWith($(this).text());
            }
        })
        cb();
        fetchAndShowAnnotations();
    });
}

function onAnnotationSearch() {
    var $this = $('#annotationFindInput')
    var q = $this.val().trim();
    if (q) {
        $this.attr('data-status', 'pending');
        $.ajax({
            method: "GET",
            url: apiUrl("annotation/" + PDF.id + "/search?q=" + q)
        }).done(function(data) {
            var str = '<ul class="annotation-list">';
            var foundIds = [];
            data.rows.forEach(function(v) {
                str += '<li class="item" data-page="' + v.page + '" data-id="' + v.id + '" data-annotation="' + v.id + '" ><span>' + v.page + '</span>' + v.text + '</li>'
            });
            str += '</ul>';

            if (data.total < 1) {
                str = "<div class='no-result'>Annotations not found</div>";
            }

            $('.annotationsearchList').show().html(str);
        }).fail(function() {
            alert("Error while annotation search.");
        }).always(function() {
            $this.attr('data-status', '');
        });

    } else {
        $this.attr('data-status', '');
        $('.annotationsearchList').hide();
    }
}

function annotationSearch() {
    // show annotations search result when click on input when result is present
    $('#annotationFindInput').on('click', function() {
        if ($('.annotationsearchList').find('li').length) {
            $('.annotationsearchList').show();
        } else {
            $('.annotationsearchList').hide();
        }
    });

    // search annotations
    $('#annotationFindInput').on('input', debounce(onAnnotationSearch, 500));


    // when click on other than annoation list then hide the list
    $(document).mouseup(function(e) {
        var container = $('.annotationsearchList');
        if (!container.is(e.target) && container.has(e.target).length === 0) {
            container.hide();
        }
    });

    showAnnotationWhenClick();
}


function showAnnotationWhenClick() {
    // when click on annotation show annotation pop
    $(document).on('click', '.annotation-list .item', function() {
        $('.annotation-list .item').removeClass('active');
        $(this).addClass('active');
        if ($(this).data('type') === 'sidebar') {
            let parentDiv = $('#annotationView');
            let annotationEl = $("#annotation-" + $(this).data('id'));
            let pageOffsetTop = annotationEl.offset().top;
            let parentTop = parentDiv.scrollTop();
            let parentOffsetTop = parentDiv.offset().top;
            let vTop = parentTop - parentOffsetTop + pageOffsetTop - 5;
            parentDiv.animate({ scrollTop: vTop }, 500);
        }

        $('.annotator-viewer').addClass('annotator-hide');
        $('.annotationsearchList').hide();
        var id = $(this).data('id');
        var page = $(this).data('page');
        const content = $('#viewer').find('.page:nth-child(' + page + ')');
        var found = false;
        if (content.find('.canvasWrapper').length) {
            content.find('.annotator-hl').each(function(i, a) {
                var a = $(this);
                var annotation = a.data('annotation');
                if (!found && annotation.id == id) {
                    found = true;
                    if (PDFViewerApplication.page !== page) {
                        PDFViewerApplication.pdfViewer.currentPageNumber = page;
                    }
                    setTimeout(() => {
                        var el = annotation.highlights ? $(annotation.highlights) : $('.annotator-' + annotation.id);
                        var position = el.offset();
                        var top = position.top + $('#viewerContainer').scrollTop();
                        var left = position.left - content.offset().left;
                        $('#viewerContainer').animate({
                            scrollTop: top - 200
                        }, '500');
                        position.top = position.top - content.offset().top;
                        position.left = left + (el.width() / 2);
                        content.data('annotator').showViewer([annotation], position);
                    }, 100);
                }
            });
        }

        if (!found) {
            highlightAnnotation = { id, page };
            PDFViewerApplication.pdfViewer.currentPageNumber = page;
        }
    });
}

function loadAnnotations() {

    // load annotation when pdf text render
    document.addEventListener('pagerendered', function(event) {
        const num = event.detail.pageNumber;
        const content = $('#viewer').find('.page:nth-child(' + num + ')');

        // load stamps for the page
        loadStamp(num, function(data) {
            var digita_signed = (typeof data.digita_signed != 'undefined') ? data.digita_signed : 0;
            if (digita_signed == 1) {
                $('.digital_sign').show();
            } else {
                $('.digital_sign').hide();
            }

            if (data.rows && data.rows.length) {

                data.rows.forEach(function(v) {


                    if (v.type.stamp_type == 'text') {
                        //console.log("text");
                        var stamp = $(getStampTemplate(getStampUrlByIdText(v.stamp_image_id)));
                    } else {
                        //console.log("other");
                        var stamp = $(getStampTemplate(getStampUrlById(v.stamp_image_id)));
                    }

                    stamp.append('<span>By ' + v.created_by.name + ' <br/> ' + v.created_date + ' </span>')
                    v.position = getposition(v.position, v.page);
                    var div = renderStamp(v.position, stamp, v.stamp_image_id);
                    div.data('stamp', v);
                    content.prepend(div);
                    stampDraggable(div, v);
                });
            }
        });

    });

    document.addEventListener('textlayerrendered', function(event) {
        const num = event.detail.pageNumber;
        const content = $('#viewer').find('.page:nth-child(' + num + ')');
        // destory annotation is already loaded
        if (content.data('annotator')) {
            content.data('annotator').destroy();
            content.removeData('annotator');
        }
        // init annotation
        content.annotator();
        // for pdf annotations
        content.data('annotator').setupAnnotation = function(annotation) {
            if (annotation.ranges !== undefined || $.isEmptyObject(annotation)) {
                return content.data('annotator').__proto__.setupAnnotation.call(content.data('annotator'), annotation);
            }
        };
        content.annotator('addPlugin', 'Properties');
        content.annotator('addPlugin', 'Shape');
        content.annotator('addPlugin', 'Store', {
            prefix: '/annotation',
            annotationData: {
                'pdf_id': PDF.id,
                'page': num
            },
            loadFromSearch: {
                'pdf_id': PDF.id,
                'page': num
            },
            urls: {
                create: '',
                update: '/:id',
                destroy: '/:id',
                search: '/'
            }
        });

        // make pdf page droppable for stamps
        content.find('.annotator-wrapper').droppable({
            accept: '.stamp-select',
            activeClass: "drop-area",
            drop: function(e, ui) {
                var droppable = $(this);
                var draggable = ui.draggable.clone();
                draggable = draggable.find('.stamp-item');
                draggable.removeClass('stamp-item').removeClass('ui-draggable').removeClass('ui-draggable-handle');
                var offset = $('.ui-draggable-dragging').offset();
                var stampType = draggable.data('stamp');
                var width = 250 * PDFViewerApplication.pdfViewer._currentScale;
                var position = {
                    top: offset.top - (content.offset().top + 10),
                    left: offset.left - (content.offset().left + 10),
                    width: width,
                    height: getStampRatio(stampType) * width
                }
                console.log("%c USERNAME --> " + USERNAME, "color:orange;font-weight:bold");
                draggable.append('<span>by ' + USERNAME + ' <br/> ' + getDateFormat(Date.now()) + ' </span>')
                var div = renderStamp(position, draggable, stampType);
                droppable.parent().prepend(div);
                const data = {
                    position,
                    stamp_image_id: stampType,
                    page: num,
                    pdf_id: PDF.id
                };
                saveStamp(div, data);
                setTimeout(() => {
                    stampDraggable($('.stamp'), data);
                }, 100);
                $('.stamp-collection').hide();
            }
        });

        // when annotation loaded
        content.data('annotator').subscribe("annotationsLoaded", function(annotation) {
            // highlight annotation
            if (annotation.length && highlightAnnotation && highlightAnnotation.page === num) {
                annotation.forEach(function(annotation) {
                    if (highlightAnnotation.id === annotation.id) {
                        var el = annotation.highlights ? $(annotation.highlights) : $('.annotator-' + annotation.id);
                        var position = el.offset();
                        var top = position.top + $('#viewerContainer').scrollTop();
                        var left = position.left - content.offset().left;
                        position.top = position.top - content.offset().top;
                        position.left = left + (el.width() / 2);
                        setTimeout(() => {
                            content.data('annotator').showViewer([annotation], position);
                        }, 100);
                    }
                });

                highlightAnnotation = null;
            }
            // disable draw shape for text annotation
            if (MODE == 'text') {
                content.find('.annotator-wrapper').boxer('destroy');
            }
        });

        // listen for events
        listenAnnotationEvents(content.data('annotator'));

    }, true);
}

/* Sidebar Annotations */

function truncate(str) {
    const words = str.split(" ");
    const limit = 20;
    if (words.length <= limit) {
        return words.join(" ");
    }

    return words.splice(0, 20).join(" ") + '...';
}


function fetchAndShowAnnotations() {
    $.ajax({
        method: "GET",
        url: apiUrl("annotation/" + PDF.id + "/search")
    }).done(function(data) {
        var str = '<ul class="annotation-list">';
        data.rows.sort((a, b) => a.page - b.page).forEach(function(v) {
            str += '<li id="annotation-' + v.id + '" >' +
                '<div class="item" data-type="sidebar" data-page="' + v.page + '" data-id="' + v.id + '" data-annotation="' + v.id + '">' +
                '<p class="text">' +
                truncate(v.text) +
                ' <strong class="page">(page ' + v.page + ')</strong>' +
                '</p>' +
                '</div>' +
                '<img src="/script/images/trash.svg" class="annotation-remove" data-id="' + v.id + '"   />' +
                '</li>'
        });
        str += '</ul>';

        if (data.total < 1) {
            str = "<div class='no-result'>Annotations not add yet.</div>";
        }
        $('#annotationViewer .count').text('(' + data.rows.length + ')');
        $('#annotationViewer #annotationView').html(str);
    }).fail(function() {
        alert("Error while loading annotations.");
    }).always(function() {});
}

function loadSideBarAnnotation() {
    $('#annotationToggle').on('click', function() {
        if (PDFViewerApplication.pdfSidebar.isOpen && PDFViewerApplication.pdfSidebar.type === 'annotation') {
            PDFViewerApplication.pdfSidebar.type = null;
            PDFViewerApplication.pdfSidebar.close();
            return;
        }
        showAnnotationSidebar()
    });

    $('#thunbnailToggle').on('click', function(e) {
        if (PDFViewerApplication.pdfSidebar.isOpen && PDFViewerApplication.pdfSidebar.type === 'thumbnail') {
            PDFViewerApplication.pdfSidebar.type = null;
            PDFViewerApplication.pdfSidebar.close();
            return;
        }

        showThumbnailSidebar();
    });

    document.addEventListener('documentinit', function() {
        PDFViewerApplication.store.getMultiple({ sidebarView: -1, sidebarViewType: null }).then(cache => {
            if (!cache.sidebarView) return;
            if (cache.sidebarViewType === 'annotation') {
                showAnnotationSidebar();
            } else {
                showThumbnailSidebar();
            }
        })
    }, true);
}

function showAnnotationSidebar() {
    PDFViewerApplication.pdfSidebar.open();
    $(this).addClass('toggled');
    $('#thunbnailToggle').removeClass('toggled');
    $('#thumbnailViewer').hide();
    $('#annotationViewer').show();
    PDFViewerApplication.pdfSidebar.type = 'annotation';
    PDFViewerApplication.store.set('sidebarView', 1);
    PDFViewerApplication.store.set('sidebarViewType', 'annotation');

    fetchAndShowAnnotations();

    $(document).on('click', '.annotation-remove', function(e) {
        e.preventDefault();
        const id = $(this).data('id');
        if (confirm('Are you sure, you want to delete this annotation?')) {
            $('.annotator-hl').each(function() {
                const annotation = $(this).data('annotation');
                if (annotation.id === id) {
                    deleteAnnotationsByIds([id], function() {
                        createdList = createdList.filter(a => a.id != annotation.id);
                    });
                }
            })
        }
    });
}

function showThumbnailSidebar() {
    PDFViewerApplication.pdfSidebar.open();
    $(this).addClass('toggled');
    $('#annotationToggle').removeClass('toggled');
    $('#thumbnailViewer').show();
    $('#annotationViewer').hide();
    PDFViewerApplication.pdfSidebar.type = 'thumbnail';
    PDFViewerApplication.store.set('sidebarViewType', 'thumbnail');
}

function updateMode() {
    // update mode
    $('body').addClass('mode_' + MODE);
    $('.mode-' + MODE).addClass('active');



    // toggle mode
    $('.toggleMode').on('click', function(e) {
        e.preventDefault();
        if ($(this).data('mode') === 'text') {
            MODE = 'text';
            $(this).addClass('active');
            $('.mode-shape').removeClass('active');
            $('.page').find('.annotator-wrapper').boxer('destroy');
            $('body').removeClass('mode_shape').addClass('mode_text');
        } else {
            MODE = 'shape';
            $('.mode-shape').removeClass('active');
            $('.mode-text').removeClass('active');
            $(this).addClass('active');
            $('.page').find('.annotator-wrapper').boxer({ disabled: false, shape: $(this).data('mode') });
            $('body').removeClass('mode_text').addClass('mode_shape');
        }
        $('body').data('shape', $(this).data('mode'));
    });
}


$(document).on('ready', function() {
    // enable annotation list on sidebar
    loadSideBarAnnotation();
    // load stamp list
    loadStampList();
    loadStampListText();

    // when click on delete annotation
    Annotator.Viewer.prototype.onDeleteClick = function(event) {
        if (confirm('Do you want to delete this annotation along with comments?')) {
            return this.onButtonClick(event, "delete")
        }
    };

    // toggle show stamp list
    $('.botton-stamp').on('click', function() {
        $('.stamp-collection').toggle();
    });

    // toggle show stamp_text list
    $('.botton-stamp-text').on('click', function() {
        $('.stamp-collection-text').toggle();
    })

    // update worker url and pdf url
    document.addEventListener('load', function() {
        PDFViewerApplicationOptions.set('workerSrc', WORKER_URL);
        PDFViewerApplicationOptions.set('defaultUrl', PDF.url);
    }, true);

    // update page title
    document.addEventListener('documentloaded', function(params) {
        PDFViewerApplication.setTitle(PAGE_TITLE);
    });






    // delete all annotations
    $('.deleteAnnotations').on('click', function() {
        if (confirm('Do you want to remove all annotations?')) {
            $.ajax({
                method: "DELETE",
                url: baseurl + "/annotation/" + PDF.id + "/deleteAll",
            }).done(function() {
                $('.annotator-pdf-hl').remove();
                $('.annotator-hl').each(function() {
                    $(this).replaceWith($(this).text());
                })
            });
        }
    })

    // show annotations search result when click on input when result is present
    $('#annotationFindInput').on('click', function() {
        if ($('.annotationsearchList').find('li').length) {
            $('.annotationsearchList').show();
        } else {
            $('.annotationsearchList').hide();
        }
    });

    // search annotations
    $('#annotationFindInput').on('input', debounce(onAnnotationSearch, 500));

    function onAnnotationSearch() {
        var $this = $('#annotationFindInput')
        var q = $this.val().trim();
        if (q) {
            $this.attr('data-status', 'pending');
            $.ajax({
                method: "GET",
                url: baseurl + "/annotation/" + PDF.id + "/search?q=" + q
            }).done(function(data) {
                var str = '<ul class="annotation-list">';
                var foundIds = [];
                data.rows.forEach(function(v) {
                    str += '<li class="item" data-page="' + v.page + '" data-id="' + v.id + '" data-annotation="' + v.id + '" ><span>' + v.page + '</span>' + v.text + '</li>'
                });
                str += '</ul>';

                if (data.total < 1) {
                    str = "<div class='no-result'>Annotations not found</div>";
                }

                $('.annotationsearchList').show().html(str);
            }).fail(function() {
                alert("Error while annotation search.");
            }).always(function() {
                $this.attr('data-status', '');
            });

        } else {
            $this.attr('data-status', '');
            $('.annotationsearchList').hide();
        }
    }

    // when click on other than annoation list then hide the list
    $(document).mouseup(function(e) {
        var container = $('.annotationsearchList');
        if (!container.is(e.target) && container.has(e.target).length === 0) {
            container.hide();
        }
    });

    // when click on annotation show annotation pop
    $(document).on('click', '.annotation-list .item', function() {
        $('.annotator-viewer').addClass('annotator-hide');
        $('.annotationsearchList').hide();
        var id = $(this).data('id');
        var page = $(this).data('page');
        const content = $('#viewer').find('.page:nth-child(' + page + ')');
        var found = false;
        if (content.find('.canvasWrapper').length) {
            content.find('.annotator-hl').each(function(i, a) {
                var a = $(this);
                var annotation = a.data('annotation');
                if (!found && annotation.id == id) {
                    found = true;
                    if (PDFViewerApplication.page !== page) {
                        PDFViewerApplication.pdfViewer.currentPageNumber = page;
                    }
                    setTimeout(() => {
                        var el = annotation.highlights ? $(annotation.highlights) : $('.annotator-' + annotation.id);
                        var position = el.offset();
                        var top = position.top + $('#viewerContainer').scrollTop();
                        var left = position.left - content.offset().left;
                        $('#viewerContainer').animate({
                            scrollTop: top - 200
                        }, '500');
                        position.top = position.top - content.offset().top;
                        position.left = left + (el.width() / 2);
                        content.data('annotator').showViewer([annotation], position);
                    }, 100);
                }
            });
        }

        if (!found) {
            highlightAnnotation = { id, page };
            PDFViewerApplication.pdfViewer.currentPageNumber = page;
        }
    });

    //update mode
    updateMode();

    // load annotations on pdf
    loadAnnotations();

    // delete all annotations
    deleteAnnotations();

    // enable annotation search 
    annotationSearch();
});