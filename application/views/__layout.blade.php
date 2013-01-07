<?php
    header('Content-Type: text/html; charset=UTF-8');
?>
<!doctype html>
<html>
<head>
    <meta charset="UTF-8">

    <title>STS App DB</title>

    <link rel="stylesheet" type="text/css" href="{{ asset('css/style.css') }}">
</head>
<body>
    <div id="wrap">
        <div id="header">
            {{ $header }}
        </div>
        <div id="content">
            {{ $content }}
        </div>
        <div id="footer">
            {{ $footer }}
        </div>
    </div>

    <script type="text/javascript">
    (function() {
        var langForm   = document.getElementById('form-lang');
        var langSelect = document.getElementById('form-lang-select');

        langSelect.onchange = function() {
            langForm.submit();
        };
    }());
    </script>
</body>
</html>