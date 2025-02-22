<?php

namespace App\Http\Controllers;

use App\Helpers\DateParser;
use App\Http\Requests\PermitLetterRequest;
use App\Http\Resources\PermitLetterResource;
use App\Models\PermitLetters;
use App\Models\User;
use App\Notifications\AdminPermitLetterNotification;
use App\Notifications\UserPermitLetterNotification;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Notifications;
use Symfony\Component\HttpFoundation\Response;

class PermitLetterController extends Controller
{

    public function postPermitLetter(PermitLetterRequest $request): JsonResponse
    {
        $data = $request->validated();

        if (PermitLetters::where('no_surat', $data['no_surat'])->exists()) {
            throw new HttpResponseException(response([
                'statusCode' => Response::HTTP_BAD_REQUEST,
                'status' => 'error',
                'message' => 'The no surat already exists.',
            ], Response::HTTP_BAD_REQUEST));
        }

        $data['upload_status'] = 'PENDING';
        $parsedDate = DateParser::parseDate($data['tanggal']);
        $data['user_id'] = $request->user()->id;

        if ($parsedDate) {
            $data['tanggal'] = $parsedDate;
        } else {
            throw new HttpResponseException(response([
                'statusCode' => Response::HTTP_BAD_REQUEST,
                'status' => 'error',
                'message' => 'Invalid tanggal format.',
            ], Response::HTTP_BAD_REQUEST));
        }

        if ($request->hasFile('dokumen')) {
            $filepath = $request->file('dokumen')->store('public/permit_letters');
            $data['dokumen'] = $filepath;
        }

        $permitLetter = PermitLetters::create($data);

        $request->user()->notify(new UserPermitLetterNotification(
            $permitLetter,
            'Your permit letter has been uploaded and is awaiting review.'
        ));

        $admins = User::where('role', 'ADMIN')->get();
        Notification::send($admins, new AdminPermitLetterNotification($permitLetter));

        return response()->json([
            'statusCode' => Response::HTTP_CREATED,
            'status' => 'success',
            'message' => 'Permit Letter created successfully.',
            'data' => new PermitLetterResource($permitLetter)
        ], Response::HTTP_CREATED);
    }

    public function getPermitLetterById($id): JsonResponse
    {
        $permitLetter = PermitLetters::find($id);

        if (!$permitLetter) {
            return response()->json([
                'statusCode' => Response::HTTP_NOT_FOUND,
                'status' => 'error',
                'message' => 'Permit Letter not found.',
            ], Response::HTTP_NOT_FOUND);
        }

        if ($permitLetter->dokumen) {
            $permitLetter->dokumen_url = Storage::url($permitLetter->dokumen);
        }

        return response()->json([
            'statusCode' => Response::HTTP_OK,
            'status' => 'success',
            'message' => 'Permit Letter retrieved successfully.',
            'data' => new PermitLetterResource($permitLetter)
        ], Response::HTTP_OK);
    }

    public function getLatestPermitLetter(Request $request): JsonResponse
    {
        $permitLetter = PermitLetters::orderBy('created_at', 'desc')->first();

        if (!$permitLetter) {
            return response()->json([
                'statusCode' => Response::HTTP_NOT_FOUND,
                'status' => 'error',
                'message' => 'Permit Letter not found.',
            ], Response::HTTP_NOT_FOUND);
        }

        if ($permitLetter->dokumen) {
            $permitLetter->dokumen_url = Storage::url($permitLetter->dokumen);
        }

        return response()->json([
            'statusCode' => Response::HTTP_OK,
            'status' => 'success',
            'message' => 'Permit Letter retrieved successfully.',
            'data' => new PermitLetterResource($permitLetter)
        ], Response::HTTP_OK);
    }

    public function getAllPermitLetter(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user->role !== 'ADMIN' && $user->role !== 'USER') {
            return response()->json([
                'statusCode' => Response::HTTP_FORBIDDEN,
                'status' => 'error',
                'message' => 'Unauthorized. You do not have the required permissions to perform this action.',
            ], Response::HTTP_FORBIDDEN);
        }

        $permitLetters = PermitLetters::all()->map(function ($permitLetter) {
            if ($permitLetter->dokumen) {
                $permitLetter->dokumen_url = Storage::url($permitLetter->dokumen);
            }
            return $permitLetter;
        });
        return response()->json([
            'statusCode' => Response::HTTP_OK,
            'status' => 'success',
            'message' => 'Permit letters retrieved successfully.',
            'data' => PermitLetterResource::collection($permitLetters)
        ], Response::HTTP_OK);
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

        if ($request->has('nama_pt')) {
            $query->where('nama_pt', 'like', '%' . $data['nama_pt'] . '%');
        }

        if ($request->has('tanggal')) {
            $query->where('tanggal', 'like', '%' . $data['tanggal'] . '%');
        }

        if ($request->has('kategori_permit_letter')) {
            $query->where('kategori_permit_letter', 'like', '%' . $data['kategori_permit_letter'] . '%');
        }

        if ($request->has('produk_no_surat_mabes')) {
            $query->where('produk_no_surat_mabes', 'like', '%' . $data['produk_no_surat_mabes'] . '%');
        }

        if ($request->has('upload_status')) {
            $query->where('upload_status', 'like', '%' . $data['upload_status'] . '%');
        }

        if ($request->has('sub_kategori_permit_letter')) {
            $query->where('sub_kategori_permit_letter', 'like', '%' . $data['sub_kategori_permit_letter'] . '%');
        }

        $permitLetter = $query->paginate(perPage: 10, page: 1);
        if ($permitLetter->isEmpty()) {
            return response()->json([
                'statusCode' => Response::HTTP_NOT_FOUND,
                'status' => 'error',
                'message' => 'No Permit Letters found.',
            ], Response::HTTP_NOT_FOUND);
        }

        $permitLetter->getCollection()->transform(function ($item) {
            $item->dokumen_url = $item->dokumen ? Storage::url($item->dokumen) : null;
            return $item;
        });

        return response()->json([
            'statusCode' => Response::HTTP_OK,
            'status' => 'success',
            'message' => 'Permit letters retrieved successfully.',
            'data' => PermitLetterResource::collection($permitLetter)
        ], Response::HTTP_OK);
    }

    public function updatePermitLetter(PermitLetterRequest $request, int $id): PermitLetterResource
    {

        $permitLetter = PermitLetters::where('id', $id)->first();

        if (!$permitLetter) {
            throw new HttpResponseException(response([
                'statusCode' => Response::HTTP_BAD_REQUEST,
                'status' => 'error',
                'message' => 'Permit Letter not found.',
            ], Response::HTTP_BAD_REQUEST));
        }

        $data = $request->only([
            'uraian',
            'nama_pt',
            'tanggal',
            'no_surat',
            'status_tahapan',
            'kategori_permit_letter',
            'produk_no_surat_mabes',
            'sub_kategori_permit_letter',
            'dokumen',
            'note',
            'upload_status'
        ]);

        if ($request->has('tanggal')) {
            $parsedDate = DateParser::parseDate($data['tanggal']);

            if ($parsedDate) {
                $data['tanggal'] = $parsedDate;
            } else {
                throw new HttpResponseException(response([
                    'statusCode' => Response::HTTP_BAD_REQUEST,
                    'status' => 'error',
                    'message' => 'The tanggal format is invalid. Please use dd-mm-yyyy.',
                ], Response::HTTP_BAD_REQUEST));
            }
        }

        if ($request->hasFile('dokumen')) {
            if ($permitLetter->dokumen) {
                Storage::delete($permitLetter->dokumen);
            }

            $filePath = $request->file('dokumen')->store('public/permit_letters');
            $data['dokumen'] = $filePath;
        }

        $permitLetter->update($data);


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

        if (isset($data['status_tahapan'])) {
            $permitLetter->status_tahapan = $data['status_tahapan'];
        }

        if (isset($data['produk_no_surat_mabes'])) {
            $permitLetter->produk_no_surat_mabes = $data['produk_no_surat_mabes'];
        }

        if (isset($data['dokumen'])) {
            $permitLetter->dokumen = $data['dokumen'];
        }

        if (isset($data['note'])) {
            $permitLetter->note = $data['note'];
        }

        if (isset($data['sub_kategori_permit_letter'])) {
            $permitLetter->sub_kategori_permit_letter = $data['sub_kategori_permit_letter'];
        }

        if (isset($data['upload_status'])) {
            $permitLetter->upload_status = $data['upload_status'];
        }

        $data = $request->validated();
        $permitLetter->fill($data);
        $permitLetter->save();

        if ($permitLetter->user) {
            if (isset($data['upload_status'])) {
                $status = $data['upload_status'];
                $message = match ($status) {
                    'APPROVED' => 'Upload Status is APPROVED.',
                    'REJECTED' => 'Upload Status is REJECTED. Please review the notes for more details.',
                    default => 'Your permit letter status has been updated to: ' . $status,
                };
                $permitLetter->user->notify(new UserPermitLetterNotification($permitLetter, $message));
            } elseif (isset($data['note'])) {
                $permitLetter->user->notify(new UserPermitLetterNotification(
                    $permitLetter,
                    'Your permit letter has been updated. Please review the notes for more details.'
                ));
            } elseif (isset($data['status_tahapan'])) {
                $status = $data['status_tahapan'];
                $message = match ($status) {
                    'Draft', 'Verification 3',
                    'Approval' => 'Your permit letter status has been updated to ' . $status,
                    'Release' => 'Your permit letter is ' . $status . ', you might want to check it',
                    default => 'Your permit letter status has been updated.',
                };
                $permitLetter->user->notify(new UserPermitLetterNotification($permitLetter, $message));
            }
        }
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
            'statusCode' => Response::HTTP_OK,
            'status' => 'success',
            'message' => 'Permit Letter deleted successfully.'
        ], Response::HTTP_OK);
    }

    public function getApprovedPermitLetter(): JsonResponse
    {

        $permitLetters = PermitLetters::where('upload_status', 'APPROVED')->get()->map(function ($permitLetter) {
            if ($permitLetter->dokumen) {
                $permitLetter->dokumen_url = Storage::url($permitLetter->dokumen);
            }
            return $permitLetter;
        });


        if ($permitLetters->isEmpty()) {
            return response()->json([
                'statusCode' => Response::HTTP_NOT_FOUND,
                'status' => 'error',
                'message' => 'No approved Permit Letters found.',
            ], Response::HTTP_NOT_FOUND);
        }

        return response()->json([
            'statusCode' => Response::HTTP_OK,
            'status' => 'success',
            'message' => 'Approved Permit Letters retrieved successfully.',
            'data' => PermitLetterResource::collection($permitLetters)
        ], Response::HTTP_OK);
    }

    public function getRejectedPermitLetter(): JsonResponse
    {

        $permitLetters = PermitLetters::where('upload_status', 'REJECTED')->get()->map(function ($permitLetter) {
            if ($permitLetter->dokumen) {
                $permitLetter->dokumen_url = Storage::url($permitLetter->dokumen);
            }
            return $permitLetter;
        });

        if ($permitLetters->isEmpty()) {
            return response()->json([
                'statusCode' => Response::HTTP_NOT_FOUND,
                'status' => 'error',
                'message' => 'No rejected Permit Letters found.',
            ], Response::HTTP_NOT_FOUND);
        }

        return response()->json([
            'statusCode' => Response::HTTP_OK,
            'status' => 'success',
            'message' => 'Rejected Permit Letters retrieved successfully.',
            'data' => PermitLetterResource::collection($permitLetters)
        ], Response::HTTP_OK);

    }

    public function getPendingPermitLetter(): JsonResponse
    {

        $permitLetters = PermitLetters::where('upload_status', 'PENDING')->get()->map(function ($permitLetter) {
            if ($permitLetter->dokumen) {
                $permitLetter->dokumen_url = Storage::url($permitLetter->dokumen);
            }
            return $permitLetter;
        });

        if ($permitLetters->isEmpty()) {
            return response()->json([
                'statusCode' => Response::HTTP_NOT_FOUND,
                'status' => 'error',
                'message' => 'No rejected Permit Letters found.',
            ], Response::HTTP_NOT_FOUND);
        }

        return response()->json([
            'statusCode' => Response::HTTP_OK,
            'status' => 'success',
            'message' => 'Rejected Permit Letters retrieved successfully.',
            'data' => PermitLetterResource::collection($permitLetters)
        ], Response::HTTP_OK);

    }
}
