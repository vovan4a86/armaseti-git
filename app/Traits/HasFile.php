<?php namespace App\Traits;

use Illuminate\Support\Str;
use Illuminate\Http\UploadedFile ;

/**
 * Created by PhpStorm.
 * User: aleks
 * Date: 19.12.2017
 * Time: 11:09
 */


trait HasFile{
	public $file_field = 'file';

	public function getFileSrcAttribute() {
		return $this->{$this->file_field} ? url(self::UPLOAD_URL . $this->{$this->file_field}) : null;
	}

    public function fileSrc($slug) {
        return $this->{$this->file_field} ? url(self::UPLOAD_URL . $slug . '/'  . $this->{$this->file_field}) : null;
    }

    public function deleteSrcFile($slug = null) {
        if(!$this->{$this->file_field}) return;

        if(!$slug){
            $upload_url_full = self::UPLOAD_URL;
        } else {
            $upload_url_full = self::UPLOAD_URL . $slug . '/';
        }

        @unlink(public_path($upload_url_full . $this->{$this->file_field}));
    }

    /**
     * Converts bytes into human readable file size.
     *
     * @param string $bytes
     * @return string human readable file size (2,87 Мб)
     * @author Mogilev Arseny
     */
    public function fileSizeConvert(string $bytes): string {
        $bytes = floatval($bytes);
        $arBytes = array(
            0 => array(
                "UNIT" => "тб",
                "VALUE" => pow(1024, 4)
            ),
            1 => array(
                "UNIT" => "гб",
                "VALUE" => pow(1024, 3)
            ),
            2 => array(
                "UNIT" => "мб",
                "VALUE" => pow(1024, 2)
            ),
            3 => array(
                "UNIT" => "кб",
                "VALUE" => 1024
            ),
            4 => array(
                "UNIT" => "б",
                "VALUE" => 1
            ),
        );

        foreach($arBytes as $arItem)
        {
            if($bytes >= $arItem["VALUE"])
            {
                $result = $bytes / $arItem["VALUE"];
                $result = str_replace(".", "," , strval(round($result, 2)))." ".$arItem["UNIT"];
                break;
            }
        }
        return $result;
    }

    public function getFileSizeAttribute() {
        $fileUrl = public_path(self::UPLOAD_URL . $this->{$this->file_field});
        if(file_exists($fileUrl)) {
            $size = filesize($fileUrl);
            return $this->fileSizeConvert($size);
        }
        return 0;

    }

	/**
	 * @param UploadedFile $file
	 * @return string
	 */
    public static function uploadFile(UploadedFile $file): string {
        $file_name = md5(uniqid(rand(), true)) . '_' . time() . '.' . Str::lower($file->getClientOriginalExtension());
        $file->move(public_path(self::UPLOAD_URL), $file_name);
        return $file_name;
    }
}
