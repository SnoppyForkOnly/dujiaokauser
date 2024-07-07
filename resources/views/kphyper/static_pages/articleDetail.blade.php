@extends('hyper.layouts.article')
@section('content')
<div class="row">
    <div class="col-12">
        <div>
             
        </div>
        <div class="card">

            <div class="card-body">
                <div class="card-title">
                    <a href="/article">首页</a>-><a href="/article?cat_id={{$article['category_id']}}">{{$article['category']['category_name']}}</a>-> {{ $article['title'] }}
                </div>
                <hr>
                <div class="blog-content">
                    <div class="blog-post">
                        <h3 class="blog-post-title text-center">
                            {{ $article['title'] }}
                        </h3>
                        <p class="blog-post-meta text-right">日期：{{ $article['updated_at'] }}</p>
                        <p>
                            {!! $article['content'] !!}
                        </p>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>
<div class="tab-content">
    <div class="tab-pane active" id="group-all">

    </div>
</div>
@stop
@section('js')
@stop