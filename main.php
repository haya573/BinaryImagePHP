<?php
/** MEMO:
 * imagecreatefromjpeg: 画像の読み込み(jpg)
 * imagecreatefrompng: 画像の読み込み(png)
 * imagecolorat: 画像上の特定位置にあるピクセルの色のインデックスを返す
 * imagecreatetruecolor: 指定した大きさの黒い画像を表す画像オブジェクトを返す
 * imagecolorallocate: 画像で使用する色を作成する
 * imagesetpixel: 指定した座標にピクセルを描画する
 * imagepng: PNG 画像を出力あるいは保存する
 * imagedestroy: 画像を破棄する
 * PHP_EOL: OSに依存しない改行コード
 * FILE_APPEND: 既にファイルがある場合は追記する
 * LOCK_EX: 書き込みしている間は他に書き込みができない
 */

require_once('./class/ImageBinarizer.php');

$imageBinarizer = new ImageBinarizer();
$dirList = $imageBinarizer->renderImgList(); // ./imgsに存在する画像一覧を表示

echo "Which number to convert binary image?\n";

// 入力
$selectedNumber = trim(fgets(STDIN));
if (!is_numeric($selectedNumber)) {
    echo "Please input just number." . PHP_EOL;
    return;
}

$selectedImgName = isset($dirList[$selectedNumber]) ? $dirList[$selectedNumber] : null;
if (is_null($selectedImgName)) {
    echo "Selected image is not found." . PHP_EOL;
    return;
}

$pathInfo = pathinfo($selectedImgName);
$ext = $pathInfo['extension'];
$selectedBaseImgName = $pathInfo['filename'];
if (!in_array($ext, $imageBinarizer->getAllowExtList())) {
    echo "{$ext} is not allowed. Please select image of jpg, jpeg or png." . PHP_EOL;
    return;
}

// 画像読み込み
$img = $imageBinarizer->loadImg($ext, $selectedImgName);
if (is_null($img)) {
    echo "Image does not exist. Please try again." . PHP_EOL;
    return;
}

$baseImgInfo = $imageBinarizer->setBaseImgInfo($img);
$binaryArr = $imageBinarizer->setBinaryToArr($img, $baseImgInfo);
$fileName = $imageBinarizer->getUniqueFileName($imageBinarizer->getAfterConvertImgDir()."/{$selectedBaseImgName}.pnm");
$filePath = $imageBinarizer->getAfterConvertImgDir()."/{$fileName}";
$header = "P1" . PHP_EOL . $baseImgInfo['width'] . PHP_EOL . $baseImgInfo['height'] . PHP_EOL;
file_put_contents($filePath, $header, FILE_APPEND | LOCK_EX);
foreach ($binaryArr as $row) {
    $rowText = "";
    foreach ($row as $col) {
        $rowText .= $col . " ";
    }
    file_put_contents($filePath, $rowText . PHP_EOL, FILE_APPEND | LOCK_EX);
}

if (file_exists($filePath)) {
    echo "Successfully saved in ./binary-imgs after converted {$selectedImgName} to {$fileName}!" . PHP_EOL;
} else {
    echo "Image does not exist. Please try again." . PHP_EOL;
}

imagedestroy($img);
imagedestroy($baseImgInfo['test_img']);

?>