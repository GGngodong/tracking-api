<?php

namespace App\Http\Controllers;

use App\Helpers\DateParser;
use App\Http\Requests\PermitLetterRequest;
use App\Http\Resources\PermitLetterResource;
use App\Models\PermitLetters;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

class PermitLetterController extends Controller
{

    public function postPermitLetter(PermitLetterRequest $request): JsonResponse
    {
        $data = $request->validated();

        if (PermitLetters::where('no_surat', $data['no_surat'])->exists()) {
            throw new HttpResponseException(response([
                'errors' => [
                    'no_surat' => ['The no surat already exists.']
                ]
            ], Response::HTTP_BAD_REQUEST));
        }

        $parsedDate = DateParser::parseDate($data['tanggal']);

        if ($parsedDate) {
            $data['tanggal'] = $parsedDate;
        } else {
            throw new HttpResponseException(response([
                'errors' => [
                    'tanggal' => ['The tanggal format is invalid. Please use dd-mm-yyyy.']
                ]
            ], Response::HTTP_BAD_REQUEST));
        }

        if ($request->hasFile('dokumen')) {
            $data['dokumen'] = $request->file('dokumen')->store('permit_letters');
        }

        $permitLetter = PermitLetters::create($data);

        return (new PermitLetterResource($permitLetter))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function getPermitLetterById($id): JsonResponse
    {
        $permitLetter = PermitLetters::find($id);

        if (!$permitLetter) {
            return response()->json([
                'errors' => [
                    'message' => 'Permit Letter not found.'
                ]
            ], Response::HTTP_NOT_FOUND);
        }

        return response()->json([
            'data' => new PermitLetterResource($permitLetter)
        ], Response::HTTP_OK);
    }

    public function getAllPermitLetter(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user->role !== 'ADMIN' && $user->role !== 'USER') {
            return response()->json([
                'errors' => ['message' => 'Unauthorized. You do not have the required permissions to perform this action.']
            ], Response::HTTP_FORBIDDEN);
        }

        $permitLetter = PermitLetters::all();
        return PermitLetterResource::collection($permitLetter)->response();
    }

    public function searchPermitLetter(PermitLetterRequest $request): JsonResponse
    {
        $data = $request->validated();
        $query = PermitLetters::query();

        if ($request->has('uraian')) {
            $query->where('uraian', 'like', '%' . $data['uraian'] . '%');
        }

        if ($request->has('no_surat')) {
            $query->where('no_surat', 'like', '%' . $data['no_surat'] . '%');
        }
        $permitLetter = $query->paginate(10);
        return PermitLetterResource::collection($permitLetter)->response();
    }

    public function updatePermitLetter(PermitLetterRequest $request, int $id): PermitLetterResource
    {

        $permitLetter = PermitLetters::where('id', $id)->first();

        if (!$permitLetter) {
            throw new HttpResponseException(response([
                'errors' => [
                    'message' => ['Permit Letter not found.']
                ]
            ], Response::HTTP_BAD_REQUEST));
        }

        $data = $request->only([
            'uraian',
            'nama_pt',
            'tanggal',
            'no_surat',
            'kategori_permit_letter',
            'produk_no_surat_mabes',
            'dokumen',
        ]);

        if ($request->has('tanggal')) {
            $parsedDate = DateParser::parseDate($data['tanggal']);

            if ($parsedDate) {
                $data['tanggal'] = $parsedDate;
            } else {
                throw new HttpResponseException(response([
                    'errors' => [
                        'tanggal' => ['The tanggal format is invalid. Please use dd-mm-yyyy.']
                    ]
                ], Response::HTTP_BAD_REQUEST));
            }
        }

        if ($request->hasFile('dokumen')) {
            if ($permitLetter->dokumen) {
                Storage::delete($permitLetter->dokumen);
            }

            $data['dokumen'] = $request->file('dokumen')->store('permit_letters');
        }

        if (isset($data['uraian'])) {
            $permitLetter->uraian = $data['uraian'];
        }

        if (isset($data['nama_pt'])) {
            $permitLetter->nama_pt = $data['nama_pt'];
        }

        if (isset($data['tanggal'])) {
            $permitLetter->tanggal = $data['tanggal'];
        }

        if (isset($data['no_surat'])) {
            $permitLetter->no_surat = $data['no_surat'];
        }

        if (isset($data['kategori_permit_letter'])) {
            $permitLetter->kategori_permit_letter = $data['kategori_permit_letter'];
        }

        if (isset($data['produk_no_surat_mabes'])) {
            $permitLetter->produk_no_surat_mabes = $data['produk_no_surat_mabes'];
        }

        if (isset($data['dokumen'])) {
            $permitLetter->dokumen = $data['dokumen'];
        }
        $data = $request->validated();
        $permitLetter->fill($data);
        $permitLetter->save();
        return new PermitLetterResource($permitLetter);
    }

    public function deletePermitLetter($id): JsonResponse
    {
        $permitLetter = PermitLetters::find($id);


        if (!$permitLetter) {
            throw new HttpResponseException(response([
                'errors' => [
                    'message' => ['Permit Letter not found.']
                ]
            ], Response::HTTP_BAD_REQUEST));

        }
        $permitLetter->delete();
        return response()->json([
            'message' => 'Permit Letter deleted successfully.'
        ], Response::HTTP_OK);
    }
}
