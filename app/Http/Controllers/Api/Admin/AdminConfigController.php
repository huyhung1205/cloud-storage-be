<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Api\BaseApiController;
use App\Models\SystemConfig;
use Illuminate\Http\Request;

class AdminConfigController extends BaseApiController
{
    /**
     * 11.1. API: GET /api/admin/configs
     *
     * Lấy danh sách cấu hình hệ thống với tìm kiếm + phân trang.
     */
    public function index(Request $request)
    {
        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $search = isset($validated['search']) ? trim($validated['search']) : '';
        $page = (int) ($validated['page'] ?? 1);
        $perPage = (int) ($validated['per_page'] ?? 15);

        $query = SystemConfig::query();

        if ($search !== '') {
            $like = '%' . str_replace('%', '\\%', $search) . '%';
            $query->where(function ($q) use ($like) {
                $q->where('config_key', 'like', $like)
                    ->orWhere('config_value', 'like', $like);
            });
        }

        $total = (int) (clone $query)->count();

        $items = $query->orderBy('config_key')
            ->offset(($page - 1) * $perPage)
            ->limit($perPage)
            ->get(['id', 'config_key', 'config_value'])
            ->map(function (SystemConfig $cfg) {
                return [
                    'config_id' => (int) $cfg->id,
                    'config_key' => $cfg->config_key,
                    'config_value' => $cfg->config_value,
                ];
            })
            ->values()
            ->all();

        $totalPages = (int) ceil($total / max(1, $perPage));

        return response()->json([
            'data' => $items,
            'pagination' => [
                'current_page' => $page,
                'total_pages' => $totalPages,
                'total_items' => $total,
            ],
        ]);
    }

    /**
     * 11.2. API: GET /api/admin/configs/{key}
     *
     * Lấy chi tiết cấu hình theo config_key.
     */
    public function show(string $key)
    {
        $config = SystemConfig::where('config_key', $key)->first();

        if (! $config) {
            return response()->json([
                'success' => false,
                'message' => 'Configuration key not found.',
            ], 404);
        }

        return response()->json([
            'config_id' => (int) $config->id,
            'config_key' => $config->config_key,
            'config_value' => $config->config_value,
        ]);
    }

    /**
     * 11.3. API: POST /api/admin/configs
     *
     * Tạo cấu hình mới.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'config_key' => ['required', 'string', 'max:255', 'unique:system_configs,config_key'],
            'config_value' => ['required', 'string'],
        ]);

        $config = SystemConfig::create([
            'config_key' => $validated['config_key'],
            'config_value' => $validated['config_value'],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Configuration created successfully.',
            'config' => [
                'config_id' => (int) $config->id,
                'config_key' => $config->config_key,
                'config_value' => $config->config_value,
            ],
        ], 201);
    }

    /**
     * 11.4. API: PUT /api/admin/configs/{key}
     *
     * Cập nhật giá trị cấu hình hiện có.
     */
    public function update(Request $request, string $key)
    {
        $validated = $request->validate([
            'config_value' => ['required', 'string'],
        ]);

        $config = SystemConfig::where('config_key', $key)->first();

        if (! $config) {
            return response()->json([
                'success' => false,
                'message' => 'Configuration key not found.',
            ], 404);
        }

        $config->config_value = $validated['config_value'];
        $config->save();

        return response()->json([
            'success' => true,
            'message' => 'Configuration updated successfully.',
            'config' => [
                'config_id' => (int) $config->id,
                'config_key' => $config->config_key,
                'config_value' => $config->config_value,
            ],
        ]);
    }

    /**
     * 11.5. API: DELETE /api/admin/configs/{key}
     *
     * Xóa cấu hình theo config_key.
     */
    public function destroy(string $key)
    {
        $config = SystemConfig::where('config_key', $key)->first();

        if (! $config) {
            return response()->json([
                'success' => false,
                'message' => 'Configuration key not found.',
            ], 404);
        }

        $config->delete();

        return response()->json([
            'success' => true,
            'message' => 'Configuration deleted successfully.',
        ]);
    }
}
