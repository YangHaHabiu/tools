<?php

namespace App\Console\Commands\Tool;

use Illuminate\Console\Command;


class Dat2ImgCommand extends Command
{
    /**
     * @var string
     */
    protected $signature = 'dat2img:run';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '微信Dat文件解码';

    /**
     * DeleteYesterdayQcCodeCommand constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }


    public function handle()
    {
        ini_set('memory_limit', '1024M');
        $strInputDir  = $this->ask('Input Dir?');
        $strOutPutDir = $this->ask('Output Dir?');
        if (!is_dir($strInputDir)) {
            exit($strInputDir . ' is not dir !');
        }
        if (!is_dir($strOutPutDir)) {
            mkdir($strOutPutDir,0755,true);
        }
        if(!is_writable($strOutPutDir)){
            exit($strInputDir . ' is not writable !');
        }

        $arrFile = $this->getDatFiles($strInputDir);

        foreach ($arrFile as $strName => &$file) {
            echo "正在处理:".$strName;
            echo PHP_EOL;
            $strContent = file_get_contents($file);
            $arrContent = $this->str2Bytes($strContent);
            //计算 magic 值
            $a = $arrContent[0];
            $b = $arrContent[1];
            $magic = null;
            $strExt = null;
            //jpg
            if(@($a ^ 0xFF) == @($b ^ 0xD8)){
                $strExt = '.jpg';
                $magic = @($a ^ 0xFF);
            }
            //png
            if(@($a ^ 0x89) == @($b ^ 0x50)){
                $strExt = '.png';
                $magic = @($a ^ 0xFF);
            }
            //gif
            if(@($a ^ 0x47) == @($b ^ 0x49)){
                $strExt = '.gif';
                $magic = @($a ^ 0xFF);
            }
            if(is_null($magic) || is_null($strExt)){
                continue;
            }
            foreach ($arrContent as & $byte) {
                $byte ^= $magic;
            }

            $strContent = $this->bytes2Str($arrContent);

            $outFile = $strOutPutDir . DIRECTORY_SEPARATOR . $strName . $strExt;
            file_put_contents($outFile, $strContent);
            unset($strContent);
        }

    }


    /**
     * 获取文件路径
     * @param $strInputDir
     * @return array
     */
    private function getDatFiles($strInputDir)
    {
        $dh = opendir($strInputDir);
        if (empty($dh)) {
            closedir($dh);
            return [];
        }

        $arrFile = [];
        while (($file = readdir($dh)) !== false) {
            if ($file == '.' || $file == '..') {
                continue;
            }

            $strFilePath = $strInputDir . DIRECTORY_SEPARATOR . $file;
            if (is_file($strFilePath)) {
                if (!in_array($this->getFileExt($strFilePath), ['dat'])) {
                    continue;
                }
                $arrFile[$this->getFileName($file)] = $strFilePath;
            }
        }
        closedir($dh);

        return $arrFile;
    }

    /**
     * 返回扩展名
     * @param $strFilePath
     * @return string
     */
    private function getFileExt($strFilePath)
    {
        $tmp = explode('.', $strFilePath);
        return is_array($tmp) && !empty($tmp) ? strtolower(end($tmp)) : '';
    }

    /**
     * @param $file
     * @return mixed|string
     */
    private function getFileName($file)
    {
        $tmp = explode('.', $file);
        return is_array($tmp) && !empty($tmp) ? $tmp[0] : '';
    }


    /**
     * @param $str
     * @return array
     */
    private function str2Bytes($str)
    {
        $len = strlen($str);
        $bytes = array();
        for ($i = 0; $i < $len; $i++) {
            if (ord($str[$i]) >= 128) {
                $byte = ord($str[$i]) - 256;
            } else {
                $byte = ord($str[$i]);
            }
            $bytes[] = $byte;
        }
        return $bytes;
    }

    /**
     * @param $bytes
     * @return string
     */
    protected function bytes2Str($bytes)
    {
        $str = '';
        foreach ($bytes as $ch) {
            $str .= chr($ch);
        }
        return $str;
    }
}
