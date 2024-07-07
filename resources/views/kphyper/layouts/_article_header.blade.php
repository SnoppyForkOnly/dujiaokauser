<head>
    <meta charset="utf-8" />
    @if (!empty($seo) && $seo['title'])
    <title>{{ isset($seo['title']) ? $seo['title'] : '' }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="Keywords" content="{{ isset($seo['keywords']) ? $seo['keywords'] : '' }}">
    <meta name="Description" content="{{ isset($seo['description']) ? $seo['description'] : '' }}">
    @else
    <title>{{ dujiaoka_config_get('title') }}</title>
    @endif

    @if(\request()->getScheme() == "https")
    <meta http-equiv="Content-Security-Policy" content="upgrade-insecure-requests">
    @endif
    <link rel="shortcut icon" href="/favicon.ico">
    <link href="/assets/hyper/css/vendor/jquery-jvectormap-1.2.2.css" rel="stylesheet" type="text/css">
    <link href="/assets/hyper/css/icons.min.css" rel="stylesheet" type="text/css">
    <link href="/assets/hyper/css/app-creative.min.css" rel="stylesheet" type="text/css" id="light-style">
    <link href="/assets/hyper/css/hyper.css?v=045256" rel="stylesheet" type="text/css">
</head>
