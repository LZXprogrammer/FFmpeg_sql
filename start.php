<?php
/**
 * 后台上传视频到OSS上耗时太长，利用oss软件上传视频，再执行这个程序就可以添加material_video_oss表里
 * 需要手动更改的：$image_url的图片名，isnew, ishot, isvoice, ischosen, desc, author, tag, play_num
 *
 * @var array
 */

require_once __DIR__ . '/vendor/autoload.php';

use FFMpeg\FFProbe;

// 连接数据库
$con = mysqli_connect("127.0.0.1","homestead","secret",'ad_show_control_db');
// 设置数据库编码
mysqli_query($con, "set names 'utf8' ");
mysqli_query($con, "set character_set_client=utf8");
mysqli_query($con, "set character_set_results=utf8");

// 获取视频信息的必要参数，为下面获取视频大小和时长做基础
$ffmpeg = FFProbe::create(array(
  'ffmpeg.binaries'  => '/usr/bin/ffmpeg',
  'ffprobe.binaries' => '/usr/bin/ffprobe'
));

// 存放视频的文件夹（可以根据本机的文件夹路径进行修改）
$dir = '../video/';

// 列出视频目录中的 文件和目录
$file = scandir($dir);

foreach ($file as $key => $value) {

    // 去除无用文件
    if($value == '.' || $value == '..' || $value == '.DS_Store') {
        continue;
    }

    // 获取类别名字
    $type = $value;

    foreach (scandir($dir.$value) as $k => $v) {

      // 去除无用文件
      if($v == '.' || $v == '..' || $v == '.DS_Store') {
          continue;
      }

      // 视频名字就是 $video_name[0]
      $video_name = explode('.', $v);

      // 拼接图片和视频的地址
      $image_url = "video_library/".$type."/".$video_name[0]."/aaa.jpg";
      $data_url = "video_library/".$type."/".$video_name[0]."/".$video_name[0].".".$video_name[1];

      // 需要手动改动的字段
      $play_num = 0;
      $isnew = 0;
      $ishot = 0;
      $isvoice = 0;
      $ischosen = 0;
      $author = 'nicai';
      $tag = '';
      $desc = '';

      //获取视频文件的大小，返回的是字节
      $video_size = $ffmpeg->format($dir.$type.'/'.$v)->get('size');

      //获取视频文件的时长（浮点型）
      $video_duration = $ffmpeg->format($dir.$type.'/'.$v)->get('duration');

      // 把所有需要的字段加入数据库 material_video_oss 表里
      $sql = "insert into material_video_oss
              (video_name, type, image_url, data_url, size, duration, play_num,
              isnew, ishot, isvoice, ischosen, author, tag, `desc`, update_time)
              values('".$video_name[0]."', '".$type."', '".$image_url."', '".$data_url."',
              ".$video_size.", ".$video_duration.", ".$play_num.", ".$isnew.", ".$ishot.",
              ".$isvoice.", ".$ischosen.", '".$author."', '".$tag."', '".$desc."', ".time().")
          ";

      mysqli_query($con, $sql);

    }
}
