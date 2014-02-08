<?php

require  __DIR__ . "/lib/libs.php";


function getRss($url, $idx=0){
    $arr = xml2array($url);
    if(empty($arr) || !is_array($arr) || !isset($arr['rss']) || !isset($arr['rss']['channel']) )
        return FALSE;

    $channel = $arr['rss']['channel'];
    $channelAttr = [
        'title' => (isset($channel['title'])) ? $channel['title'] : $url,
        'link' => (isset($channel['link'])) ? $channel['link'] : $url,
    ];
    $channelAttr['image'] = (isset($channel['image'])  && isset($channel['image']['url'])) ? $channel['image']['url']
        : $channelAttr['link']. '/favicon.ico';

    $result = [];
    if(empty($channel['item']) || !is_array($channel['item']) )
        return false;
    foreach($channel['item'] as $itm ){
        if(!is_array($itm)) return false;
        $itm['channel'] = $channelAttr;
        $pubDate = floatval(rsstotime($itm['pubDate']) . '.' . $idx);
        $result[$pubDate] = $itm;
    }
    return $result;
}


function combineRss($sources, &$err){
    $result = []; $err = [];
    foreach($sources as $idx => $url){
        $rss = getRss($url,$idx);
        if(!$rss){
            $err[] = "Error getting feed from $url";
            continue;
        }
        $result = $result + $rss;
    }
    ksort($result);
    return $result;
}


function sentError($err,$msg,$code=400){
    header($err,true, $code);
    echo(json_encode($msg));
    exit();
}




$method = $_SERVER['REQUEST_METHOD'];

//header('Content-type: application/json');
if($method=='GET'){
    $sources = isset($_GET['sources']) ? $_GET['sources'] : null;
    if(empty($sources) || !is_array($sources)){
        sentError("Bad request", "Link sumber rss kosong");
    }
    $result = combineRss($sources,$err);
    echo(json_encode([
        'data' => $result,
        'error' => (!empty($err)) ? $err : null
    ]));
}
else{
    sentError("Method Not Allowed", "Metode $method tidak tersedia pada WEB Service ini.", 405);
}



