<!DOCTYPE html>
<html dir="ltr" mozdisallowselectionprint>

<head>
  <meta charset="utf-8">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
  <meta name="google" content="notranslate">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title>PDF Annotation</title>
  <!-- Latest compiled and minified CSS -->
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.0/css/bootstrap.min.css">
  <link rel="resource" type="application/l10n" href="{{url('script/locale/locale.properties')}}"/>
  <link rel="stylesheet" href="{{url('script/css/viewer.css')}}"/>
  <link rel="stylesheet" href="{{url('script/css/jquery.minicolors.css')}}"/>
  <link rel="stylesheet" href="{{url('script/css/main.css')}}"/>
  <link rel="stylesheet" href="{{url('script/css/jquery-ui.css')}}"/>
  <link rel="stylesheet" href="{{url('script/css/spectrum.css')}}"/>
  <link rel="stylesheet" href="{{url('script/css/annotator.min.css')}}"/>

  <script src="{{url('script/js/pdf.js')}}"></script>
  <script src="{{url('script/js/jquery.min.js')}}"></script>
  <!-- Latest compiled JavaScript -->
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.0/js/bootstrap.min.js"></script>
  <script src="{{url('script/js/annotator.min.js')}}"></script>
  <script src="{{url('script/js/annotator.store.min.js')}}"></script>
  <script src="{{url('script/js/moment.min.js')}}"></script>
  <script src="{{url('script/js/jquery-ui.min.js')}}"></script>
  <script src="{{url('script/js/jbox.js')}}"></script>
  <script src="{{url('script/js/annotator.shape.js')}}"></script>
  <script src="{{url('script/js/annotator.properties.js')}}"></script>
  <script src="{{url('script/js/viewer.js')}}"></script>
  <script src="{{url('script/js/spectrum.js')}}"></script>
  <script>
    var USER = {!!json_encode(Auth::user())!!};
    var MODE = 'text';
    var PDF = {!!json_encode($pdf)!!};
    var WORKER_URL = '{{url("script/js/pdf.worker.js")}}';
    var PAGE_TITLE = 'PDF annotations';
    var STAMPS = {!!json_encode($stamps)!!}
  </script>
  <script src="{{url('script/js/main.js')}}"></script>
</head>
  <body tabindex="1" class="loadingInProgress">
    <div class="container container-annotation" >
      <div class="row" >
            <div class="col-sm-2" style="background:#ccc; height: 100%; padding:0px;">
                <!-- left menu goes here  this menu contains the details about the pdf and the particular documents like filesize, filename, depatment, document no, document name etc -->
            </div>
            <div class="col-sm-10" style="height: 100%; position: relative; padding:0px;">
                @include('annotation.viewer')
            </div>
     </div>
    </div>
  </body>
</html>
