<?php
namespace inis\auth\qq;

use inis\auth\qq\Error;
use think\facade\Config;

/* PHP SDK
 * @version 2.0.0
 * @author connect@qq.com
 * @copyright © 2013, Tencent Corporation. All rights reserved.
 */

// require_once(QQ_CONNECT_CLASS_PATH."ErrorCase.php");
class Recorder
{
    private static $data;
    
    private $inc;
    
    private $error;
    
    public function __construct()
    {
        $this->error = new Error;
        
        $config = Config::get('qq');
        
        $incFileContents = [
            'appid'      => $config['appid'],
            'appkey'     => $config['appkey'],
            'callback'   => $config['callback'],
            'scope'      => $config['scope'],
            'errorReport'=> $config['errorReport'],
            // 'storageType'=> $config['storageType'],
            // 'host'       => $config['host'],
            // 'user'       => $config['user'],
            // 'password'   => $config['password'],
            // 'database'   => $config['database']
        ];
        
        $this->inc = json_decode(json_encode($incFileContents));
        
        if (empty($this->inc)) $this->error->showError("20001");
        
        if (empty($_SESSION['QC_userData'])) self::$data = [];
        else self::$data = $_SESSION['QC_userData'];
    }

    public function write($name,$value)
    {
        self::$data[$name] = $value;
    }

    public function read($name)
    {
        if (empty(self::$data[$name])) $result = null;
        else $result = self::$data[$name];
        
        return $result;
    }

    public function readInc($name)
    {
        if (empty($this->inc->$name)) $result = null;
        else $result = $this->inc->$name;
        
        return $result;
    }

    public function delete($name)
    {
        unset(self::$data[$name]);
    }

    function __destruct()
    {
        $_SESSION['QC_userData'] = self::$data;
    }
}
