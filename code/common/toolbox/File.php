<?php
namespace common\toolbox;

class File{

    /**
     * 读取文件数据，转换成自己可用数据
     * @param $file 文件路径
     * @param $resArr 数据键值
     * @return array
     */
    public function read($file, $resArr)
    {
        $content = file_get_contents($file);

        $dataArr = explode("\r\n", $content);

        array_shift($dataArr);
        array_pop($dataArr);

        $resultArr = [];

        foreach($dataArr as $key=>$value){
            $list = explode(',', $value);
            foreach($list as $k=>$v){
                $resultArr[$key][$resArr[$k]] = $v;
            }
        }

        return $resultArr;
    }

    /**
     * 解压文件zip
     * @param $path 文件路径
     * @param $filename 文件名（必须是*.zip格式）
     */
    public function unzip($path, $filename)
    {
        $zip = new \ZipArchive();//新建一个ZipArchive的对象
        /*
        通过ZipArchive的对象处理zip文件
        $zip->open这个方法的参数表示处理的zip文件名。
        如果对zip文件对象操作成功，$zip->open这个方法会返回TRUE
        */
        if ($zip->open($path.$filename) === TRUE)
        {
            $zip->extractTo($path);//假设解压缩到在当前路径下images文件夹的子文件夹php
            $zip->close();//关闭处理的zip文件
            return true;
        }

        return false;
    }

}