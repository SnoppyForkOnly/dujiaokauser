@extends('hyper.layouts.article')

@section('content')
<style>
       .avatar-sm {
        width: 30px;
        height: 30px;
        object-fit: cover;
    }
    .blog-post-meta {
        font-size: 0.875rem; /* 更新时间的字体大小 */
        color: #6c757d; /* 更新时间的字体颜色 */
    }
    .blog-post-title a {
        font-size: 1.00rem; /* 文章标题的字体大小 */
        color: inherit; /* 移除这里的蓝色颜色设置，使链接继承父元素的颜色 */
        text-decoration: none; /* 可选：移除下划线 */
    }
    .blog-post-title a:hover {
        color: #007bff; /* 当鼠标悬停时变为蓝色 */
    }
    .blog-post-header {
        display: flex;
        justify-content: space-between;
        align-items: baseline;
        flex-wrap: wrap;
    }
    .blog-post-header h3 {
        margin-bottom: 0; /* 移除标题的默认下边距 */
    }
    .article-summary {
        font-size: 0.775rem; /* 将摘要的字体大小调整为更小的尺寸 */
    }
</style>
<div class="container">
    <div class="row">
        <div class="col-12">
            <div class="card mt-3">
                <div class="card-body">
                    <nav class="nav d-flex">
                    @if(empty($catId))
                        <a class="p-2" href="/article"><strong>全部</strong></a>
                    @else
                        <a class="p-2 text-muted" href="/article">全部</a>
                    @endif

                    @foreach($categorys as  $index => $category)
                        @if($category['id'] == $catId)
                            <a class="p-2" href="?cat_id={{$category['id']}}"><strong>{{ $category['category_name'] }}</strong></a>
                        @else
                            <a class="p-2 text-muted" href="?cat_id={{$category['id']}}">{{ $category['category_name'] }}</a>
                        @endif

                    @endforeach
                    </nav>
                    <hr>
                    <div class="blog-content">
                        @foreach($articles as $index => $article)
                        <div class="row mb-4">
                            <div class="col-12">
                                <div class="blog-post-header">
                                    <h3 class="blog-post-title">
                                        <a href="article/{{ !empty($article['link']) ? $article['link'] : $article['id'] }}">
                                            <img src="{{ picture_ulr($article['picture']) }}" class="avatar-sm mr-2">{{ $article['title'] }}
                                        </a>
                                    </h3>
                          <p class="blog-post-meta">
                          <a href="article/{{ !empty($article['link']) ? $article['link'] : $article['id'] }}" class="blog-post-meta">
                                         {{ $article['updated_at'] }}
                                      </a>
                                </p>

                                </div>
                                <p class="article-summary">{{ \Illuminate\Support\Str::limit(strip_tags($article['content']), 100, '...') }}</p>
                            </div>
                        </div>
                        @endforeach
                        <div class="pagination justify-content-center">
                            {{ $articles->appends(request()->all())->links('vendor.pagination.default') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@stop
