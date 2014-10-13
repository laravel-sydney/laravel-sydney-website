@extends('layouts.scaffold')

@section('content')

<div class="row">
    <div class="col-md-10 col-md-offset-2">
        <h1>Edit Post</h1>

        @if ($errors->any())
        	<div class="alert alert-danger">
        	    <ul>
                    {{ implode('', $errors->all('<li class="error">:message</li>')) }}
                </ul>
        	</div>
        @endif
    </div>
</div>

{{ Form::model($post, array('class' => 'form-horizontal', 'method' => 'PATCH', 'route' => array('posts.update', $post->id))) }}

                <div class="form-group">
                    {{ Form::label('title', 'Title', array("class" => "col-md-2 control-label")) }}
                    <div class="col-sm-10">
                      {{ Form::text('title', Input::old('title'), ['class' => 'form-control', 'maxlength' => '255', 'placeholder'=>'Title']) }}
                    </div>
                </div>
                <div class="form-group">
                    {{ Form::label('body', 'Body', array("class" => "col-md-2 control-label")) }}
                    <div class="col-sm-10">
                      {{ Form::textarea('body', Input::old('body'), array('class'=>'form-control', 'placeholder'=>'Body')) }}
                    </div>
                </div>

<div class="form-group">
    <label class="col-sm-2 control-label">&nbsp;</label>
    <div class="col-sm-10">
      {{ Form::submit('Update', array('class' => 'btn btn-lg btn-primary')) }}
      {{ link_to_route('posts.show', 'Cancel', $post->id, array('class' => 'btn btn-lg btn-default')) }}
    </div>
</div>

{{ Form::close() }}

@stop