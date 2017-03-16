<?php

namespace Harris\AliyunOSS;
use Illuminate\Support\Facades\File;
use Carbon\Carbon;
use Illuminate\Support\Facades\Config;
use JohnLui\AliyunOSS;

class OSSUtils {
	
	private $ossClient;
	
	public function __construct($isInternal = false) {
		if(config('networkType') == 'VPC' && !$isInternal) {
			throw new \Exception('VPC模式下不允许外网上传或者下载');
		}
		$this->ossClient = AliyunOSS::boot(
			config('fileuploads.aliyun-oss.ossCity'),
			config('fileuploads.aliyun-oss.networkType'),
			$isInternal,
			config('fileuploads.aliyun-oss.accessKeyId'),
			config('fileuploads.aliyun-oss.accessKeySecret')
		);
		$this->ossClient->setBucket(config('fileuploads.aliyun-oss.ossBucket'));
	}
	
	public static function doUpload($mpf) {
		$oName = $mpf->getClientOriginalName();
		$oExt = $mpf->getClientOriginalExtension();
		$nName = md5($oName . time() . rand()) . '.' . $oExt;
		$date = Carbon::now()->format('Ymd');
		$path = $date . '/ ' . $nName;
		$ossKey = Config::get('fileuploads.aliyun-oss.ossPrefix') . $path;
		$oss = new OSSUtils();
		if($oss->ossClient->uploadFile($ossKey, $mpf->getRealPath())) {
			return $path;
		}
		return false;
	}
	
	public static function doUploadFs($oPath) {
		$oName = basename($oPath);
		$oExt = substr(strrchr($oName, '.'), 1);
		if($oExt == false) {
			$oExt = 'jpg';
		}
		$nName = md5($oName . time() . rand()) . '.' . $oExt;
		$date = Carbon::now()->format('Ymd');
		$path = $date . '/ ' . $nName;
		$ossKey = Config::get('fileuploads.aliyun-oss.ossPrefix') . $path;
		$oss = new OSSUtils();
		if($oss->ossClient->uploadFile($ossKey, $oPath)) {
			return $path;
		}
		return false;
	}
	
	public static function doUploadContent($content) {
		$oExt = 'jpg';
		$nName = md5(time() . rand()) . '.' . $oExt;
		$date = Carbon::now()->format('Ymd');
		$path = $date . '/ ' . $nName;
		$ossKey = Config::get('fileuploads.aliyun-oss.ossPrefix') . $path;
		$oss = new OSSUtils();
		if($oss->ossClient->uploadContent($ossKey, $content)) {
			return $path;
		}
		return false;
	}
	
	public static function deleteOSSObject($path) {
		$oss = new OSSUtils();
		$ossKey = config('fileuploads.aliyun-oss.ossPrefix') . $path;
		$oss->ossClient->deleteObject(config('fileuploads.aliyun-oss.ossBucket'), $ossKey);
	}
	
	public static function url($path, $dimension = null) {
		$ossKey = config('fileuploads.aliyun-oss.ossPrefix') . $path;
		$url = config('fileuploads.aliyun-oss.staticEndPoint') . $ossKey;
		return self::append($url, $dimension);
	}
	
	public static function getObjectUrl($ossKey) {
		return config('fileuploads.aliyun-oss.staticEndPoint') . $ossKey;
	}
	
	public static function getAllObjectKey() {
		$oss = new OSSUtils();
		return $oss->ossClient->getAllObjectKey(config('fileuploads.aliyun-oss.ossBucket'));
	}
	
	public static function getAllObjectUrls() {
		$objectKeys = self::getAllObjectKey();
		$data = [];
		foreach($objectKeys as $key) {
			$data[count($data)] = [
				'url' => self::getObjectUrl($key),
			];
		}
		return $data;
	}
	
	public static function append($imgUrl, $dimension) {
		if(!empty($dimension)) {
			$divider = strpos($dimension, 'x');
			$height = substr($dimension, $divider + 1);
			$width = substr($dimension, 0, $divider);
			return $imgUrl . '@1e_1c_0o_0l_' . $height . 'h_' . $width . 'w.png';
		}
		return $imgUrl;
	}
}