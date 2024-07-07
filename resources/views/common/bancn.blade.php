<!DOCTYPE html>
<html>
<head>
<title>    {!! dujiaoka_config_get('rjtitle') !!}</title>
<meta charset="UTF-8" />
<meta http-equiv="X-UA-Compatible" content="IE=edge" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<meta http-equiv="Content-Security-Policy" content="upgrade-insecure-requests" />

<link rel="stylesheet" href="/assets/unicorn/css/bootstrap.min.css" />

</head>
 <style> .container { padding-top: 100px; }
h1 {
color: #333;
font-size: 36px;
font-weight: bold;
margin-bottom: 30px;
}

p {
font-size: 18px;
margin-bottom: 10px;
}

.emphasis {
font-weight: bold;
color: #555;
}
</style>

</head> <body> <div class="container text-center"> <h1>Sorry!</h1> <div style="padding-top: 30px;"></div> <p>Your IP</p  <p><em class="emphasis"> {{ $clientip }}</em> from <em class="emphasis">{{ $country }}</em></p>
    
        <p>    {!! dujiaoka_config_get('cntitle') !!}</p>
    </div>
</body>

</html>