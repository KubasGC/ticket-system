<!DOCTYPE html>
<html lang="pl" >
    <head>
        <meta charset="UTF-8">
        <title>{{ $err }}</title>
        <link rel='stylesheet' href='https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.7/css/bootstrap.min.css'>
        <link rel="stylesheet" href="/errors/css/style.css">
    </head>
    <body>
    <div class="error-page">
        <div>
            <h1 data-h1="{{ $errno }}">{{ $errno }}</h1>
            <p data-p="{{ $err }}">{{ $err }}</p>
        </div>
    </div>
    <div id="particles-js"></div>
    <script src='https://cdnjs.cloudflare.com/ajax/libs/particles.js/2.0.0/particles.min.js'></script>
    <script  src="/errors/js/index.js"></script>
    </body>

</html>
