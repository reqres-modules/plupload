<?php
namespace Reqres\Module\Plupload;

use Reqres\Response;

trait View  {
    
    
    /**
     *
     *
     *
     */
	protected function mod_plupload_no_cache_response()
	{

		return Response\JSON::json()
			// HTTP headers for no cache etc
			-> header("Expires: Mon, 26 Jul 1997 05:00:00 GMT")
			-> header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT")
			-> header("Cache-Control: no-store, no-cache, must-revalidate")
			-> header("Cache-Control: post-check=0, pre-check=0", false)
			-> header("Pragma: no-cache")
		;

	}

    /**
     *
     *
     *
     */
	function mod_plupload_error($code, $message)
	{

		return $this-> mod_plupload_no_cache_response()
			-> data([
				'jsonrpc' => '2.0',
				'error' => [
					'code' => $code,
					'message' => $message			
				],
				'id' => 'id'							
			])
		;

	}

    /**
     *
     *
     *
     */
	function mod_plupload_result()
	{

		return $this-> mod_plupload_no_cache_response()
			-> data([
				'jsonrpc' => '2.0',
				'result' => null,
				'id' => 'id'							
			])
		;

	}

    /**
     *
     *
     *
     */
	function mod_plupload_curl_result()
	{

		return Response\Text::newtext('OK');

	}

    /**
     *
     *
     *
     */
	function mod_plupload_curl_error()
	{

		return Response\Text::newtext('OK')
			-> status(404)
		);

	}


}
