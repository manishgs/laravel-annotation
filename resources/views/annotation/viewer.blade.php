<?php
$user_permission = Auth::user()->user_permission;
?>
@if(stristr($user_permission,"download"))
<script>
$(function(){
    $('#download').show();
});
</script>
@endif
@if(stristr($user_permission,"print"))
<script>
$(function(){
    $('#print').show();
});
</script>
@endif
<!--Sweet alert-->
  {!! Html::style('css/sweetalert2.min.css') !!}
  {!! Html::script('js/sweetalert2.min.js') !!}

<style type="text/css">
  .swal2-modal .swal2-title{
    font-size: 18px;
  }
  .swal2-modal .swal2-styled{
    font-size: 14px;
  }
  .toolbarButton{
    cursor: pointer;
  }
  .swal2-content{
    font-size: 14px !important;
  }
  .txtbox-control{
    width: 50%;
    height: 20px;
    padding: 4px 12px;
    font-size: 14px;
  }
  .hint{
    font-size: 10px;
    color: #999;
  }
  .complsry{
    color: red;
  }
</style>
<div id="outerContainer">
      <div id="sidebarContainer">


       <div id="annotationViewer">
                   <div id="toolbarSidebar">
                      <div class="splitToolbarButton">
                       Annotations <span class="count"></span>
                      </div>
                    </div>
                    <div id="annotationView">
                    </div>
        </div><!--annotationViewer-->
        <div id="thumbnailViewer">
          <div id="toolbarSidebar">
          <div class="splitToolbarButton toggled">
            <button id="pdf-bookmark" onclick="pop()" class="toolbarButton" style="float:left;" title="Add Bookmark"><img src="{{ asset('images/add-button.png') }}" alt="Logo"> </button>

            <select id="list" class="dropdwn" style="background-color: rgb(58, 58, 58); color: white;margin-left: 5px;margin-top: 2px;height: 22px; float: left; width:60%; font-size: 12px;" title="Select Bookmarked Pages">
                          <option value="0">Select Bookmark</option>
                            <?php
foreach (@$bookmarklist as $key => $bList) {
    ?>
                                <option value="<?php echo $bList->document_bookmark_id; ?>"><?php echo $bList->document_bookmark; ?></option>
                                <?php
}
?>
                        </select>
          <button id="pdf-bookmark-dlte" onclick="deleteBookmrk()" class="toolbarButton" title="Delete Bookmark"><img src="{{ asset('images/delete-button.png') }}" alt="Logo"> </button>

          </div>
        </div>
                    <div id="toolbarSidebar">
                      <div class="splitToolbarButton toggled">
                        <button id="viewThumbnail" class="toolbarButton toggled" title="Show Thumbnails" tabindex="2"
                          data-l10n-id="thumbs">
                          <span data-l10n-id="thumbs_label">Thumbnails</span>
                        </button>
                        <button id="viewOutline" class="toolbarButton"
                          title="Show Document Outline (double-click to expand/collapse all items)" tabindex="3"
                          data-l10n-id="document_outline">
                          <span data-l10n-id="document_outline_label">Document Outline</span>
                        </button>
                        <button id="viewAttachments" class="toolbarButton" title="Show Attachments" tabindex="4"
                          data-l10n-id="attachments">
                          <span data-l10n-id="attachments_label">Attachments</span>
                        </button>
                      </div>
                    </div>
                    <div id="sidebarContent">
                      <div id="thumbnailView">
                      </div>
                      <div id="outlineView" class="hidden">
                      </div>
                      <div id="attachmentsView" class="hidden">
                      </div>
                    </div>
                    <div id="sidebarResizer" class="hidden"></div>
                </div>
              </div>

      <div id="mainContainer">
        <div class="findbar hidden doorHanger" id="findbar">
          <div id="findbarInputContainer">
            <input id="findInput" class="toolbarField" title="Find" placeholder="Find in document…" tabindex="91"
              data-l10n-id="find_input">
            <div class="splitToolbarButton">
              <button id="findPrevious" class="toolbarButton findPrevious"
                title="Find the previous occurrence of the phrase" tabindex="92" data-l10n-id="find_previous">
                <span data-l10n-id="find_previous_label">Previous</span>
              </button>
              <button id="findNext" class="toolbarButton findNext" title="Find the next occurrence of the phrase"
                tabindex="93" data-l10n-id="find_next">
                <span data-l10n-id="find_next_label">Next</span>
              </button>
            </div>
          </div>

          <div id="findbarOptionsOneContainer">
            <input type="checkbox" id="findHighlightAll" class="toolbarField" tabindex="94">
            <label for="findHighlightAll" class="toolbarLabel" data-l10n-id="find_highlight">Highlight all</label>
            <input type="checkbox" id="findMatchCase" class="toolbarField" tabindex="95">
            <label for="findMatchCase" class="toolbarLabel" data-l10n-id="find_match_case_label">Match case</label>
          </div>
          <div id="findbarOptionsTwoContainer">
            <input type="checkbox" id="findEntireWord" class="toolbarField" tabindex="96">
            <label for="findEntireWord" class="toolbarLabel" data-l10n-id="find_entire_word_label">Whole words</label>
            <span id="findResultsCount" class="toolbarLabel hidden"></span>
          </div>

          <div id="findbarMessageContainer">
            <span id="findMsg" class="toolbarLabel"></span>
          </div>
        </div> <!-- findbar -->

        <div id="secondaryToolbar" class="secondaryToolbar hidden doorHangerRight">
          <div id="secondaryToolbarButtonContainer">

            <button id="secondaryPresentationMode" class="secondaryToolbarButton presentationMode visibleLargeView"
              title="Switch to Presentation Mode" tabindex="51" data-l10n-id="presentation_mode">
              <span data-l10n-id="presentation_mode_label">Presentation Mode</span>
            </button>

            <button id="secondaryPrint" class="secondaryToolbarButton print visibleMediumView" title="Print" tabindex="53"
              data-l10n-id="print">
              <span data-l10n-id="print_label">Print</span>
            </button>

            <button id="secondaryDownload" class="secondaryToolbarButton download visibleMediumView" title="Download"
              tabindex="54" data-l10n-id="download">
              <span data-l10n-id="download_label">Download</span>
            </button>

            <a href="#" id="secondaryViewBookmark" class="secondaryToolbarButton bookmark visibleSmallView"
              title="Current view (copy or open in new window)" tabindex="55" data-l10n-id="bookmark">
              <span data-l10n-id="bookmark_label">Current View</span>
            </a>

            <div class="horizontalToolbarSeparator visibleLargeView"></div>

            <button id="firstPage" class="secondaryToolbarButton firstPage" title="Go to First Page" tabindex="56"
              data-l10n-id="first_page">
              <span data-l10n-id="first_page_label">Go to First Page</span>
            </button>
            <button id="lastPage" class="secondaryToolbarButton lastPage" title="Go to Last Page" tabindex="57"
              data-l10n-id="last_page">
              <span data-l10n-id="last_page_label">Go to Last Page</span>
            </button>

            <div class="horizontalToolbarSeparator"></div>

            <button id="pageRotateCw" class="secondaryToolbarButton rotateCw" title="Rotate Clockwise" tabindex="58"
              data-l10n-id="page_rotate_cw">
              <span data-l10n-id="page_rotate_cw_label">Rotate Clockwise</span>
            </button>
            <button id="pageRotateCcw" class="secondaryToolbarButton rotateCcw" title="Rotate Counterclockwise"
              tabindex="59" data-l10n-id="page_rotate_ccw">
              <span data-l10n-id="page_rotate_ccw_label">Rotate Counterclockwise</span>
            </button>

            <div class="horizontalToolbarSeparator"></div>

            <button id="cursorSelectTool" class="secondaryToolbarButton selectTool toggled"
              title="Enable Text Selection Tool" tabindex="60" data-l10n-id="cursor_text_select_tool">
              <span data-l10n-id="cursor_text_select_tool_label">Text Selection Tool</span>
            </button>
            <button id="cursorHandTool" class="secondaryToolbarButton handTool" title="Enable Hand Tool" tabindex="61"
              data-l10n-id="cursor_hand_tool">
              <span data-l10n-id="cursor_hand_tool_label">Hand Tool</span>
            </button>

            <div class="horizontalToolbarSeparator"></div>

            <button id="scrollVertical" class="secondaryToolbarButton scrollModeButtons scrollVertical toggled"
              title="Use Vertical Scrolling" tabindex="62" data-l10n-id="scroll_vertical">
              <span data-l10n-id="scroll_vertical_label">Vertical Scrolling</span>
            </button>
            <button id="scrollHorizontal" class="secondaryToolbarButton scrollModeButtons scrollHorizontal"
              title="Use Horizontal Scrolling" tabindex="63" data-l10n-id="scroll_horizontal">
              <span data-l10n-id="scroll_horizontal_label">Horizontal Scrolling</span>
            </button>
            <button id="scrollWrapped" class="secondaryToolbarButton scrollModeButtons scrollWrapped"
              title="Use Wrapped Scrolling" tabindex="64" data-l10n-id="scroll_wrapped">
              <span data-l10n-id="scroll_wrapped_label">Wrapped Scrolling</span>
            </button>

            <div class="horizontalToolbarSeparator scrollModeButtons"></div>

            <button id="spreadNone" class="secondaryToolbarButton spreadModeButtons spreadNone toggled"
              title="Do not join page spreads" tabindex="65" data-l10n-id="spread_none">
              <span data-l10n-id="spread_none_label">No Spreads</span>
            </button>
            <button id="spreadOdd" class="secondaryToolbarButton spreadModeButtons spreadOdd"
              title="Join page spreads starting with odd-numbered pages" tabindex="66" data-l10n-id="spread_odd">
              <span data-l10n-id="spread_odd_label">Odd Spreads</span>
            </button>
            <button id="spreadEven" class="secondaryToolbarButton spreadModeButtons spreadEven"
              title="Join page spreads starting with even-numbered pages" tabindex="67" data-l10n-id="spread_even">
              <span data-l10n-id="spread_even_label">Even Spreads</span>
            </button>

            <div class="horizontalToolbarSeparator spreadModeButtons"></div>

            <button id="documentProperties" class="secondaryToolbarButton documentProperties" title="Document Properties…"
              tabindex="68" data-l10n-id="document_properties">
              <span data-l10n-id="document_properties_label">Document Properties…</span>
            </button>
          </div>
        </div> <!-- secondaryToolbar -->

        <div class="toolbar">
          <div id="toolbarContainer">
            <div id="toolbarViewer">
              <div id="toolbarViewerLeft">
                      <span id="sidebarToggle"></span>
                        <button id="thunbnailToggle" class="toolbarButton" title="Toggle Sidebar" tabindex="11"
                  data-l10n-id="toggle_sidebar">
                  <span data-l10n-id="toggle_sidebar_label">Toggle Sidebar</span>
                        </button>
                        <button id="annotationToggle" class="toolbarButton" title="Toggle Annotation" tabindex="111">
                            <img src="{{url('script/images/annotation.png')}}" style="height: 16px;" />
                </button>
                <button id="viewFind" class="toolbarButton" title="Find in Document" tabindex="12" data-l10n-id="findbar">
                  <span data-l10n-id="findbar_label">Find</span>
                </button>
                <div class="splitToolbarButton hiddenSmallView">
                  <button class="toolbarButton pageUp" title="Previous Page" id="previous" tabindex="13"
                    data-l10n-id="previous">
                    <span data-l10n-id="previous_label">Previous</span>
                  </button>
                  <div class="splitToolbarButtonSeparator"></div>
                  <button class="toolbarButton pageDown" title="Next Page" id="next" tabindex="14" data-l10n-id="next">
                    <span data-l10n-id="next_label">Next</span>
                  </button>
                </div>
                <input type="number" id="pageNumber" class="toolbarField pageNumber" title="Page" value="1" size="4"
                  min="1" tabindex="15" data-l10n-id="page">
                <span id="numPages" class="toolbarLabel"></span>
              </div>
              <div id="toolbarViewerRight">
                <!-- digital signature -->

                  <button title="Digitally Signed" class="toolbarButton digital_sign" style="display: none;">
                    <img src="{{url('script/images/digital-signature.png')}}" style="height: 20px;" />
                  </button>

                <!-- digital signature -->
                <button id="presentationMode" class="toolbarButton presentationMode hiddenLargeView"
                  title="Switch to Presentation Mode" tabindex="31" data-l10n-id="presentation_mode">
                  <span data-l10n-id="presentation_mode_label">Presentation Mode</span>
                </button>

                <button id="print" class="toolbarButton print hiddenMediumView" title="Print" tabindex="33"
                  data-l10n-id="print">
                  <span data-l10n-id="print_label">Print</span>
                </button>

                <button id="download" class="toolbarButton download hiddenMediumView" title="Download" tabindex="34"
                  data-l10n-id="download">
                  <span data-l10n-id="download_label">Download</span>
                </button>
                <a href="#" id="viewBookmark" class="toolbarButton bookmark hiddenSmallView"
                  title="Current view (copy or open in new window)" tabindex="35" data-l10n-id="bookmark">
                  <span data-l10n-id="bookmark_label">Current View</span>
                </a>

                <div class="verticalToolbarSeparator hiddenSmallView"></div>

                <button id="secondaryToolbarToggle" class="toolbarButton" title="Tools" tabindex="36"
                  data-l10n-id="tools">
                  <span data-l10n-id="tools_label">Tools</span>
                </button>
              </div>
              <div id="toolbarViewerMiddle">
                        <input id="annotationFindInput"
                          autocomplete="off"
                          class="toolbarField" title="Find"
                          placeholder="Search Annotations" />
                <div class="findbar annotationsearchList doorHanger" id="findbar">
                </div>
                         <div style="position:relative">
                            <button title="Delete annotation" class="toolbarButton deleteAnnotations">
                  <img src="{{url('script/images/trash.svg')}}" style="height: 12px;" />
                </button>
                            <div class="findbar doorHanger annotation-delete-option" id="findbar"
                              style="display:none; position: absolute; left:0px; min-width: 285px;">
                              <ul class="annotation-option-list">
                                <li data-action="all">All annotations</li>
                                <li data-action="session">Created during this session</li>
                              </ul>
                            </div>
                        </div>
                <button title="Text annotation" data-mode="text" class="toolbarButton toggleMode mode-text">
                  <img src="{{url('script/images/text.svg')}}" style="height: 12px;" />
                </button>
                <div class="splitToolbarButtonSeparator"></div>
                <button title="Draw rectangle" data-mode="rectangle" class="toolbarButton toggleMode mode-shape">
                  <img src="{{url('script/images/rectangle.svg')}}" style="height: 12px;" />
                </button>
                <button title="Draw circle" data-mode="circle" class="toolbarButton toggleMode mode-shape">
                  <img src="{{url('script/images/circle.svg')}}" style="height: 12px;" />
                </button>
                <div style="position:relative">
                          <button title="Insert stamp" class="toolbarButton botton-stamp">
                    <img src="{{url('script/images/stamp.png')}}" style="height: 16px;" />
                  </button>
                  <div class="findbar doorHanger stamp-collection" id="findbar"
                    style="display:none; position: absolute; left:0px; min-width: 285px;">
                    <ul class="stamp-list">
                    </ul>
                  </div>
                </div>
                <div style="position:relative">
                  <button title="Insert stamp text" class="toolbarButton botton-stamp-text">
                    <img src="{{url('script/images/Stationery-Pen-48.png')}}" style="height: 16px;" />
                  </button>
                  <div class="findbar doorHanger stamp-collection-text" id="findbar-text"
                    style="display:none; position: absolute; left:0px; min-width: 285px;">
                    <ul class="stamp-text-list">
                    </ul>
                  </div>
                </div>
                <!-- digital signature -->
                <!-- <div style="position:relative">
                  <button title="Digitally Signed" class="toolbarButton botton-stamp-text">
                    <img src="{{url('script/images/digital-signature.png')}}" style="height: 20px;" />
                  </button>
                </div> -->
                <!-- digital signature -->
                <div class="splitToolbarButton">
                  <button id="zoomOut" class="toolbarButton zoomOut" title="Zoom Out" tabindex="21"
                    data-l10n-id="zoom_out">
                    <span data-l10n-id="zoom_out_label">Zoom Out</span>
                  </button>
                  <div class="splitToolbarButtonSeparator"></div>
                  <button id="zoomIn" class="toolbarButton zoomIn" title="Zoom In" tabindex="22" data-l10n-id="zoom_in">
                    <span data-l10n-id="zoom_in_label">Zoom In</span>
                  </button>
                </div>
                <span id="scaleSelectContainer" class="dropdownToolbarButton">
                  <select id="scaleSelect" title="Zoom" tabindex="23" data-l10n-id="zoom">
                    <option id="pageAutoOption" title="" value="auto" selected="selected" data-l10n-id="page_scale_auto">
                      Automatic Zoom</option>
                    <option id="pageActualOption" title="" value="page-actual" data-l10n-id="page_scale_actual">Actual
                      Size</option>
                    <option id="pageFitOption" title="" value="page-fit" data-l10n-id="page_scale_fit">Page Fit</option>
                    <option id="pageWidthOption" title="" value="page-width" data-l10n-id="page_scale_width">Page Width
                    </option>
                    <option id="customScaleOption" title="" value="custom" disabled="disabled" hidden="true"></option>
                    <option title="" value="0.5" data-l10n-id="page_scale_percent" data-l10n-args='{ "scale": 50 }'>50%
                    </option>
                    <option title="" value="0.75" data-l10n-id="page_scale_percent" data-l10n-args='{ "scale": 75 }'>75%
                    </option>
                    <option title="" value="1" data-l10n-id="page_scale_percent" data-l10n-args='{ "scale": 100 }'>100%
                    </option>
                    <option title="" value="1.25" data-l10n-id="page_scale_percent" data-l10n-args='{ "scale": 125 }'>125%
                    </option>
                    <option title="" value="1.5" data-l10n-id="page_scale_percent" data-l10n-args='{ "scale": 150 }'>150%
                    </option>
                    <option title="" value="2" data-l10n-id="page_scale_percent" data-l10n-args='{ "scale": 200 }'>200%
                    </option>
                    <option title="" value="3" data-l10n-id="page_scale_percent" data-l10n-args='{ "scale": 300 }'>300%
                    </option>
                    <option title="" value="4" data-l10n-id="page_scale_percent" data-l10n-args='{ "scale": 400 }'>400%
                    </option>
                  </select>
                </span>
              </div>
            </div>
            <div id="loadingBar">
              <div class="progress">
                <div class="glimmer">
                </div>
              </div>
            </div>
          </div>
        </div>

        <menu type="context" id="viewerContextMenu">
          <menuitem id="contextFirstPage" label="First Page" data-l10n-id="first_page">
          </menuitem>
          <menuitem id="contextLastPage" label="Last Page" data-l10n-id="last_page">
          </menuitem>
          <menuitem id="contextPageRotateCw" label="Rotate Clockwise" data-l10n-id="page_rotate_cw">
          </menuitem>
          <menuitem id="contextPageRotateCcw" label="Rotate Counter-Clockwise" data-l10n-id="page_rotate_ccw">
          </menuitem>
        </menu>

        <div id="viewerContainer" tabindex="0">
          <div id="viewer" class="pdfViewer"></div>
        </div>
        <input type="hidden" name="docid" id="docid" value="{{$docid}}">
        <input type="hidden" name="pageno" id="pageno" value="0">

        <div id="errorWrapper" hidden='true'>
          <div id="errorMessageLeft">
            <span id="errorMessage"></span>
            <button id="errorShowMore" data-l10n-id="error_more_info">
              More Information
            </button>
            <button id="errorShowLess" data-l10n-id="error_less_info" hidden='true'>
              Less Information
            </button>
          </div>
          <div id="errorMessageRight">
            <button id="errorClose" data-l10n-id="error_close">
              Close
            </button>
          </div>
          <div class="clearBoth"></div>
          <textarea id="errorMoreInfo" hidden='true' readonly="readonly"></textarea>
        </div>
      </div> <!-- mainContainer -->

      <div id="overlayContainer" class="hidden">
        <div id="passwordOverlay" class="container hidden">
          <div class="dialog">
            <div class="row">
              <p id="passwordText" data-l10n-id="password_label">Enter the password to open this PDF file:</p>
            </div>
            <div class="row">
              <input type="password" id="password" class="toolbarField">
            </div>
            <div class="buttonRow">
              <button id="passwordCancel" class="overlayButton"><span
                  data-l10n-id="password_cancel">Cancel</span></button>
              <button id="passwordSubmit" class="overlayButton"><span data-l10n-id="password_ok">OK</span></button>
            </div>
          </div>
        </div>
        <div id="documentPropertiesOverlay" class="container hidden">
          <div class="dialog">
            <div class="row">
              <span data-l10n-id="document_properties_file_name">File name:</span>
              <p id="fileNameField">-</p>
            </div>
            <div class="row">
              <span data-l10n-id="document_properties_file_size">File size:</span>
              <p id="fileSizeField">-</p>
            </div>
            <div class="separator"></div>
            <div class="row">
              <span data-l10n-id="document_properties_title">Title:</span>
              <p id="titleField">-</p>
            </div>
            <div class="row">
              <span data-l10n-id="document_properties_author">Author:</span>
              <p id="authorField">-</p>
            </div>
            <div class="row">
              <span data-l10n-id="document_properties_subject">Subject:</span>
              <p id="subjectField">-</p>
            </div>
            <div class="row">
              <span data-l10n-id="document_properties_keywords">Keywords:</span>
              <p id="keywordsField">-</p>
            </div>
            <div class="row">
              <span data-l10n-id="document_properties_creation_date">Creation Date:</span>
              <p id="creationDateField">-</p>
            </div>
            <div class="row">
              <span data-l10n-id="document_properties_modification_date">Modification Date:</span>
              <p id="modificationDateField">-</p>
            </div>
            <div class="row">
              <span data-l10n-id="document_properties_creator">Creator:</span>
              <p id="creatorField">-</p>
            </div>
            <div class="separator"></div>
            <div class="row">
              <span data-l10n-id="document_properties_producer">PDF Producer:</span>
              <p id="producerField">-</p>
            </div>
            <div class="row">
              <span data-l10n-id="document_properties_version">PDF Version:</span>
              <p id="versionField">-</p>
            </div>
            <div class="row">
              <span data-l10n-id="document_properties_page_count">Page Count:</span>
              <p id="pageCountField">-</p>
            </div>
            <div class="row">
              <span data-l10n-id="document_properties_page_size">Page Size:</span>
              <p id="pageSizeField">-</p>
            </div>
            <div class="separator"></div>
            <div class="row">
              <span data-l10n-id="document_properties_linearized">Fast Web View:</span>
              <p id="linearizedField">-</p>
            </div>
            <div class="buttonRow">
              <button id="documentPropertiesClose" class="overlayButton"><span
                  data-l10n-id="document_properties_close">Close</span></button>
            </div>
          </div>
        </div>
        <div id="printServiceOverlay" class="container hidden">
          <div class="dialog">
            <div class="row">
              <span data-l10n-id="print_progress_message">Preparing document for printing…</span>
            </div>
            <div class="row">
              <progress value="0" max="100"></progress>
              <span data-l10n-id="print_progress_percent" data-l10n-args='{ "progress": 0 }'
                class="relative-progress">0%</span>
            </div>
            <div class="buttonRow">
              <button id="printCancel" class="overlayButton"><span
                  data-l10n-id="print_progress_close">Cancel</span></button>
            </div>
          </div>
        </div>
      </div> <!-- overlayContainer -->

</div> <!-- outerContainer -->
<div id="printContainer"></div>
<script type="text/javascript">
  function pop() {
    var pageno = $("#pageno").val();
    console.log('pageno:'+pageno);
    var my_text= null;
    var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
    //my_text = prompt("Enter bookmarking text:", "Text");

  swal({
      title: "Bookmark",
      html:
      '<label name="labalbmark">Please enter bookmark text <span class="complsry">*</span></label><br/>'+
      '<input type="text" class="txtbox-control" name="bookmarktxt" id="bookmarktxt"> <br/><br/>' +
      '<input type="radio" name="pagecat" value="1" checked> Current Page ' +
      '<input type="radio" name="pagecat" value="2"> Pages <br/>' +
      '<input type="text" class="txtbox-control" name="multipages" id="multipages"> <br/>' +
      '<label name="hint" class="hint">Type page numbers and/or page ranges seperated by comma counting from the start of the document or the sectoin. For example, type 1,3,5-12 </label>',

      showCancelButton: true,
      confirmButtonColor: '#DD6B55',
      confirmButtonText: 'Save',
      cancelButtonText: "Cancel",
      closeOnConfirm: false,
      closeOnCancel: false

  }).then(function(isConfirm) {
      if (isConfirm){
        var my_text = $("#bookmarktxt").val();
        if(!my_text){
          swal("Warning!", "Please enter bookmark text", "warning");
        }else{
          var radioValue = $("input[name='pagecat']:checked").val();
          //getting document id
          var docid = $("#docid").val();
          //getting radio button value
          if(radioValue == 1){
          var pageno = $("#pageno").val();
          }else{
            var pageno = $("#multipages").val();
            if(!pageno){
              swal("Warning!", "Please enter page numbers", "warning");
            }else{
               $.ajax({
              type:'get',
              url: '{{URL('saveBookmark')}}',
              data: {_token:CSRF_TOKEN,docmntid:docid,bokmrktxt:my_text,pageno:pageno},
              success: function(data,response){
                if(data!='failed'){
                  $("#list").append(new Option(my_text, data));
                  swal("Success!", "'"+my_text+"' saved successfully.", "success");
                }else{
                  swal("Failed!", "Sorry, something went wrong. bookmark saving failed.", "error");
                }
              }
            });
            }
          }

        }

      } else {
        //swal("Cancelled", "Your imaginary file is safe :)", "error");
        e.preventDefault();
      }

  });


      //alert(pageno);
      // if(my_text == null){
      //  swal("Please enter bookmark text");
      // }else{
      //
      // //}
    }

  function deleteBookmrk() {
    var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
    var selectdname  = $('#list :selected').text();
    var selectdid = $('#list').val();
    if(selectdid>0){
      swal({
        title: "{{trans('language.confirm_delete_single')}}'" + selectdname + "' ?",
        text: "{{trans('language.Swal_not_revert')}}",
        type: "{{trans('language.Swal_warning')}}",
        showCancelButton: true
      }).then(function (result) {
          if(result){
              // Success
          var docid = $("#docid").val();
          $.ajax({
            type:'get',
            url: '{{URL('dleteBookmark')}}',
            data: {_token:CSRF_TOKEN,docmntid:docid,selectdid:selectdid},
            success: function(data,response){
              if(data==1){
                $("#list option[value='"+selectdid+"']").remove();
                swal("Success!", "'"+selectdname+"' deleted successfully.", "success");
              }else{
                swal("Failed!", "Sorry, something went wrong. bookmark deletion failed.", "error");
              }
              }
            });
        }
      });
    }else{
      swal('Please select bookmark');
    }
  }

  function clickCounter() {
    //var temp = window.localStorage.getItem("current_page_no");
    //alert(temp);
    var url = document.URL;
    var fields = url.split('#');
    var name = fields[0];
    //var $select = $('#list');
    //$("#list")

    //$("#list option[value='"+selectdid+"']").remove();
    //alert(name);
  }
  $("#list").change(function() {
    var page = 1;
    event.preventDefault();
    var docid = $("#docid").val();
    var selectdid = $('#list').val();
    var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
    $.ajax({
      type:'get',
      url: '{{URL('getBookmark')}}',
      data: {_token:CSRF_TOKEN,docmntid:docid,selectdid:selectdid},
      success: function(data,response){
        page = data;
        //find commas in string
        var substr = ",";
        $('.thumbnail').removeClass('selected');
        var result = page.indexOf(substr) > -1;
        if(result){ //if there is a comma in page number
          var substr1 = "-";
          var result1 = page.indexOf(substr1) > -1;
          if(result1){ // if there is hyphen in value with or without comma(like 6,9,12-16)
            var arrSplit4 = page.split(',');
            var condcnt2 = 1;
            var incrvalue2 = 1;
            var condcnt5 = 1;
            $.each(arrSplit4, function( index, value ) {
              var result5 = value.indexOf(substr1) > -1;
              if(result5){ // value have commas
                var arrSplit5 = value.split('-');
                var startval = parseInt(arrSplit5[0]);
                var endval = parseInt(arrSplit5[1]);
                for(var m=startval; m<=endval;m++){
                  var x = Number(page);
                  var url = document.URL;
                  var substring = '#';
                  if(url.includes(substring))
                  {
                    var fields = url.split('#');
                    var name = fields[0];
                    if(condcnt5==1){
                      window.location.replace(name+'#page='+startval);
                      $("#pageno").val(startval);
                    }else{
                      $('div[data-page-number = '+startval+']').addClass('selected');
                    }
                  }
                  else
                  {
                    if(condcnt5==1){
                      window.location.replace(url+'#page='+startval);
                      $("#pageno").val(startval);
                    }else{
                      $('div[data-page-number = '+startval+']').addClass('selected');
                    }
                  }
                  startval = startval + 1;
                  condcnt5 = condcnt5 + 1;
                }
              }else{
                var x = Number(page);
                var url = document.URL;
                var substring = '#';
                if(url.includes(substring))
                {
                  var fields = url.split('#');
                  var name = fields[0];
                  if(condcnt5==1){
                    window.location.replace(name+'#page='+value);
                    $("#pageno").val(value);
                  }else{
                    $('div[data-page-number = '+value+']').addClass('selected');
                  }
                }
                else
                {
                  if(condcnt5==1){
                    window.location.replace(url+'#page='+value);
                    $("#pageno").val(value);
                  }else{
                    $('div[data-page-number = '+value+']').addClass('selected');
                  }
                }
                condcnt5 = condcnt5+1;
              }
            });

          }else{
            // if there is only comma (no hyphen like 5,6,9)
            var arrSplit = page.split(',');
            var condcnt = 1;
            var substring = '#';
            var leng  = arrSplit.length;

            $.each(arrSplit, function( index, value ) {
                var x = Number(page);
              var url = document.URL;
              if(url.includes(substring))
              {
                var fields = url.split('#');
                var name = fields[0];
                if(condcnt==1){
                    window.location.replace(name+'#page='+value);
                    $("#pageno").val(value);
                }else{
                  $('div[data-page-number = '+value+']').addClass('selected');
                }
              }
              else
              {
                if(condcnt==1){
                  window.location.replace(url+'#page='+value);
                  $("#pageno").val(value);
                }else{
                  $('div[data-page-number = '+value+']').addClass('selected');
                }
              }
              condcnt = condcnt+1;
            });

          }
        }else{ // if there is no comma in the page number
          var substr3 = "-";
          var result3 = page.indexOf(substr3) > -1;
          if(result3){ // if the page num has hyphen with multiple pages  (no comma or a single value like 10-14/23-24)
            var arrSplit3 = page.split("-");
            //var incrcnt = parseInt(arrSplit3[0]);
            var condcnt1 = 1;
            var startval = parseInt(arrSplit3[0]);
            var incrcnt = startval;
            var endval = parseInt(arrSplit3[1]);
            //$.each(arrSplit3, function( index, value ) {
            for(var n=startval; n<=endval;n++){
              var x = Number(page);
              var url = document.URL;
              var substring = '#';
              if(url.includes(substring))
              {
                var fields = url.split('#');
                var name = fields[0];
                if(condcnt1==1){
                  window.location.replace(name+'#page='+incrcnt);
                  $("#pageno").val(startval);
                }else{
                  $('div[data-page-number = '+incrcnt+']').addClass('selected');
                }
              }
              else
              {
                if(condcnt1==1){
                  window.location.replace(url+'#page='+incrcnt);
                  $("#pageno").val(startval);
                }else{
                  $('div[data-page-number = '+incrcnt+']').addClass('selected');
                }
              }
              condcnt1 = condcnt1+1;
              incrcnt = incrcnt+1;
            }
          }else{ // if the page num has only number (no hyphen and comma)
            var x = Number(page);
            var url = document.URL;
            var substring = '#';
            if(url.includes(substring))
            {
              var fields = url.split('#');
              var name = fields[0];
              window.location.replace(name+'#page='+page);
              $("#pageno").val(page);
            }
            else
            {
              window.location.replace(url+'#page='+page);
              $("#pageno").val(page);
            }
          }
        }
      },
      complete: function (data) {
        }
    });

  });
</script>