<?php
class ImageBinarizer
{
    public const ALLOW_EXT_LIST = ['jpg', 'jpeg', 'png'];
    public const BEFORE_CONVERT_IMG_DIR = './imgs';
    public const AFTER_CONVERT_IMG_DIR = './binary-imgs';

    public function __construct()
    {
        if (!file_exists(self::AFTER_CONVERT_IMG_DIR)) {
            mkdir(self::AFTER_CONVERT_IMG_DIR);
        }
    }

    /**
     * 定数ALLOW_EXT_LISTを返す
     *
     * @return array
     */
    public function getAllowExtList()
    {
        return self::ALLOW_EXT_LIST;
    }

    /**
     * 定数BEFORE_CONVERT_IMG_DIRを返す
     *
     * @return string
     */
    public function getBeforeConvertImgDir()
    {
        return self::BEFORE_CONVERT_IMG_DIR;
    }

    /**
     * 定数AFTER_CONVERT_IMG_DIRを返す
     *
     * @return string
     */
    public function getAfterConvertImgDir()
    {
        return self::AFTER_CONVERT_IMG_DIR;
    }

    /**
     * 画素値からバイナリ値を配列に格納
     *
     * @return array
     */
    public function setBinaryToArr($img, $baseImgInfo)
    {
        $binaryArr = [];
        for($x = 0; $x < $baseImgInfo['width']; $x++) {
            for($y = 0; $y < $baseImgInfo['height']; $y++) {
                $rawRGB = imagecolorat($img, $x, $y); // 画素値を取得する
                $r = ($rawRGB >> 16) & 0xFF; // 右に16ビットずらして最下位ビットだけ取るために0xFF(2進数だと1111 1111)と積を取る(マスク演算)
                $g = ($rawRGB >> 8) & 0xFF; // 右に8ビットずらして最下位ビットだけ取るために0xFF(2進数だと1111 1111)と積を取る(マスク演算)
                $b = $rawRGB & 0xFF;
                $grayScale = round(($r + $g + $b) / 3); // 二値化
                $binaryArr[$y][$x] = (int) ($grayScale < 120);
            }
        }
        return $binaryArr;
    }

    /**
     * 画像生成に必要な情報をセットする
     *
     * @return array
     */
    public function setBaseImgInfo($img)
    {
        $width = imageSX($img);
        $height = imageSY($img);
        return [
            'width' => $width,
            'height' => $height,
        ];
    }

    /**
     * 画像を読み込みイメージIDを返す
     *
     * @param string $ext
     * @return GdImage|false
     */
    public function loadImg($ext, $imgName)
    {
        switch ($ext) {
            case 'jpg':
            case 'jpeg':
                return imagecreatefromjpeg(self::BEFORE_CONVERT_IMG_DIR."/".$imgName);
            case 'png':
                return imagecreatefrompng(self::BEFORE_CONVERT_IMG_DIR."/".$imgName);
            default:
                return null;
        }
    }

    /**
     * 同じ階層に存在する画像一覧を表示
     *
     * @param array $list
     * @return array
     */
    public function renderImgList()
    {
        $list = [];
        $dirIndex = 0;
        if ($handle = opendir(self::BEFORE_CONVERT_IMG_DIR)) {
            while (false !== ($entry = readdir($handle))) {
                if ($entry != "." && $entry != "..") {
                    $list[] = $entry;
                    echo "[{$dirIndex}] $entry\n";
                    $dirIndex++;
                }
            }
            closedir($handle);
        }
        return $list;
    }

    /**
     * ファイル名を取得
     * 同じファイル名がある場合は(1),(2)のように連番をつける
     *
     * @param string $orgPath
     * @param int $num
     * @return string
     */
    public function getUniqueFileName($orgPath, $num=0)
    {
        if ($num > 0) {
            $info = pathinfo($orgPath);
            $path = $info['dirname'] . "/" . $info['filename'] . "({$num})";
            if(isset($info['extension'])) {
                $path .= "." . $info['extension'];
            }
        } else {
            $path = $orgPath;
        }

        if (file_exists($path)) {
            $num++;
            return self::getUniqueFileName($orgPath, $num);
        } else {
            return str_replace(self::AFTER_CONVERT_IMG_DIR."/", '', $path);
        }
    }
}
?>