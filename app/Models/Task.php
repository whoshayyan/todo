<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Task extends Model
{
    use HasFactory;

    protected $table = 'tasks';

    protected $fillable = ['name', 'due_date', 'status', 'parent_id'];
    public function parent()
    {
        return $this->belongsTo(App\Modles\Task::class,'parent_id');
    }
    public function childs()
    {
        return $this->hasMany(App\Modles\Task::class,);
    }



}
