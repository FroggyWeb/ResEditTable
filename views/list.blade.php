@extends('resedittable::layout')

@section('pagetitle', $folder ? $folder->pagetitle : $container->pagetitle)

@section('buttons')
  <div id="actions">
    <div class="btn-group">
      <a class="btn btn-success" href="index.php?a=4&pid={{ $container->id }}">
        <i class="fa fa-file-o"></i><span>{{ $lang['create_child'] }}</span>
      </a>

      @if (request()->has('filter'))
        <a href="javascript:;" class="btn btn-secondary" onclick="location = location.pathname;">
          <i class="fa fa-times-circle"></i><span>@lang('resedittable::messages.reset_filters')</span>
        </a>
      @endif

      <a href="javascript:;" class="btn btn-secondary" onclick="location.reload();">
        <i class="fa fa-refresh"></i><span>@lang('resedittable::messages.refresh')</span>
      </a>
      <a class="btn btn-secondary" href="index.php?a=27&id={{ $container->id }}">
        <i class="fa fa-pencil"></i><span>{{ $lang['edit_document'] }}</span>
      </a>
    </div>
  </div>
@endsection

@section('body')

@endsection
