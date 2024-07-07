<?php

namespace App\Models;

use App\Events\ArticlesDeleted;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

/**

 */
class Articles extends Model
{
    use SoftDeletes;
    protected $table = 'articles';

    protected $dispatchesEvents = [
        'deleted' => ArticlesDeleted::class
    ];

    protected $fillable = ['title','link', 'content', 'picture','updated_at', 'category_id', 'category_id', 'category_name'];

    public function category()
    {
        return $this->belongsTo(ArticleCategory::class, 'category_id', 'id');
    }
    
    public function getSummary()
    {
        //简单地输出摘要
        $summary = substr($this->content, 0, 200);
        return strip_tags($summary);
    }
}
