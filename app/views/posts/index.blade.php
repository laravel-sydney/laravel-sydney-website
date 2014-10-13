@extends('layouts.scaffold')

@section('content')

<h1>All Posts</h1>

<p>{{ link_to_route('posts.create', 'Add New Post', null, array('class' => 'btn btn-lg btn-success')) }}</p>

@if ($posts->count())
	<table class="table table-striped">
		<thead>
			<tr>
				<th>Title</th>
				<th>Body</th>
				<th>&nbsp;</th>
			</tr>
		</thead>

		<tbody>
			@foreach ($posts as $post)
				<tr>
					<td>{{{ $post->title }}}</td>
					<td>{{{ $post->body }}}</td>
                    <td>
                        {{ Form::open(array('style' => 'display: inline-block;', 'method' => 'DELETE', 'route' => array('posts.destroy', $post->id))) }}
                            {{ Form::submit('Delete', array('class' => 'btn btn-danger')) }}
                        {{ Form::close() }}
                        {{ link_to_route('posts.edit', 'Edit', array($post->id), array('class' => 'btn btn-info')) }}
                    </td>
				</tr>
			@endforeach
		</tbody>
	</table>
@else
	There are no posts
@endif

@stop