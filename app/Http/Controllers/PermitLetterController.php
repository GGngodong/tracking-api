<?php

namespace App\Http\Controllers;

use App\Http\Requests\PermitLetterRequest;
use App\Http\Resources\PermitLetterResource;
use App\Models\PermitLetter;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PermitLetterController extends Controller
{
    public function postPermitLetter(PermitLetterRequest $request): JsonResponse
    {

        $data = $request->validated();
        if (PermitLetter::where('no_surat', $data['no_surat'])->exists()) {
            throw new HttpResponseException(response([
                'errors' => [
                    'no_surat' => ['the no surat already exists.']
                ]
            ], 400));
        }

        $permitLetter = new PermitLetter($data);
        $permitLetter->save();

        return (new PermitLetterResource($permitLetter))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);

    }

    public function getAllPermitLetter(Request $request): JsonResponse
    {
        $permitLetter = PermitLetter::all();
        return PermitLetterResource::collection($permitLetter)->response();
    }

    public function searchPermitLetter(PermitLetterRequest $request): JsonResponse
    {
        $data = $request->validated();
        $query = PermitLetter::query();

        if ($request->has('uraian')) {
            $query->where('uraian', 'like', '%' . $data['uraian'] . '%');
        }

        if ($request->has('no_surat')) {
            $query->where('no_surat', 'like', '%' . $data['no_surat'] . '%');
        }
        $permitLetter = $query->paginate(10);
        return PermitLetterResource::collection($permitLetter)->response();
    }

    public function updatePermitLetter(PermitLetterRequest $request, $id): JsonResponse
    {
        $data = $request->validated();
        $permitLetter = PermitLetter::find($id);

        if (!$permitLetter) {
            throw new HttpResponseException(response([
                'errors' => [
                    'message' => ['Permit Letter not found.']
                ]
            ], 400));
        }

        $permitLetter->update($data);
        return (new PermitLetterResource($permitLetter))->response()->setStatusCode(Response::HTTP_OK);
    }

    public function deletePermitLetter($id): JsonResponse
    {
        $permitLetter = PermitLetter::find($id);

        if (!$permitLetter) {
            throw new HttpResponseException(response([
                'errors' => [
                    'message' => ['Permit Letter not found.']
                ]
            ], 400));

        }
        $permitLetter->delete();
        return response()->json([
            'message' => 'Permit Letter deleted successfully.'
        ], Response::HTTP_OK);
    }
}
