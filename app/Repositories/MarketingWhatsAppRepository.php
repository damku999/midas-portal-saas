<?php

namespace App\Repositories;

use App\Contracts\Repositories\MarketingWhatsAppRepositoryInterface;
use App\Models\MarketingWhatsApp;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class MarketingWhatsAppRepository extends AbstractBaseRepository implements MarketingWhatsAppRepositoryInterface
{
    protected string $modelClass = MarketingWhatsApp::class;

    protected array $searchableFields = [
        'phone_number',
        'message',
        'status',
        'message_type',
        'whatsapp_message_id',
    ];

    public function getMarketingMessagesWithFilters(Request $request, int $perPage = 15): LengthAwarePaginator
    {
        $query = $this->getModel()->newQuery();

        // Apply search filter
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                foreach ($this->searchableFields as $field) {
                    $q->orWhere($field, 'like', "%{$search}%");
                }
            });
        }

        // Apply status filter
        if ($request->has('status') && $request->input('status') !== '') {
            $query->where('status', $request->boolean('status'));
        }

        // Apply message type filter
        if ($messageType = $request->input('message_type')) {
            $query->where('message_type', $messageType);
        }

        // Apply date range filter
        if ($startDate = $request->input('start_date')) {
            $query->whereDate('created_at', '>=', $startDate);
        }

        if ($endDate = $request->input('end_date')) {
            $query->whereDate('created_at', '<=', $endDate);
        }

        // Apply phone number filter
        if ($phoneNumber = $request->input('phone_number')) {
            $query->where('phone_number', 'like', "%{$phoneNumber}%");
        }

        // Apply sorting
        $sortField = $request->input('sort_field', 'created_at');
        $sortOrder = $request->input('sort_order', 'desc');

        if (in_array($sortField, ['created_at', 'phone_number', 'message_type', 'status'])) {
            $query->orderBy($sortField, $sortOrder);
        }

        return $query->paginate($perPage);
    }

    public function getMarketingMessagesByStatus(bool $status): Collection
    {
        return $this->getModel()->where('status', $status)->get();
    }

    public function getMarketingMessagesByType(string $type): Collection
    {
        return $this->getModel()->where('message_type', $type)->get();
    }

    public function getTodayMarketingMessages(): Collection
    {
        return $this->getModel()
            ->whereDate('created_at', today())
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function getMarketingMessagesByDateRange(string $startDate, string $endDate): Collection
    {
        return $this->getModel()
            ->whereBetween('created_at', [$startDate, $endDate])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function getMarketingMessageStatistics(): array
    {
        $model = $this->getModel();

        return [
            'total_messages' => $model->count(),
            'sent_messages' => $model->where('status', true)->count(),
            'failed_messages' => $model->where('status', false)->count(),
            'today_messages' => $model->whereDate('created_at', today())->count(),
            'this_month_messages' => $model->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->count(),
            'message_types' => $model->select('message_type')
                ->selectRaw('COUNT(*) as count')
                ->groupBy('message_type')
                ->pluck('count', 'message_type')
                ->toArray(),
            'recent_activity' => $model->orderBy('created_at', 'desc')
                ->limit(5)
                ->get(['phone_number', 'message_type', 'status', 'created_at']),
        ];
    }

    public function searchMarketingMessages(string $searchTerm, int $limit = 20): Collection
    {
        $query = $this->getModel()->newQuery();

        $query->where(function ($q) use ($searchTerm) {
            foreach ($this->searchableFields as $field) {
                $q->orWhere($field, 'like', "%{$searchTerm}%");
            }
        });

        return $query->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    public function getMarketingMessagesByPhoneNumber(string $phoneNumber): Collection
    {
        return $this->getModel()
            ->where('phone_number', 'like', "%{$phoneNumber}%")
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function getFailedMarketingMessages(): Collection
    {
        return $this->getModel()
            ->where('status', false)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function getAllMarketingMessagesForExport(): Collection
    {
        return $this->getModel()
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function markMessageAsSent(MarketingWhatsApp $marketingMessage, string $messageId): bool
    {
        return $marketingMessage->update([
            'status' => true,
            'whatsapp_message_id' => $messageId,
            'sent_at' => now(),
            'error_message' => null,
        ]);
    }

    public function markMessageAsFailed(MarketingWhatsApp $marketingMessage, string $errorMessage): bool
    {
        return $marketingMessage->update([
            'status' => false,
            'error_message' => $errorMessage,
            'sent_at' => null,
        ]);
    }
}
