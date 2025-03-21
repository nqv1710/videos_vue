<?php

namespace App\Http\Controllers;

use App\Models\FactoryVisitor;
use Illuminate\Http\Request;
use Inertia\Inertia;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Support\Facades\Mail;
use App\Mail\VisitConfirmation;
use App\Services\GoogleSheetsService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FactoryVisitorController extends Controller
{
    /**
     * Hiển thị danh sách khách tham quan
     */
    public function index(Request $request)
    {
        try {
            $search = $request->input('search');
            $sortBy = $request->input('sort_by', 'created_at');
            $sortDesc = $request->boolean('sort_desc', true);
            $perPage = $request->input('per_page', 10);

            $query = FactoryVisitor::query()
                ->select(['id', 'name', 'email', 'phone', 'company', 'visit_date', 'number_of_visitors', 'qr_code', 'created_at']);

            // Tìm kiếm
            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%")
                        ->orWhere('company', 'like', "%{$search}%");
                });
            }

            // Sắp xếp
            $query->orderBy($sortBy, $sortDesc ? 'desc' : 'asc');

            // Phân trang với eager loading
            $visitors = $query->paginate($perPage);

            return Inertia::render('FactoryVisitor/Index', [
                'visitors' => $visitors,
                'filters' => [
                    'search' => $search,
                    'sort_by' => $sortBy,
                    'sort_desc' => $sortDesc,
                    'per_page' => $perPage
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error in visitor index:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->back()->with('error', 'Có lỗi xảy ra khi tải danh sách.');
        }
    }

    /**
     * Hiển thị form đăng ký
     */
    public function create()
    {
        return Inertia::render('FactoryVisitor/Register');
    }

    /**
     * Lưu thông tin đăng ký mới
     */
    public function store(Request $request,GoogleSheetsService $googleSheetsService)
    {

        // Validate đầu vào
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email',
            'phone' => 'required|string',
            'company' => 'nullable|string',
            'visit_date' => 'required|date|after:today',
            'number_of_visitors' => 'required|integer|min:1',
            'purpose' => 'nullable|string',
        ]);

        // Tạo visitor record
        $visitor = FactoryVisitor::create($validated);

        // Tạo QR code
        try {
            // Tạo URL tuyệt đối cho QR code
            $qrCodeContent = url()->to(route('factory-visitors.show', $visitor->id));

            // Log URL để kiểm tra
            Log::info('QR Code URL:', ['url' => $qrCodeContent]);

            // Tạo QR code với GD backend
            $qrCode = QrCode::format('png')
                ->size(400)
                ->margin(1)
                ->backgroundColor(255, 255, 255)
                ->generate($qrCodeContent);

            // Tạo tên file duy nhất
            $fileName = 'qr_codes/' . Str::uuid() . '.png';

            // Đảm bảo thư mục tồn tại
            Storage::disk('public')->makeDirectory('qr_codes');

            // Lưu QR code vào storage
            Storage::disk('public')->put($fileName, $qrCode);
            // Log để debug
            Log::info('QR Code generated and stored:', [
                'visitor_id' => $visitor->id,
                'file_path' => $fileName
            ]);

            // Cập nhật visitor với đường dẫn QR code
            $visitor->qr_code = $fileName;
            $visitor->save();

            // Gửi email xác nhận với QR code
            Mail::to($visitor->email)->send(new VisitConfirmation($visitor));
            try {
                $googleSheetsService->appendRow([
                    now()->format('d/m/Y H:i:s'), // Timestamp
                    $visitor->name,
                    $visitor->email,
                    $visitor->phone,
                    $visitor->company ?? '',
                    $visitor->visit_date->format('d/m/Y H:i:s'),
                    $visitor->number_of_visitors,
                    $visitor->purpose ?? '',
                    $visitor->status ?? '',
                ]);
            } catch (\Exception $e) {
                Log::error('Google Sheets update failed:', ['error' => $e->getMessage()]);
            }
        } catch (\Exception $e) {
            Log::error('QR Code generation failed:', [
                'error' => $e->getMessage(),
                'visitor_id' => $visitor->id,
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }

        return redirect()->route('factory-visitors.show', $visitor->id)
            ->with('success', 'Đăng ký thành công! Vui lòng kiểm tra email của bạn.');
    }

    public function syncFromGoogleSheets(GoogleSheetsService $sheetsService)
    {
        try {
            $data = $sheetsService->getSheetData();
            if (empty($data) || count($data) < 2) {
                return response()->json(['message' => 'Không có dữ liệu hợp lệ để đồng bộ!'], 400);
            }

            array_shift($data); // Bỏ dòng tiêu đề
            $stats = ['success' => 0, 'error' => 0, 'errors' => []];
            $batchSize = 100; // Tăng kích thước batch

            foreach (array_chunk($data, $batchSize) as $batchIndex => $batch) {
                // Lấy danh sách email và phone từ batch
                $identifiers = collect($batch)->map(fn($row) => [
                    'email' => $row[2] ?? null,
                    'phone' => $row[3] ?? null
                ])->filter(fn($item) => !empty($item['email']) || !empty($item['phone']));

                // Lấy tất cả visitor hiện có trong một query
                $existingVisitors = FactoryVisitor::where(function ($query) use ($identifiers) {
                    foreach ($identifiers as $identifier) {
                        $query->orWhere(function ($q) use ($identifier) {
                            if (!empty($identifier['email'])) $q->where('email', $identifier['email']);
                            if (!empty($identifier['phone'])) $q->orWhere('phone', $identifier['phone']);
                        });
                    }
                })->get();

                // Tạo map để truy cập nhanh
                $visitorMap = $existingVisitors->mapWithKeys(fn($v) => [
                    $v->email => $v,
                    $v->phone => $v
                ])->filter();

                foreach ($batch as $index => $row) {
                    try {
                        if (empty($row[2]) && empty($row[3])) {
                            $stats['errors'][] = "Dòng {$index}: Thiếu email và số điện thoại";
                            continue;
                        }

                        $visitor = $visitorMap->get($row[2]) ?? $visitorMap->get($row[3]);
                        $visitorData = [
                            'name' => $row[1] ?? 'Khách tham quan',
                            'email' => $row[2] ?? null,
                            'phone' => $row[3] ?? null,
                            'company' => $row[4] ?? 'Chưa cập nhật',
                            'number_of_visitors' => isset($row[6]) ? (int) $row[6] : 1,
                            'purpose' => $row[7] ?? 'Tham quan nhà máy',
                            'status' => 'synced',
                            'visit_date' => !empty($row[5])
                                ? Carbon::createFromFormat('d/m/Y H:i:s', $row[5])->format('Y-m-d H:i:s')
                                : now()
                        ];

                        if ($visitor) {
                            $visitor->update($visitorData);
                        } else {
                            $visitor = FactoryVisitor::create($visitorData);
                            // Tạo QR Code và gửi email cho visitor mới
                            try {
                                $qrCode = QrCode::format('png')
                                    ->size(400)
                                    ->margin(1)
                                    ->backgroundColor(255, 255, 255)
                                    ->generate(url()->to(route('factory-visitors.show', $visitor->id)));

                                $fileName = 'qr_codes/' . Str::uuid() . '.png';
                                Storage::disk('public')->put($fileName, $qrCode);
                                $visitor->update(['qr_code' => $fileName]);

                                if ($visitor->email) {
                                    Mail::to($visitor->email)->send(new VisitConfirmation($visitor));
                                }
                            } catch (\Exception $e) {
                            }
                        }

                        $stats['success']++;
                    } catch (\Exception $e) {
                        $stats['error']++;
                        $stats['errors'][] = "Dòng {$index}: " . $e->getMessage();
                    }
                }
            }

            $message = "Đã đồng bộ thành công {$stats['success']} bản ghi";
            if ($stats['error'] > 0) {
                $message .= ". Có {$stats['error']} lỗi xảy ra.";
            }

            return response()->json([
                'message' => $message,
                'success_count' => $stats['success'],
                'error_count' => $stats['error'],
                'errors' => $stats['errors']
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Có lỗi xảy ra khi đồng bộ dữ liệu.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Hiển thị chi tiết khách tham quan
     */
    public function show($id)
    {
        try {
            $visitor = FactoryVisitor::findOrFail($id);

            // Log để debug
            Log::info('Show visitor data:', [
                'visitor_id' => $id,
                'visitor_data' => $visitor->toArray()
            ]);

            // dd để kiểm tra dữ liệu
            // dd($visitor->toArray());

            return Inertia::render('FactoryVisitor/Show', [
                'visitor' => $visitor
            ]);
        } catch (\Exception $e) {
            Log::error('Error showing visitor:', [
                'visitor_id' => $id,
                'error' => $e->getMessage()
            ]);

            return redirect()->route('factory-visitors.index')
                ->with('error', 'Không tìm thấy thông tin khách tham quan.');
        }
    }

    /**
     * Xóa đăng ký tham quan
     */
    public function destroy($id)
    {
        try {
            $visitor = FactoryVisitor::findOrFail($id);

            // Xóa file QR code nếu tồn tại
            if ($visitor->qr_code) {
                Storage::disk('public')->delete($visitor->qr_code);
            }

            // Xóa visitor record
            $visitor->delete();

            return redirect()->route('factory-visitors.index')
                ->with('success', 'Xóa đăng ký tham quan thành công!');
        } catch (\Exception $e) {
            Log::error('Error deleting visitor:', [
                'visitor_id' => $id,
                'error' => $e->getMessage()
            ]);

            return redirect()->back()
                ->with('error', 'Có lỗi xảy ra khi xóa đăng ký tham quan.');
        }
    }

    public function updateStatus(Request $request, FactoryVisitor $factoryVisitor)
    {
        try {
            Log::info('Updating visitor status:', [
                'visitor_id' => $factoryVisitor->id,
                'current_status' => $factoryVisitor->status,
                'new_status' => $request->status
            ]);

            $request->validate([
                'status' => 'required|in:pending,approved,rejected,completed'
            ]);

            $factoryVisitor->update([
                'status' => $request->status
            ]);

            return back()->with('success', 'Trạng thái đã được cập nhật thành công');
        } catch (\Exception $e) {
            Log::error('Error updating visitor status:', [
                'visitor_id' => $factoryVisitor->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return back()->with('error', 'Có lỗi xảy ra khi cập nhật trạng thái');
        }
    }
}
