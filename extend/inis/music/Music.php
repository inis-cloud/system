<?php

namespace inis\music;

use Metowolf\Meting;
use inis\utils\helper;

/**
 * Class Music
 * @package inis\music
 */
class Music
{

    /************ ↓↓↓↓↓ 如果网易云音乐歌曲获取失效，请将你的 COOKIE 放到这儿 ↓↓↓↓↓ ***************/
    protected $netease_cookie = 'JSESSIONID-WYYY=/WMbywCCRhn\24IgcJk1a3eUzdb3DJ1bxp3\GZZ5e369rkXbE2kZOZ\XWwygcGl1OvhaUePC1E+NlyA8bySYdshhufR82lxb6NiXe/qs7zSXy8dOp\/46dG44KwZqmrYY+4Yx8ojxKWROUFUYwKzqECtrXDe7vjkAvGD9j40MynNijfk:1626594476496; _iuqxldmzr_=32; _ntes_nnid=2e33555be8cffbb81fe9d7f321adfa3e,1626592676552; _ntes_nuid=2e33555be8cffbb81fe9d7f321adfa3e; NMTID=00Olb8aiamUoSXJ-UQNoTZBlLBtjTIAAAF6uHqJww; WM_NI=iZfRcrkduoibFzyybOMK7IYKRhgVnFs2u46XdLb/P2jOlDl8KRQYzn2G1RLddz2VDVzZAQF9SzQ8LWdPuav3ZSjyeMfYszEUYWsTPfYa1QHJfj9IcSwkwfFm0ANStvzkUEQ=; WM_NIKE=9ca17ae2e6ffcda170e2e6eed8b13c949af7b3e75ef89e8ba7d44e878b9a85f8398eea8b87bc6396aeafa7c82af0fea7c3b92aab86aab3e27aa9bcbb90ec6a86a6b79ae1458f95f9aadc4192b8a9b7ed4eb1eebbd5b554a894aed0d33aaa91a4a5ef34898e9fbaf966f299ffb3cf70a8a6feb7c75eba868fb2c53391a69fd9cf5da890bb82f5399a8da69ae847a687bd9bc85f9b89bdb5d36e9bbfffb2cb42a6b6f7aae259b5b5b987c567f49f82b4f77ebab59ba6d437e2a3; WM_TID=BTRKiP55sTxBBUUFAFd7ndrRb4iU+aBB; MUSIC_U=8f1c6cfebda0e73ad8eef8057e091d8875aea51059d7435c52b1eb29c74d088633a649814e309366; __csrf=7cd3a90cad177399f48d81e8c0d699b9; ntes_kaola_ad=1';
    /************ ↑↑↑↑↑ 如果网易云音乐歌曲获取失效，请将你的 COOKIE 放到这儿 ↑↑↑↑↑ ***************/
    /**
     * cookie 获取及使用方法见
     * https://github.com/mengkunsoft/MKOnlineMusicPlayer/wiki/%E7%BD%91%E6%98%93%E4%BA%91%E9%9F%B3%E4%B9%90%E9%97%AE%E9%A2%98
     *
     * 如果还有问题，可以联系主题作者
     **/

    /**
     * 获取歌单信息
     * @param int|null $id
     * @param string $media
     * @param string $type
     * @return array
     */
    function GetInfo($id = null, string $media = 'tencent', string $type = 'song', bool $shuffle = false)
    {
        $api = new Meting($media);
        // global $netease_cookie;
        if ($media == 'netease') $api->cookie($this->netease_cookie);
        
        $info = [];
        
        switch ($type) {
            case 'song':
                
                $datas = $api->format(true)->song($id);
                $datas = json_decode($datas,true);
                $data = $datas[0];
                $cover = json_decode($api->format(true)->pic($data['pic_id']),true)['url'];
                $url = json_decode($api->format(true)->url($data['id']),true)['url'];
                
                // 修复网易云音乐防盗链
                if ($media == 'netease') {
                    $url = str_replace("http://m7c","http://m7",$url);
                    $url = str_replace("http://m8c","http://m8",$url);
                }
                
                // 修复QQ音乐防盗链
                if ($media == "tencent") {
                    $url = str_ireplace("ws.stream.qqmusic.qq.com","dl.stream.qqmusic.qq.com",$url);
                }
                
                $url = str_replace("http://","https://", $url);
                
                $info = [
                    'name' => $data['name'],
                    'url' => $url,
                    'song_id' => $data['id'],
                    'cover' => $cover,
                    'author' => $data['artist'][0]
                ];
                
                break;
                
            case 'collect':
                
                $items = $api->format(true)->playlist($id);
                
                $items = json_decode($items,true);
                
                foreach ($items as $val) {
                    
                    $cover = json_decode($api->format(true)->pic($val['pic_id']),true)['url'];
                    
                    $info[] = [
                        'name' => $val['name'],
                        'url' => '',
                        'song_id' => $val['id'],
                        'cover' => $cover,
                        'author' => $val['artist'][0]
                    ];
                }
                
                // 音乐随机播放
                if ($shuffle) shuffle($info);
                
                break;
                
            default:
                
                $data = "";
                break;
        }
        
        return $info;
    }

    /**
     * @param string $url
     * @param bool $autoplay
     * @return array
     */
    function ParseMusicUrl(string $url = '', bool $autoplay = false)
    {
        $media      = null;
        $id         = null;
        $type       = null;
        $top_domain = (new helper())->GetTopDomain($url);
        // $url = trim($url);
        
        // 播放模式
        if ($autoplay) $play_mode = 'auto';
        else $play_mode = 'normal';
        
        if($top_domain == '163.com') {
            
            // 网易云音乐
            $media='netease';
            if(preg_match('/playlist\?id=(\d+)/i',$url,$id)) list($id,$type)=array($id[1],'playlist');
            elseif(preg_match('/toplist\?id=(\d+)/i',$url,$id)) list($id,$type)=array($id[1],'playlist');
            elseif(preg_match('/album\?id=(\d+)/i',$url,$id)) list($id,$type)=array($id[1],'album');
            elseif(preg_match('/song\?id=(\d+)/i',$url,$id)) list($id,$type)=array($id[1],'song');
            elseif (preg_match('/song\/(\d+)/i',$url,$id)) list($id,$type)=array($id[1],'song');
            elseif(preg_match('/artist\?id=(\d+)/i',$url,$id)) list($id,$type)=array($id[1],'artist');
            
        } elseif($top_domain == 'qq.com') {
            
            // QQ音乐
            $media = 'tencent';
            $url = (new helper())->GetRedirectUrl($url);
            if(preg_match('/playlist\/([^\.]*)/i',$url,$id)) list($id,$type) = array($id[1],'playlist');
            elseif(preg_match('/album\/([^\.]*)/i',$url,$id))list($id,$type)=array($id[1],'album');
            elseif(preg_match('/song\/([^\.]*)/i',$url,$id))list($id,$type)=array($id[1],'song');
            elseif(preg_match('/singer\/([^\.]*)/i',$url,$id))list($id,$type)=array($id[1],'artist');
            
        } elseif($top_domain == 'xiami.com') {
            
            // 虾米音乐
            $media='xiami';
            if(preg_match('/collect\/(\w+)/i',$url,$id))list($id,$type)=array($id[1],'playlist');
            elseif(preg_match('/album\/(\w+)/i',$url,$id))list($id,$type)=array($id[1],'album');
            elseif(preg_match('/[\/.]\w+\/[songdem]+\/(\w+)/i',$url,$id))list($id,$type)=array($id[1],'song');
            elseif(preg_match('/artist\/(\w+)/i',$url,$id))list($id,$type)=array($id[1],'artist');
            if(!preg_match('/^\d*$/i',$id,$t)) {
                $data=curl($url);
                preg_match('/'.$type.'\/(\d+)/i',$data,$id);
                $id=$id[1];
            }
            
        } elseif($top_domain == 'kugou.com') {
            
            // 酷狗音乐
            $media='kugou';
            if(preg_match('/special\/single\/(\d+)/i',$url,$id))list($id,$type)=array($id[1],'playlist');
            elseif(preg_match('/#hash\=(\w+)/i',$url,$id))list($id,$type)=array($id[1],'song');
            elseif(preg_match('/album\/[single\/]*(\d+)/i',$url,$id))list($id,$type)=array($id[1],'album');
            elseif(preg_match('/singer\/[home\/]*(\d+)/i',$url,$id))list($id,$type)=array($id[1],'artist');
            
        } elseif($top_domain == 'baidu.com') {
            
            $media='baidu';
            if(preg_match('/songlist\/(\d+)/i',$url,$id))list($id,$type)=array($id[1],'playlist');
            elseif(preg_match('/album\/(\d+)/i',$url,$id))list($id,$type)=array($id[1],'album');
            elseif(preg_match('/song\/(\d+)/i',$url,$id))list($id,$type)=array($id[1],'song');
            elseif(preg_match('/artist\/(\d+)/i',$url,$id))list($id,$type)=array($id[1],'artist');
        }
        
        $res = [
            "media"         => $media,
            "url"           => $url,
            "play_mode"     => $play_mode,
            "type"          => $type,
            "id"            => $id
        ];
        
        return  $res;
    }

}