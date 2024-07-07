<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use App\Events\ArticleCategoryDeleted;
/**

 */
class ArticleCategory extends BaseModel
{

    use SoftDeletes;

    protected $table = 'article_category';

    protected $dispatchesEvents = [
        'deleted' => ArticleCategoryDeleted::class
    ];

    /**
     * 关联商品
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     *
     * @author    assimon<ashang@utf8.hk>
     * @copyright assimon<ashang@utf8.hk>
     * @link      http://utf8.hk/
     */
    public function article()
    {
        return $this->hasMany(Articles::class, 'category_id');
    }

}
