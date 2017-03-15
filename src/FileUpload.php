<?php

namespace Harris\AliyunOSS;
use Illuminate\Support\Facades\File;
use Carbon\Carbon;
use Illuminate\Support\Facades\Config;
use Aliyun\OSS\Utilities\OSSUtils;

class FileUpload {
	
	public function doUpload($mpf) {
		$oName = $mpf->getClientOriginalName();
		$oExt = $mpf->getClientOriginalExtension();
		$nName = md5($oName . time() . rand()) . '.' . $oExt;
		$date = Carbon::now()->format('Ymd');
		$path = Config::get('fileuploads.aliyun-oss.ossPrefix') . $date . '/';
		$ossKey = $path . $nName;
	}
	
	public function doUploadFs() {
		
	}
}