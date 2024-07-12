<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Worker extends Model
{
    use HasFactory;

    protected $table = 'workers';
    protected $guarded = ['id'];
    
    public $timestamps = true;

    /**
     * 特定の期間内のアサインを取得
     */
    public function getAssignmentsForPeriod($startDate, $endDate)
    {
        return $this->assigns()
            ->whereBetween('start_date', [$startDate, $endDate])
            ->orWhereBetween('end_date', [$startDate, $endDate])
            ->orWhere(function ($query) use ($startDate, $endDate) {
                $query->where('start_date', '<=', $startDate)
                      ->where('end_date', '>=', $endDate);
            })
            ->with('workplace')
            ->get();
    }
    /**
     * 特定の日のアサインを取得
     */
    public function getAssignmentsForDate($date)
    {
        return $this->assigns()
            ->whereDate('start_date', '<=', $date)
            ->whereDate('end_date', '>=', $date)
            ->with('workplace')
            ->get();
    }

    /**
     * スケジュールの重複をチェック
     */
    public function hasScheduleConflict($startDate, $endDate, $excludeAssignId = null)
    {
        $query = $this->assigns()
            ->where(function ($query) use ($startDate, $endDate) {
                $query->whereBetween('start_date', [$startDate, $endDate])
                    ->orWhereBetween('end_date', [$startDate, $endDate])
                    ->orWhere(function ($query) use ($startDate, $endDate) {
                        $query->where('start_date', '<=', $startDate)
                              ->where('end_date', '>=', $endDate);
                    });
            });

        if ($excludeAssignId) {
            $query->where('id', '!=', $excludeAssignId);
        }

        return $query->exists();
    }

    // リレーション: 施工会社との1対多の関係
    public function constructionCompany()
    {
        return $this->belongsTo(ConstructionCompany::class, 'construction_company_id');
    }

    public function role()
    {
        return $this->belongsTo(Role::class);
    }
    
    /**
     * 送信された通知のリレーション
     */
    public function sentNotifications()
    {
        return $this->morphMany(Notification::class, 'sender');
    }

    /**
     * 受信した通知のリレーション
     */
    public function receivedNotifications()
    {
        return $this->morphMany(NotificationReceiver::class, 'recipient');
    }
    
    // Assign（割り当て）とのリレーション
    public function assigns()
    {
        return $this->hasMany(Assign::class);
    }

    /**
     * 現在のアサインを取得
     */
    public function getCurrentAssignment()
    {
        return $this->assigns()
            ->whereDate('start_date', now())
            ->where(function($query) {
                $query->whereNull('actual_start_time')
                    ->orWhereNull('actual_end_time');
            })
            ->orderBy('start_time')
            ->first();
    }

}
