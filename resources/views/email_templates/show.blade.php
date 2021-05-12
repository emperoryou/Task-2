@extends('layouts.admin')
@section('title')
    {{ $emailTemplate->name }}
@endsection

@push('css')
    <link rel="stylesheet" href="{{asset('assets/libs/summernote/summernote-bs4.css')}}">
@endpush
@push('theme-script')
    <script src="{{asset('assets/libs/summernote/summernote-bs4.js')}}"></script>
@endpush

@section('content')
    <div class="row">
        <div class="col-xs-12 col-sm-12 col-md-4 col-lg-4">
            <div class="card">
                <div class="card-body">
                    {{Form::model($emailTemplate, array('route' => array('email_template.update', $emailTemplate->id), 'method' => 'POST')) }}
                    <div class="row">
                        <div class="form-group col-md-12">
                            {{Form::label('name',__('Name'))}}
                            {{Form::text('name',null,array('class'=>'form-control font-style','disabled'=>'disabled'))}}
                        </div>
                        <div class="form-group col-md-12">
                            {{Form::label('from',__('From'))}}
                            {{Form::text('from',null,array('class'=>'form-control font-style','required'=>'required'))}}
                        </div>
                        {{Form::hidden('lang',$currEmailTempLang->lang,array('class'=>''))}}
                        <div class="form-group col-md-12 text-right">
                            {{Form::submit(__('Save'),array('class'=>'btn btn-sm btn-primary rounded-pill'))}}
                        </div>
                    </div>
                    {{ Form::close() }}
                </div>
            </div>
        </div>
        <div class="col-xs-12 col-sm-12 col-md-8 col-lg-8">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-6 pb-3 min-h-250">
                            @foreach(explode(',',$emailTemplate->keyword) as $keyword)
                                @php($word = explode(':',$keyword))
                                <p class="mb-1">{{__($word[0])}} : <span class="pull-right text-primary">{{ $word[1] }}</span></p>
                            @endforeach
                        </div>
                        <div class="col-6 pb-3 min-h-250">
                            <p class="mb-1">{{__('App Name')}} : <span class="pull-right text-primary">{app_name}</span></p>
                            <p class="mb-1">{{__('App URL')}} : <span class="pull-right text-primary">{app_url}</span></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="language-wrap">
                        <div class="row">
                            <div class="col-lg-9 col-md-9 col-sm-12 language-form-wrap">
                                {{Form::model($currEmailTempLang, array('route' => array('store.email.language',$currEmailTempLang->parent_id), 'method' => 'POST')) }}
                                <div class="row">
                                    <div class="col-12">
                                        <div class="form-group">
                                            {{Form::label('subject',__('Subject'))}}
                                            {{Form::text('subject',null,array('class'=>'form-control font-style','required'=>'required'))}}
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="form-group">
                                            {{Form::label('content',__('Email Message'))}}
                                            {{Form::textarea('content',$currEmailTempLang->content,array('class'=>'summernote-simple','required'=>'required'))}}
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-12 text-right">
                                    {{Form::hidden('lang',null)}}
                                    {{Form::submit(__('Save'),array('class'=>'btn btn-sm btn-primary rounded-pill'))}}
                                </div>
                                {{ Form::close() }}
                            </div>
                            <div class="col-lg-3 col-md-3 col-sm-12 language-list-wrap">
                                <div class="language-list">
                                    <ul class="nav nav-pills flex-column" id="myTab4" role="tablist">
                                        @foreach($languages as $lang)
                                            <li class="nav-item">
                                                <a href="{{route('manage.email.language',[$emailTemplate->id,$lang])}}" class="nav-link {{($currEmailTempLang->lang == $lang)?'active':''}}">{{Str::upper($lang)}}</a>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
