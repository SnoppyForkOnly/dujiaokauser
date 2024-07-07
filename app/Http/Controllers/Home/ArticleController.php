<?php
namespace App\Http\Controllers\Home;

use App\Http\Controllers\BaseController;
use App\Models\ArticleCategory;
use App\Models\Articles;
use Illuminate\Http\Request;

/**

 */
class ArticleController extends BaseController {
    
    public function list(Request $request){
        // SEO
        $seo = array();
        $seo['title'] = '文章列表';
        $seo['keywords'] = '文章列表 ';
        $seo['description']  = '文章列表';

        $articlePageSize = 15;
        $likePageSize = 15;
        $catId = $request->get('cat_id');
        $categorys = ArticleCategory::all();
        if (!empty($catId)) {
            $articles  = Articles::where('category_id', '=', $catId)->paginate($articlePageSize);
        } else {
            $articles  = Articles::paginate($articlePageSize);
        }
        $ids = [];
        foreach ($articles as $article) {
            $ids[] = $article->id;
        }
        $randArticles = Articles::whereNotIn('id', $ids)->inRandomOrder()->take($likePageSize)->get();
        $data =
            [
                'catId' => $catId,
                'categorys' => $categorys,
                'articles' => $articles,
                'paginator' => $articles,
                'elements' => $articles,
                'randArticles' => $randArticles,
                'seo' => $seo
            ];
        return $this->render('static_pages/article', $data, __('dujiaoka.page-title.article'));
    }
    
   public function detail(Request $request, $identifier) {
    $article = Articles::where('link', $identifier)->first();

    // 如果没有找到文章，尝试将 identifier 作为 id 查询
    if (!$article) {
        $article = Articles::find($identifier);
    }

    if (!$article) {
        abort(404);
    }

    // SEO 设置
    $seo = [
        'title' => $article->title,
        'keywords' => $article->keywords,
        'description' => $article->description,
    ];

    return $this->render('static_pages/articleDetail', [
        'article' => $article,
        'seo' => $seo
    ], $seo['title']." | ". __('dujiaoka.page-title.article'));
}

 /*   public function detail(Request $request, $id) {

//        $catId = $request->get('cat_id');
//        $categorys = ArticleCategory::all();

        // 根据id查询文章内容
        $article = Articles::where('id', $id)->first();

        if (!$article) {
            abort(404);
        }
        // SEO
        $seo = array();
        $seo['title'] = $article->title;
        $seo['keywords'] = $article->keywords;
        $seo['description']  = $article->description;

        return $this->render('static_pages/articleDetail', [
        'article' => $article,
        'seo' => $seo
        ], $seo['title']." | ". __('dujiaoka.page-title.article'));
    }
    */
}