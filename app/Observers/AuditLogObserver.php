<?php

namespace App\Observers;

use App\Models\AuditLog;
use App\Models\InvoiceDispatch;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class AuditLogObserver
{
    public function created(Model $model): void
    {
        if ($model instanceof InvoiceDispatch) {
            $action = $model->status === 'sent' ? 'invoice.dispatched' : 'invoice.dispatch_failed';
            $this->logAction($model, $action, null, $model->toArray());
            return;
        }

        $this->logAction($model, 'created', null, $model->toArray());
    }

    public function updated(Model $model): void
    {
        $original = $model->getOriginal();
        $changes = $model->getChanges();

        // Remove timestamps from changes tracking
        unset($changes['updated_at']);
        unset($original['updated_at']);

        if (!empty($changes)) {
            $oldValues = array_intersect_key($original, $changes);
            $this->logAction($model, 'updated', $oldValues, $changes);
        }
    }

    public function deleted(Model $model): void
    {
        $this->logAction($model, 'deleted', $model->toArray(), null);
    }

    private function logAction(Model $model, string $action, ?array $oldValues, ?array $newValues): void
    {
        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => $action,
            'entity_type' => class_basename($model),
            'entity_id' => $model->getKey(),
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => Request::ip(),
        ]);
    }
}
