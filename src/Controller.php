<?php
namespace Reqres\Module\Plupload;

use Reqres\User;
use Reqres\Request;
use Reqres\Form;
use Reqres\Error;
use Reqres\Lib;
use Reqres\Server;

/**
 *
 *
 *
 */
trait Controller
{
    
	public function mod_plupload_action()
	{

		$action = GET::action(false) or exit('Action not selected');

		if(!method_exists($this, $action)) exit('Action not exists');

		$this-> $action();

	}


	/**
	 *
	 * Скачивание файла через curl
	 *
	 */
	protected function mod_plupload_curl($url, $targetDir, $fileName)
	{

		$ch = curl_init();

		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_FAILONERROR, 1);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_URL, $url);
		$content = curl_exec($ch);
		curl_close($ch);

		// проверяем уникальность имени файла
		$filePath = Lib\Files::get_unique_file_name($targetDir, $fileName, true);

		// сохраняем файл
		//if(file_put_contents($filePath, $content) === false) return false;

		// создаем файл, если файл не существует
		$fp = fopen($filePath, "w");
		// записываем в файл текст
		fwrite($fp, $content);
		// закрываем
		fclose($fp);

		return $filePath;

	}


	/**
	 *
	 * Copyright 2009, Moxiecode Systems AB
	 * Released under GPL License.
	 *
	 * License: http://www.plupload.com/license
	 * Contributing: http://www.plupload.com/contributing
	 */


	// стандартный скрипт загрузки изображений
	protected function mod_plupload($targetDir) //  ,$fileName = null
	{

		// 5 minutes execution time
		set_time_limit(5 * 60);

		// файлы загружаются по частям (количество частей и номер части)
		$chunk = POST::chunk(0, 'int', 'pos');
		$chunks = POST::chunks(0, 'int', 'pos');

		// получаем имя файла
		$fileName = POST::name() or $fileName = $_FILES["file"]["name"] or $fileName = uniqid();


		if($chunks < 2) $filePath = Lib\Files::get_unique_file_name($targetDir, $fileName);

		// смотрим метод передачи
		$contentType = SERVER::HTTP_CONTENT_TYPE() or $contentType = SERVER::CONTENT_TYPE();

		// открываем выходящий поток
		$out = fopen("{$filePath}.part", $chunk == 0 ? "wb" : "ab");

		// Handle non multipart uploads older WebKit versions didn't support multipart in HTML5
		if (strpos($contentType, "multipart") !== false) {


			// провереяем загружен ли файл
			if(!isset($_FILES['file']['tmp_name']) or !is_uploaded_file($_FILES['file']['tmp_name']))
				$this-> view()-> mod_plupload_error(103, 'Failed to move uploaded file.');

			// открываем входящий поток
			$in = fopen($_FILES['file']['tmp_name'], "rb");

		} else {

			// открываем входящий поток
			$in = fopen("php://input", "rb");

		}

		// выводим ошибки
		if(!$out) $this-> view()-> mod_plupload_error(102, 'Failed to open output stream.');
		if(!$in) $this-> view()-> mod_plupload_error(101, 'Failed to open input stream.');


		// пишем данные в файл
		while ($buff = fread($in, 4096)) fwrite($out, $buff);

		// закрываем ресурсы
		fclose($in);
		fclose($out);

		if(isset($_FILES['file']))
			if(file_exists($_FILES['file']['tmp_name']))
				unlink($_FILES['file']['tmp_name']);


		// если файл был загружен только частично, отсылаем промежуточный результат
		if($chunks && $chunk < $chunks - 1) return $this-> view()-> mod_plupload_result();

		return [$filePath.'.part', $fileName];

	}

}