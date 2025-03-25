<?php
namespace App\TraitResponse;


/*
|--------------------------------------------------------------------------
| Api Responser Trait
|--------------------------------------------------------------------------
|
| This trait will be used for any response we sent to clients.
|
*/

trait ApiResponse
{
	/**
     * Return a success JSON response.
     *
     * @param  array|string  $data
     * @param  string  $message
     * @param  int|null  $code
     * @return \Illuminate\Http\JsonResponse
     */
	protected function ok($data, string $message = 'La operación ha tenido éxito', int $code = 200)
	{
		return response()->json([
			'success' => true,
			'message' => $message,
			'data' => $data
		], $code);
	}


	/**
     * Return an error JSON response.
     *
	 * @param  array|string  $data
     * @param  string|int|null  $message
     * @param  int  $code
     * @param  array|string|null  $data
     * @return \Illuminate\Http\JsonResponse
     */
	protected function information($data, $message, int $code)
	{
		return response()->json([
			'success' => false,
			'message' => $message,
			'data' => $data
		], $code);
	}

    /**
     * Return an error JSON response.
     *
     * @param  string  $message
     * @param  int  $code
     * @param  array|string|null  $data
     * @return \Illuminate\Http\JsonResponse
     */
	protected function error(string $message, int $code)
	{
		return response()->json([
			'success' => false,
			'message' => $message,
			'data' => []
		], $code);
	}

}