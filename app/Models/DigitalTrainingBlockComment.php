<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DigitalTrainingBlockComment extends Model
{
    use HasFactory;

    protected $fillable = [
        'digital_training_id',
        'training_module_id',
        'training_block_id',
        'digital_training_enrollment_id',
        'client_profile_id',
        'parent_comment_id',
        'participant_name_snapshot',
        'participant_email_snapshot',
        'comment',
        'created_by_role',
        'is_visible',
    ];

    protected $casts = [
        'is_visible' => 'boolean',
    ];

    public function parent()
    {
        return $this->belongsTo(self::class, 'parent_comment_id');
    }

    public function replies()
    {
        return $this->hasMany(self::class, 'parent_comment_id')->orderBy('created_at');
    }

    public function training()
    {
        return $this->belongsTo(DigitalTraining::class, 'digital_training_id');
    }

    public function module()
    {
        return $this->belongsTo(TrainingModule::class, 'training_module_id');
    }

    public function block()
    {
        return $this->belongsTo(TrainingBlock::class, 'training_block_id');
    }

    public function enrollment()
    {
        return $this->belongsTo(DigitalTrainingEnrollment::class, 'digital_training_enrollment_id');
    }

    public function clientProfile()
    {
        return $this->belongsTo(ClientProfile::class);
    }
}
