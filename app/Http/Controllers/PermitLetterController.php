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
            $file = $request->file('dokumen');
            $filename = time() . '_' . $file->getClientOriginalName();

            $dest = rtrim($_SERVER['DOCUMENT_ROOT'], '/') . '/permit_letters';

            if (!file_exists($dest)) {
                mkdir($dest, 0755, true);
            }

            $file->move($dest, $filename);
            $data['dokumen'] = 'permit_letters/' . $filename;

        }

        $permitLetter = PermitLetters::create($data);

        $request->user()->notify(new UserPermitLetterNotification(
            $permitLetter,
            'Your permit letter has been uploaded and is awaiting review.'
        ));

        $admins = User::where('role', 'ADMIN')->get();
        Notification::send($admins, new AdminPermitLetterNotification($permitLetter));

        $permitLetter->dokumen_url = $this->generatePublicUrl($permitLetter->dokumen);

        return response()->json([
            'statusCode' => Response::HTTP_CREATED,
            'status' => 'success',
            'message' => 'Permit Letter created successfully.',
            'data' => new PermitLetterResource($permitLetter)
        ], Response::HTTP_CREATED);
    }

    protected function generatePublicUrl(?string $path): ?string
    {
        if (!$path) {
            return null;
        }
        $full = rtrim($_SERVER['DOCUMENT_ROOT'], '/') . '/' . $path;
        return file_exists($full)
            ? url($path)
            : null;
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

        $permitLetter->dokumen_url = $this->generatePublicUrl($permitLetter->dokumen);

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

        return response()->json([
            'statusCode' => Response::HTTP_OK,
            'status' => 'success',
            'message' => 'Permit Letter retrieved successfully.',
            'data' => new PermitLetterResource($permitLetter),
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

        $permitLetters = PermitLetters::all()
            ->map(function ($pl) {
                $pl->dokumen_url = $this->generatePublicUrl($pl->dokumen);
                return $pl;
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
        $permitLetter = PermitLetters::find($id);
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
            'note',
            'upload_status',
        ]);

        if ($request->has('tanggal')) {
            $parsedDate = DateParser::parseDate($data['tanggal']);
            if (!$parsedDate) {
                throw new HttpResponseException(response([
                    'statusCode' => Response::HTTP_BAD_REQUEST,
                    'status' => 'error',
                    'message' => 'The tanggal format is invalid. Please use dd-mm-yyyy.',
                ], Response::HTTP_BAD_REQUEST));
            }
            $data['tanggal'] = $parsedDate;
        }

        if ($request->hasFile('dokumen')) {
            $file = $request->file('dokumen');
            $filename = time() . '_' . $file->getClientOriginalName();

            $isAdmin = $request->user()->role === 'ADMIN';
            $isReleasePhase = isset($data['status_tahapan']) && $data['status_tahapan'] === 'Release';

            if ($isAdmin && $isReleasePhase) {
                $dest = rtrim($_SERVER['DOCUMENT_ROOT'], '/') . '/permit_letters_released';
                if (!file_exists($dest)) {
                    mkdir($dest, 0755, true);
                }
                $file->move($dest, $filename);
                $data['released_dokumen'] = 'permit_letters_released/' . $filename;
            } else {
                $dest = rtrim($_SERVER['DOCUMENT_ROOT'], '/') . '/permit_letters';
                if (!file_exists($dest)) {
                    mkdir($dest, 0755, true);
                }
                $file->move($dest, $filename);
                $data['dokumen'] = 'permit_letters/' . $filename;
            }
        }

        $permitLetter->update($data);

        if ($permitLetter->user) {
            if (isset($data['upload_status'])) {
                $status = $data['upload_status'];
                $message = match ($status) {
                    'APPROVED' => 'Upload Status is APPROVED.',
                    'REJECTED' => 'Upload Status is REJECTED. Please review the notes for more details.',
                    default => 'Your permit letter status has been updated to: ' . $status,
                };
            } elseif (isset($data['note'])) {
                $message = 'Your permit letter has been updated. Please review the notes for more details.';
            } elseif (isset($data['status_tahapan'])) {
                $status = $data['status_tahapan'];
                $message = match ($status) {
                    'Draft', 'Verifikasi 3', 'Approval' => 'Your permit letter status has been updated to ' . $status,
                    'Release' => 'Your permit letter is ' . $status . ', you might want to check it',
                    default => 'Your permit letter status has been updated.',
                };
            }

            if (isset($message)) {
                $permitLetter->user->notify(
                    new UserPermitLetterNotification($permitLetter, $message)
                );
            }
        }

        $permitLetter->dokumen_url = $this->generatePublicUrl($permitLetter->dokumen);
        $permitLetter->released_dokumen_url = $this->generatePublicUrl($permitLetter->released_dokumen);

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

        $file = $_SERVER['DOCUMENT_ROOT'] . '/' . $permitLetter->dokumen;
        if (file_exists($file)) {
            unlink($file);
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
                'message' => 'No pending Permit Letters found.',
            ], Response::HTTP_NOT_FOUND);
        }

        return response()->json([
            'statusCode' => Response::HTTP_OK,
            'status' => 'success',
            'message' => 'Pending Permit Letters retrieved successfully.',
            'data' => PermitLetterResource::collection($permitLetters)
        ], Response::HTTP_OK);

    }

    public function getReleasePermitLetter(): JsonResponse
    {
        $releasedPermitLetters = PermitLetters::where('status_tahapan', 'Release')->get();

        if ($releasedPermitLetters->isEmpty()) {
            return response()->json([
                'statusCode' => Response::HTTP_NOT_FOUND,
                'status' => 'error',
                'message' => 'No released Permit Letters found.',
            ], Response::HTTP_NOT_FOUND);
        }

        return response()->json([
            'statusCode' => Response::HTTP_OK,
            'status' => 'success',
            'message' => 'Released Permit Letters retrieved successfully.',
            'data' => PermitLetterResource::collection($releasedPermitLetters),
        ], Response::HTTP_OK);
    }
}
