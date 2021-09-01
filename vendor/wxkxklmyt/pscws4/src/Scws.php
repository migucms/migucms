<?php

/**
 * PSCWS4中文分词工具
 */
namespace wxkxklmyt;

define('PATH', dirname(__FILE__));

class Scws{

    /**
     * SCWS中文分词
     *
     * @param string $text 分词字符串
     * @param number $number 权重高的词数量(默认5个)
     * @param string $type 返回类型,默认字符串
     * @param string $delimiter 分隔符
     * @return string|array 字符串|数组
     */
    public function scws($text = '', $number = 5, $type = true, $delimiter = ' '){
        if(empty($text)){
            return $text;
        }
        
        $scws = new PSCWS4();
        $scws -> set_dict(PATH . '/lib/dict.utf8.xdb');
        $scws -> set_rule(PATH . '/lib/rules.utf8.ini');
        $scws -> set_ignore(true);
        $scws -> send_text($text);
        $words = $scws -> get_tops(5);
        $scws -> close();
        
        $tags = [];
        foreach($words as $k => $val){
            $tags[] = $val['word'];
        }
        
        return $type === true ? implode($delimiter, $tags) : $tags;
    }
}