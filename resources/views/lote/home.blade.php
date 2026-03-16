@php
    function asset_path($path):string
    {
        return url('lote/'.$path);
    }
@endphp
    <!DOCTYPE html>
<html lang="en" class="no-js">
<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="google-site-verification" content="KsdKVHm-pG0Stvau4JUmEhW3n0kHth_FH_lKWpPMqrM" />
    <title>LOTE - Web design studio</title>
    <meta property="og:title" content="LOTE - Web design studio" />
    <meta property="og:image" content="http://lote.bg/img/lote_social.jpg" />
    <meta property="og:description" content="Web design studio, developing custom web sites. I have more than a decade of experience helping clients in the non profit, government and progressive business sectors to communicate better online." />
    <link rel="shortcut icon" type="image/x-icon" href="{{asset_path('favicon.png')}}">
    <link rel="stylesheet" type="text/css" href="{{asset_path('css/app.css')}}" />
</head>
<body>
<div id="container" class="container">
    <header class="intro">
        <!-- <img class="intro__image" src="img/airplane.jpg" alt="LOTE"/> -->
        <div class="intro__image"></div>
        <div class="intro__content">
            <img src="{{asset_path('img/logo_w.svg')}}" alt="" width="220px">
            <div class="intro__subtitle">
                <div class="intro__description">
                    Living On The Edge studio<br>
                    <span>Design and development.</span>
                </div>
                <button class="trigger">
                    <svg width="100%" height="100%" viewBox="0 0 60 60" preserveAspectRatio="none">
                        <g class="icon icon--grid">
                            <rect x="32.5" y="5.5" width="22" height="22"/>
                            <rect x="4.5" y="5.5" width="22" height="22"/>
                            <rect x="32.5" y="33.5" width="22" height="22"/>
                            <rect x="4.5" y="33.5" width="22" height="22"/>
                        </g>
                        <g class="icon icon--cross">
                            <line x1="4.5" y1="55.5" x2="54.953" y2="5.046"/>
                            <line x1="54.953" y1="55.5" x2="4.5" y2="5.047"/>
                        </g>
                    </svg>
                    <span>View content</span>
                </button>
            </div>
        </div><!-- /intro__content -->
    </header><!-- /intro -->
</div>
</body>
</html>
