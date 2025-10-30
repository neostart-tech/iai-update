<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SemoaCallBack;
use Illuminate\Database\Eloquent\Casts\Json;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;


class SemoaCallBackController extends Controller
{
	public function __invoke(Request $request)
	{
		try {
			Log::info('test', $request->all());
			Log::info('verb', [$request->method()]);
			$row_id = SemoaCallBack::create([
				'data' => Json::encode($request->all())
			])->id;
		} catch (Throwable $e) {
			return response([
				'message' => $e->getMessage()
			]);
		}

		return response([
			'Message' => 'Contenu reçu avec succès',
			'row_id' => $row_id,
			'content' => $request->all()
		]);
	}
}
