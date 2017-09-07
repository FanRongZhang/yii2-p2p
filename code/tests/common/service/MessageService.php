<?php
namespace common\service;
use yii;
use common\models\QfbMessage;
/**
 * 
 * @author jin
 *
 */
class MessageService extends BaseService
{
    
    public function getOneById($id=0)
    {
        if (empty($id))
        {
            return false;
        }
        $new = QfbMessage::findOne($id);
        if ($new)
        {
            return $new;
        }
        else
        {
            return false;
        }
        
    }


    public function setContent($title,$content)
    {
$content = <<<END

<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
        <meta name="apple-mobile-web-app-capable" content="yes"/>
        <meta property="qc:admins" content="13666753740606375" />
        <meta property="wb:webmaster" content="02d1e0af77e62d70" />
        <title>$title</title>

    </head>
    <body style= "word-wrap : break-word ;">
        $content
    </body>
</html>
END;
        return $content;
    }


    public function getModel()
    {
        return new QfbMessage();
    }
}

?>